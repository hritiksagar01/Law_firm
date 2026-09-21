<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chamber File Docket Sheet — {{ $client->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #ffffff;
            color: #111111;
            font-size: 11pt;
            line-height: 1.4;
            padding: 30px;
        }
        .basta-container {
            max-width: 800px;
            margin: 0 auto;
            border: 3px double #1a1a1a;
            padding: 24px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .chambers-title {
            font-family: Georgia, serif;
            font-size: 20pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .sub-header {
            font-size: 10pt;
            color: #444444;
            margin-top: 3px;
        }
        .badge-title {
            display: inline-block;
            margin-top: 8px;
            background: #1a1a1a;
            color: #ffffff;
            font-size: 9pt;
            font-weight: 600;
            padding: 3px 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .docket-ribbon {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f4f2eb;
            border: 1px solid #d4cebe;
            padding: 8px 14px;
            margin-bottom: 16px;
            font-family: "Courier New", monospace;
            font-size: 10pt;
            font-weight: bold;
        }
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #1a1a1a;
            padding-bottom: 3px;
            margin: 14px 0 8px 0;
            display: flex;
            justify-content: space-between;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 20px;
            font-size: 10pt;
        }
        .field {
            margin-bottom: 4px;
        }
        .field-label {
            font-size: 8pt;
            color: #666666;
            text-transform: uppercase;
            font-weight: 600;
            display: block;
        }
        .field-val {
            font-size: 10pt;
            color: #000000;
            font-weight: 500;
        }
        .table-box {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-top: 6px;
        }
        .table-box th, .table-box td {
            border: 1px solid #cccccc;
            padding: 5px 8px;
            text-align: left;
        }
        .table-box th {
            background: #f8f8f8;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 8pt;
        }
        .signatures {
            margin-top: 36px;
            display: flex;
            justify-content: space-between;
            padding-top: 10px;
        }
        .sig-block {
            text-align: center;
            width: 220px;
            border-top: 1px solid #1a1a1a;
            padding-top: 6px;
            font-size: 9pt;
        }
        .print-btn {
            position: fixed;
            top: 16px;
            right: 16px;
            background: #23493a;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }
        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
            .basta-container { border: 2px solid #000000; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨️ Print Docket Slip (Ctrl + P)</button>

    <div class="basta-container">
        <!-- Header -->
        <div class="header">
            <div class="chambers-title">{{ $client->firm->name ?? 'Sharma Legal Chambers' }}</div>
            <div class="sub-header">Advocates &amp; Legal Consultants • Supreme Court &amp; High Court of Delhi</div>
            <div class="badge-title">Physical Litigation Dossier &amp; Intake Sheet (Basta Slip)</div>
        </div>

        <!-- Docket Ribbon -->
        <div class="docket-ribbon">
            <span>DOCKET FILE: <strong>CHAMBER-CL-{{ str_pad($client->id, 5, '0', STR_PAD_LEFT) }}</strong></span>
            <span>INTAKE: <strong>{{ $client->created_at->format('d/m/Y') }}</strong></span>
            <span>MODE: <strong>{{ strtoupper(str_replace('_', ' ', $client->onboarding_mode)) }}</strong></span>
        </div>

        <!-- Section: Litigant Particulars -->
        <div class="section-title">
            <span>I. Litigant / Entity Identification (Court Memo)</span>
            <span>{{ ucfirst($client->category ?? $client->type) }}</span>
        </div>
        <div class="grid-2">
            <div class="field">
                <span class="field-label">Party Legal Name</span>
                <span class="field-val">{{ $client->name }}</span>
            </div>
            <div class="field">
                <span class="field-label">Parentage / Spouse</span>
                <span class="field-val">{{ $client->father_husband_name ?: ($client->contact_person ?: '—') }}</span>
            </div>
            <div class="field">
                <span class="field-label">Permanent Account Number (PAN)</span>
                <span class="field-val" style="font-family: monospace;">{{ $client->pan ?? 'NOT PROVIDED' }}</span>
            </div>
            <div class="field">
                <span class="field-label">Aadhaar (UIDAI Masked)</span>
                <span class="field-val" style="font-family: monospace;">{{ $client->masked_aadhaar ?? 'NOT PROVIDED' }}</span>
            </div>
            <div class="field">
                <span class="field-label">Age &amp; Gender</span>
                <span class="field-val">{{ $client->age ? $client->age . ' Yrs' : '—' }} / {{ ucfirst($client->gender ?? '—') }}</span>
            </div>
            <div class="field">
                <span class="field-label">Occupation / Trade</span>
                <span class="field-val">{{ $client->occupation ?? '—' }}</span>
            </div>
        </div>

        <!-- Section: Address & Jurisdiction -->
        <div class="section-title">
            <span>II. Address for Service &amp; Local Jurisdiction</span>
        </div>
        <div class="grid-2">
            <div class="field" style="grid-column: span 2;">
                <span class="field-label">Full Street / Premise Address</span>
                <span class="field-val">{{ $client->full_address }}</span>
            </div>
            <div class="field">
                <span class="field-label">District &amp; State</span>
                <span class="field-val">{{ $client->district ?: 'Delhi' }}, {{ $client->state ?: 'Delhi' }} (PIN: {{ $client->pincode ?: '—' }})</span>
            </div>
            <div class="field" style="background: #fdfaf2; padding: 4px; border: 1px dashed #d4cebe;">
                <span class="field-label" style="color: #78350f;">Police Station (Thana) Jurisdiction</span>
                <span class="field-val" style="font-weight: bold; color: #78350f;">P.S. {{ $client->police_station ?? 'NOT RECORDED' }}</span>
            </div>
        </div>

        <!-- Section: Representation & POA (If applicable) -->
        @if($client->representation_mode !== 'self' || $client->representative_name)
        <div class="section-title">
            <span>III. Representation / Power of Attorney Particulars</span>
            <span>{{ ucwords(str_replace('_', ' ', $client->representation_mode)) }}</span>
        </div>
        <div class="grid-2">
            <div class="field">
                <span class="field-label">Representative Name &amp; Relation</span>
                <span class="field-val">{{ $client->representative_name }} ({{ $client->representative_relation }})</span>
            </div>
            <div class="field">
                <span class="field-label">Representative Contact Phone</span>
                <span class="field-val">{{ $client->representative_phone ?? '—' }}</span>
            </div>
            <div class="field">
                <span class="field-label">POA Registration Details</span>
                <span class="field-val">{{ $client->poa_registration_number ?? 'Special Authority / Next Friend' }}</span>
            </div>
            <div class="field">
                <span class="field-label">Sub-Registrar Office</span>
                <span class="field-val">{{ $client->poa_sub_registrar_office ?? '—' }}</span>
            </div>
        </div>
        @endif

        <!-- Section: Joint Litigants / Members (If applicable) -->
        @if($client->members->isNotEmpty())
        <div class="section-title">
            <span>IV. Co-Litigants &amp; Joint Parties ({{ $client->members->count() }})</span>
        </div>
        <table class="table-box">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Co-Party Name</th>
                    <th>Relationship</th>
                    <th>PAN</th>
                    <th>Aadhaar</th>
                    <th>Phone</th>
                </tr>
            </thead>
            <tbody>
                @foreach($client->members as $idx => $member)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td><strong>{{ $member->name }}</strong></td>
                    <td>{{ $member->relationship }}</td>
                    <td>{{ $member->pan ?: '—' }}</td>
                    <td>{{ $member->masked_aadhaar ?: '—' }}</td>
                    <td>{{ $member->phone ?: '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Section: Associated Cases -->
        <div class="section-title">
            <span>V. Active Court Dockets &amp; Retainers</span>
        </div>
        <table class="table-box">
            <thead>
                <tr>
                    <th>Case / Docket #</th>
                    <th>Title / Matter</th>
                    <th>Court Jurisdiction</th>
                    <th>Stage</th>
                    <th>Lead Advocate</th>
                </tr>
            </thead>
            <tbody>
                @forelse($client->matters as $matter)
                <tr>
                    <td style="font-family: monospace; font-weight: bold;">{{ $matter->case_number }}</td>
                    <td>{{ $matter->title }}</td>
                    <td>{{ $matter->court_name ?? 'District Court' }}</td>
                    <td>{{ ucfirst($matter->stage) }}</td>
                    <td>{{ $matter->leadAttorney->name ?? 'Chambers Counsel' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #888888;">General Retainer / Intake in progress. No cases assigned.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Signatures & Verification Seal -->
        <div class="signatures">
            <div class="sig-block">
                <div style="height: 40px;"></div>
                Signature of Litigant / POA Holder
            </div>
            <div class="sig-block">
                <div style="height: 40px; font-style: italic; color: #666666;">Verified in Chamber</div>
                <strong>{{ $client->primaryAttorney->name ?? 'Advocate-on-Record' }}</strong><br>
                Chamber Seal &amp; Signature
            </div>
        </div>
    </div>
</body>
</html>
