<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Mail\ClientPortalInvitationMail;
use App\Models\Client;
use App\Models\ClientMember;
use App\Models\Matter;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $firmId = $user->firm_id ?? 1;

        $query = Client::where('firm_id', $firmId)
            ->with([
                'primaryAttorney:id,name',
                'members:id,client_id,name',
                'matters:id,client_id,title,status',
            ]);

        // Attorney-level scoping for non-partners
        if (! in_array($user->role, ['superadmin', 'partner'])) {
            $query->where(function ($q) use ($user) {
                $q->where('primary_attorney_id', $user->id)
                    ->orWhereHas('matters', function ($mq) use ($user) {
                        $mq->where('lead_attorney_id', $user->id)
                            ->orWhereHas('users', fn ($uq) => $uq->where('users.id', $user->id));
                    });
            });
        }

        // Filter by Entity Category
        if ($category = $request->get('category')) {
            if ($category === 'joint') {
                $query->whereIn('category', ['joint', 'multiple']);
            } elseif ($category === 'corporate') {
                $query->whereIn('category', ['corporate', 'company', 'llp']);
            } elseif ($category === 'institution') {
                $query->whereIn('category', ['institution', 'trust', 'society', 'partnership', 'proprietorship']);
            } elseif ($category !== 'all') {
                $query->where('category', $category);
            }
        }

        // Filter by Onboarding Mode
        if ($mode = $request->get('mode')) {
            if ($mode === 'portal') {
                $query->where(function ($q) {
                    $q->where('onboarding_mode', 'portal_online')
                        ->orWhere('portal_status', 'active');
                });
            } elseif ($mode === 'assisted_offline') {
                $query->where(function ($q) {
                    $q->where('onboarding_mode', 'assisted_offline')
                        ->orWhere('portal_status', 'offline_only');
                });
            }
        }

        // Search Query
        if ($search = trim($request->get('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('pan', 'like', "%{$search}%")
                    ->orWhere('cin', 'like', "%{$search}%")
                    ->orWhere('police_station', 'like', "%{$search}%")
                    ->orWhere('district', 'like', "%{$search}%");
            });
        }

        $clients = $query->latest()->get();

        // Metrics for ribbon
        $totalClientsCount = Client::where('firm_id', $firmId)->count();
        $portalActiveCount = Client::where('firm_id', $firmId)
            ->where(fn ($q) => $q->where('portal_status', 'active')->orWhereNotNull('user_id'))
            ->count();
        $assistedOfflineCount = Client::where('firm_id', $firmId)
            ->where(fn ($q) => $q->where('onboarding_mode', 'assisted_offline')->orWhere('portal_status', 'offline_only'))
            ->count();
        $activeMattersCount = Matter::where('firm_id', $firmId)->where('status', 'active')->count();

        $attorneys = User::where('firm_id', $firmId)
            ->whereIn('role', ['partner', 'associate'])
            ->select(['id', 'name', 'role'])
            ->get();

        return view('clients.index', compact(
            'clients',
            'attorneys',
            'totalClientsCount',
            'portalActiveCount',
            'assistedOfflineCount',
            'activeMattersCount'
        ));
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $firmId = Auth::user()->firm_id ?? 1;
        $attorneyId = $validated['primary_attorney_id'] ?? Auth::id();

        // Determine client type legacy column
        $legacyType = in_array($validated['category'], ['corporate', 'institution', 'partnership'])
            ? 'corporate'
            : 'individual';

        $client = Client::create([
            'firm_id' => $firmId,
            'primary_attorney_id' => $attorneyId,
            'type' => $legacyType,
            'category' => $validated['category'],
            'onboarding_mode' => $validated['onboarding_mode'],
            'name' => $validated['name'],
            'contact_person' => $validated['contact_person'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'trust_balance' => $validated['trust_balance'] ?? 0.00,
            'status' => 'active',

            // Indian KYC & Personal/Entity Particulars
            'father_husband_name' => $validated['father_husband_name'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'age' => $validated['age'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'pan' => ! empty($validated['pan']) ? strtoupper($validated['pan']) : null,
            'aadhaar_last_four' => $validated['aadhaar_last_four'] ?? null,
            'voter_id' => $validated['voter_id'] ?? null,
            'passport_number' => $validated['passport_number'] ?? null,

            // Corporate / Institution Particulars
            'cin' => ! empty($validated['cin']) ? strtoupper($validated['cin']) : null,
            'llpin' => ! empty($validated['llpin']) ? strtoupper($validated['llpin']) : null,
            'gstin' => ! empty($validated['gstin']) ? strtoupper($validated['gstin']) : null,
            'registration_number' => $validated['registration_number'] ?? null,
            'roc_jurisdiction' => $validated['roc_jurisdiction'] ?? null,

            // Address & Court Jurisdiction
            'address_line_1' => $validated['address_line_1'] ?? null,
            'address_line_2' => $validated['address_line_2'] ?? null,
            'city' => $validated['city'] ?? null,
            'district' => $validated['district'] ?? null,
            'state' => $validated['state'] ?? 'Delhi',
            'pincode' => $validated['pincode'] ?? null,
            'police_station' => $validated['police_station'] ?? null,
            'address' => $validated['address_line_1'] ?? null,

            // Representation & Assisted Mode
            'representation_mode' => $validated['representation_mode'] ?? 'self',
            'representative_name' => $validated['representative_name'] ?? null,
            'representative_relation' => $validated['representative_relation'] ?? null,
            'representative_phone' => $validated['representative_phone'] ?? null,
            'representative_email' => $validated['representative_email'] ?? null,
            'poa_registration_number' => $validated['poa_registration_number'] ?? null,
            'poa_date' => $validated['poa_date'] ?? null,
            'poa_sub_registrar_office' => $validated['poa_sub_registrar_office'] ?? null,

            // Notes
            'internal_intake_notes' => $validated['internal_intake_notes'] ?? null,
        ]);

        // Save Joint / Co-Members if present
        if (! empty($validated['members']) && is_array($validated['members'])) {
            foreach ($validated['members'] as $memberData) {
                if (! empty($memberData['name'])) {
                    ClientMember::create([
                        'firm_id' => $firmId,
                        'client_id' => $client->id,
                        'name' => $memberData['name'],
                        'relationship' => $memberData['relationship'] ?? 'Co-petitioner',
                        'phone' => $memberData['phone'] ?? null,
                        'email' => $memberData['email'] ?? null,
                        'pan' => ! empty($memberData['pan']) ? strtoupper($memberData['pan']) : null,
                        'aadhaar_last_four' => $memberData['aadhaar_last_four'] ?? null,
                        'is_primary_signatory' => ! empty($memberData['is_primary_signatory']),
                    ]);
                }
            }
        }

        // Portal Invitation Handling
        if ($validated['onboarding_mode'] === 'portal_online' && ! empty($validated['email'])) {
            $token = $client->generateInvitationToken();

            // Auto-provision user login account or create placeholder
            $portalUser = User::firstOrCreate(
                ['email' => $validated['email']],
                [
                    'firm_id' => $firmId,
                    'name' => $validated['contact_person'] ?: $validated['name'],
                    'password' => Hash::make(str()->random(24)),
                    'role' => 'client',
                    'title' => 'Client Representative',
                    'phone' => $validated['phone'] ?? null,
                ]
            );

            $client->update([
                'user_id' => $portalUser->id,
                'portal_status' => 'invited',
            ]);

            $invitationUrl = route('portal.invitation.accept', ['token' => $token]);

            try {
                Mail::to($validated['email'])->send(new ClientPortalInvitationMail($client, $invitationUrl));
            } catch (Throwable $e) {
                Log::warning('Could not send portal invitation email: '.$e->getMessage());
            }

            return redirect()->route('clients.show', $client->id)->with('success',
                "Client '{$client->name}' onboarded. Portal invitation dispatched to {$client->email}."
            );
        }

        // Assisted Offline Mode
        $client->update(['portal_status' => 'offline_only']);

        return redirect()->route('clients.show', $client->id)->with('success',
            "Client '{$client->name}' onboarded via Assisted Offline Intake. Chamber docket slip ready for printing."
        );
    }

    public function show(Client $client): View
    {
        $firmId = Auth::user()->firm_id ?? 1;
        if ($client->firm_id !== $firmId) {
            abort(403, 'Unauthorized access to client dossier.');
        }

        $client->load([
            'primaryAttorney',
            'members',
            'matters' => fn ($q) => $q->with(['leadAttorney', 'documents'])->latest(),
            'documentRequests' => fn ($q) => $q->with(['requestedBy', 'document', 'matter', 'assistedBy'])->latest(),
            'invoices' => fn ($q) => $q->latest(),
        ]);

        $attorneys = User::where('firm_id', $firmId)->whereIn('role', ['partner', 'associate'])->get();

        return view('clients.show', compact('client', 'attorneys'));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $firmId = Auth::user()->firm_id ?? 1;
        if ($client->firm_id !== $firmId) {
            abort(403, 'Unauthorized client update.');
        }

        $validated = $request->validated();

        $client->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'onboarding_mode' => $validated['onboarding_mode'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'primary_attorney_id' => $validated['primary_attorney_id'] ?? $client->primary_attorney_id,
            'trust_balance' => $validated['trust_balance'] ?? $client->trust_balance,
            'status' => $validated['status'],

            'father_husband_name' => $validated['father_husband_name'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'age' => $validated['age'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'pan' => ! empty($validated['pan']) ? strtoupper($validated['pan']) : null,
            'aadhaar_last_four' => $validated['aadhaar_last_four'] ?? null,
            'voter_id' => $validated['voter_id'] ?? null,
            'passport_number' => $validated['passport_number'] ?? null,

            'cin' => ! empty($validated['cin']) ? strtoupper($validated['cin']) : null,
            'llpin' => ! empty($validated['llpin']) ? strtoupper($validated['llpin']) : null,
            'gstin' => ! empty($validated['gstin']) ? strtoupper($validated['gstin']) : null,
            'registration_number' => $validated['registration_number'] ?? null,
            'roc_jurisdiction' => $validated['roc_jurisdiction'] ?? null,
            'contact_person' => $validated['contact_person'] ?? null,

            'address_line_1' => $validated['address_line_1'] ?? null,
            'address_line_2' => $validated['address_line_2'] ?? null,
            'city' => $validated['city'] ?? null,
            'district' => $validated['district'] ?? null,
            'state' => $validated['state'] ?? 'Delhi',
            'pincode' => $validated['pincode'] ?? null,
            'police_station' => $validated['police_station'] ?? null,
            'address' => $validated['address_line_1'] ?? null,

            'representation_mode' => $validated['representation_mode'] ?? 'self',
            'representative_name' => $validated['representative_name'] ?? null,
            'representative_relation' => $validated['representative_relation'] ?? null,
            'representative_phone' => $validated['representative_phone'] ?? null,
            'representative_email' => $validated['representative_email'] ?? null,
            'poa_registration_number' => $validated['poa_registration_number'] ?? null,
            'poa_date' => $validated['poa_date'] ?? null,
            'poa_sub_registrar_office' => $validated['poa_sub_registrar_office'] ?? null,
            'internal_intake_notes' => $validated['internal_intake_notes'] ?? null,
        ]);

        return back()->with('success', 'Client particulars and Indian statutory KYC updated.');
    }

    public function invite(Client $client): RedirectResponse
    {
        $firmId = Auth::user()->firm_id ?? 1;
        if ($client->firm_id !== $firmId) {
            abort(403, 'Unauthorized.');
        }

        if (empty($client->email)) {
            return back()->with('error', 'Client does not have an email address recorded. Update email before sending invitation.');
        }

        $token = $client->generateInvitationToken();

        $portalUser = User::firstOrCreate(
            ['email' => $client->email],
            [
                'firm_id' => $firmId,
                'name' => $client->contact_person ?: $client->name,
                'password' => Hash::make(str()->random(24)),
                'role' => 'client',
                'title' => 'Client Representative',
                'phone' => $client->phone,
            ]
        );

        $client->update([
            'user_id' => $portalUser->id,
            'onboarding_mode' => 'portal_online',
            'portal_status' => 'invited',
        ]);

        $invitationUrl = route('portal.invitation.accept', ['token' => $token]);

        try {
            Mail::to($client->email)->send(new ClientPortalInvitationMail($client, $invitationUrl));
        } catch (Throwable $e) {
            Log::warning('Could not send portal invitation: '.$e->getMessage());
        }

        return back()->with('success', "Portal invitation dispatched to {$client->email}. Token expires in 7 days.");
    }

    public function generateIntakeSlip(Client $client): View
    {
        $firmId = Auth::user()->firm_id ?? 1;
        if ($client->firm_id !== $firmId) {
            abort(403, 'Unauthorized.');
        }

        $client->load(['members', 'primaryAttorney', 'matters.leadAttorney', 'firm']);

        return view('clients.intake-slip', compact('client'));
    }

    public function destroy(Client $client): RedirectResponse
    {
        $firmId = Auth::user()->firm_id ?? 1;
        if ($client->firm_id !== $firmId) {
            abort(403, 'Unauthorized.');
        }

        $clientName = $client->name;

        // Clean up linked portal user if created exclusively for this client
        if ($client->user_id && $client->user && $client->user->role === 'client') {
            $clientUser = $client->user;
            $otherClients = Client::where('user_id', $clientUser->id)->where('id', '!=', $client->id)->count();
            if ($otherClients === 0) {
                $clientUser->delete();
            }
        }

        // Clean up client members and document requests
        $client->members()->delete();
        $client->documentRequests()->delete();

        // Delete client record
        $client->delete();

        return redirect()->route('clients.index')->with('success', "Client record for '{$clientName}' deleted successfully.");
    }
}
