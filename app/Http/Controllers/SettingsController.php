<?php

namespace App\Http\Controllers;

use App\Models\ActivityCategory;
use App\Models\EmailTemplate;
use App\Models\Firm;
use App\Models\Holiday;
use App\Models\IdType;
use App\Models\LetterTemplate;
use App\Models\PracticeArea;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Display the dynamic practice settings hub.
     */
    public function index(Request $request)
    {
        $firm = Auth::user()->firm ?? Firm::first();
        $firmId = $firm->id ?? 1;

        $tab = $request->input('tab', 'practice-areas');

        $practiceAreas = PracticeArea::where('firm_id', $firmId)->latest()->get();
        $holidays = Holiday::where('firm_id', $firmId)->orderBy('date')->get();
        $activities = ActivityCategory::where('firm_id', $firmId)->get();
        $letterTemplates = LetterTemplate::where('firm_id', $firmId)->get();
        $emailTemplates = EmailTemplate::where('firm_id', $firmId)->get();
        $idTypes = IdType::where('firm_id', $firmId)->get();
        $users = User::where('firm_id', $firmId)->get();

        return view('settings.index', compact(
            'firm',
            'tab',
            'practiceAreas',
            'holidays',
            'activities',
            'letterTemplates',
            'emailTemplates',
            'idTypes',
            'users'
        ));
    }

    /**
     * Update Firm Profile
     */
    public function updateFirm(Request $request)
    {
        $firm = Auth::user()->firm ?? Firm::first();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'domain' => 'nullable|string|max:100',
        ]);

        $firm->update($validated);

        return redirect()->route('settings.index', ['tab' => 'firm-profile'])
            ->with('success', 'Firm profile details successfully updated.');
    }

    /**
     * Store Practice Area
     */
    public function storePracticeArea(Request $request)
    {
        $firmId = Auth::user()->firm_id ?? 1;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
        ]);

        PracticeArea::create([
            'firm_id' => $firmId,
            'name' => $validated['name'],
            'code' => $validated['code'] ? strtoupper($validated['code']) : null,
            'description' => $validated['description'],
            'is_active' => true,
        ]);

        return redirect()->route('settings.index', ['tab' => 'practice-areas'])
            ->with('success', "Practice Area '{$validated['name']}' added to chambers taxonomy.");
    }

    /**
     * Toggle Practice Area Active Status
     */
    public function togglePracticeArea(PracticeArea $practiceArea)
    {
        $practiceArea->is_active = !$practiceArea->is_active;
        $practiceArea->save();

        return redirect()->route('settings.index', ['tab' => 'practice-areas'])
            ->with('success', "Practice Area '{$practiceArea->name}' status updated.");
    }

    /**
     * Store Court Holiday / Vacation
     */
    public function storeHoliday(Request $request)
    {
        $firmId = Auth::user()->firm_id ?? 1;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'is_court_vacation' => 'nullable',
            'description' => 'nullable|string|max:500',
        ]);

        Holiday::create([
            'firm_id' => $firmId,
            'name' => $validated['name'],
            'date' => $validated['date'],
            'is_court_vacation' => $request->has('is_court_vacation'),
            'description' => $validated['description'],
        ]);

        return redirect()->route('settings.index', ['tab' => 'holidays'])
            ->with('success', "Court holiday '{$validated['name']}' listed on chambers schedule.");
    }

    /**
     * Destroy Court Holiday
     */
    public function destroyHoliday(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('settings.index', ['tab' => 'holidays'])
            ->with('success', 'Holiday record removed.');
    }

    /**
     * Store Letter Template
     */
    public function storeLetterTemplate(Request $request)
    {
        $firmId = Auth::user()->firm_id ?? 1;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'body_html' => 'required|string',
        ]);

        LetterTemplate::create([
            'firm_id' => $firmId,
            'name' => $validated['name'],
            'category' => $validated['category'],
            'body_html' => $validated['body_html'],
        ]);

        return redirect()->route('settings.index', ['tab' => 'letters'])
            ->with('success', "Legal notice template '{$validated['name']}' preserved.");
    }

    /**
     * Update Letter Template
     */
    public function updateLetterTemplate(Request $request, LetterTemplate $letterTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'body_html' => 'required|string',
        ]);

        $letterTemplate->update($validated);

        return redirect()->route('settings.index', ['tab' => 'letters'])
            ->with('success', "Legal notice template '{$letterTemplate->name}' updated.");
    }

    /**
     * Update Email Template
     */
    public function updateEmailTemplate(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string',
        ]);

        $emailTemplate->update($validated);

        return redirect()->route('settings.index', ['tab' => 'emails'])
            ->with('success', "Communication template '{$emailTemplate->name}' updated.");
    }

    /**
     * Update ID Numbering Scheme
     */
    public function updateIdType(Request $request, IdType $idType)
    {
        $validated = $request->validate([
            'prefix' => 'required|string|max:10',
            'format_mask' => 'required|string|max:50',
            'next_number' => 'required|integer|min:1',
        ]);

        $idType->update($validated);

        return redirect()->route('settings.index', ['tab' => 'numbering'])
            ->with('success', "Numbering mask for '{$idType->name}' updated.");
    }
}
