<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'category' => 'required|in:individual,joint,corporate,institution,partnership,proprietorship',
            'name' => 'required|string|max:255',
            'onboarding_mode' => 'required|in:portal_online,assisted_offline',
            'phone' => 'nullable|string|max:50',
            'primary_attorney_id' => 'nullable|exists:users,id',
            'trust_balance' => 'nullable|numeric|min:0',

            // Indian KYC & Personal/Entity Particulars
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

            // Joint / Co-Members repeater (JSON or array)
            'members' => 'nullable|array',
            'members.*.name' => 'required_with:members|string|max:255',
            'members.*.relationship' => 'required_with:members|string|max:100',
            'members.*.phone' => 'nullable|string|max:50',
            'members.*.email' => 'nullable|email|max:255',
            'members.*.pan' => ['nullable', 'string', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i'],
            'members.*.aadhaar_last_four' => ['nullable', 'string', 'regex:/^[0-9]{4}$/'],
            'members.*.is_primary_signatory' => 'nullable|boolean',
        ];

        // If portal_online mode, email is mandatory. If assisted_offline, email is optional!
        if ($this->input('onboarding_mode') === 'assisted_offline') {
            $rules['email'] = 'nullable|email|max:255';
            $rules['phone'] = 'required|string|max:50'; // Phone is required for offline contacts
        } else {
            $rules['email'] = 'required|email|max:255';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'pan.regex' => 'The Permanent Account Number (PAN) must be 10 characters in the standard Indian format (e.g. ABCDE1234F).',
            'aadhaar_last_four.regex' => 'Please provide only the last 4 digits of Aadhaar (e.g. 4321) in compliance with UIDAI regulations.',
            'gstin.regex' => 'GSTIN must follow the standard 15-character format (e.g. 07AAAAA0000A1Z5).',
            'pincode.regex' => 'The Postal Pincode must be a 6-digit Indian PIN (e.g. 110001).',
            'email.required' => 'An email address is required for Online Portal onboarding so the client can receive their activation invitation.',
            'phone.required' => 'A mobile contact number is required for Assisted Offline onboarding.',
        ];
    }
}
