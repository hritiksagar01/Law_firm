<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'salutation' => 'nullable|string|max:20',
            'name' => 'required|string|max:255',
            'category' => 'required|in:individual,joint,corporate,institution,partnership,proprietorship',
            'onboarding_mode' => 'required|in:portal_online,assisted_offline',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'primary_attorney_id' => 'nullable|exists:users,id',
            'trust_balance' => 'nullable|numeric|min:0',
            'status' => 'required|in:lead,intake,conflict_check,prospective,active,inactive,former,archived,conflict',
            'intake_status' => 'nullable|in:pending,in_progress,completed,declined',
            'conflict_check_status' => 'nullable|in:not_checked,pending,clear,potential_conflict,conflict_identified,waiver_required,cleared,rejected',
            'preferred_attorney_id' => 'nullable|exists:users,id',
            'assigned_paralegal_id' => 'nullable|exists:users,id',
            'referral_source' => 'nullable|string|max:100',
            'client_type' => 'nullable|string|max:100',

            // Indian KYC & Personal/Entity Particulars
            'father_salutation' => 'nullable|string|max:20',
            'father_husband_name' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'age' => 'nullable|integer|min:0|max:130',
            'occupation' => 'nullable|string|max:255',
            'pan' => ['nullable', 'string', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i'],
            'aadhaar_last_four' => ['nullable', 'string', 'regex:/^[0-9]{4}$/'],
            'voter_id' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',

            // Corporate / Institution Particulars
            'cin' => 'nullable|string|max:25',
            'llpin' => 'nullable|string|max:15',
            'gstin' => ['nullable', 'string', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i'],
            'registration_number' => 'nullable|string|max:100',
            'roc_jurisdiction' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',

            // Address & Jurisdiction
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => ['nullable', 'string', 'regex:/^[1-9][0-9]{5}$/'],
            'police_station' => 'nullable|string|max:100',

            // Representation & Assisted Mode
            'representation_mode' => 'nullable|in:self,poa_holder,next_friend_guardian,authorized_signatory,relative',
            'representative_name' => 'nullable|string|max:255',
            'representative_relation' => 'nullable|string|max:100',
            'representative_phone' => 'nullable|string|max:50',
            'representative_email' => 'nullable|email|max:255',
            'poa_registration_number' => 'nullable|string|max:100',
            'poa_date' => 'nullable|date',
            'poa_sub_registrar_office' => 'nullable|string|max:255',

            // Notes
            'internal_intake_notes' => 'nullable|string|max:2000',
        ];
    }
}
