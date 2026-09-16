<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Practice Management Brand & Identity
    |--------------------------------------------------------------------------
    |
    | Easily customize the platform name, logo title, and branding.
    | Can be overridden in your .env via LEGAL_APP_NAME.
    |
    */
    'app_name' => env('LEGAL_APP_NAME', 'Vennamraj Associates'),
    'tagline' => 'Advocates and Legal Consultants',
    'logo_path' => env('LEGAL_LOGO_PATH', '/logo.png'),

    /*
    |--------------------------------------------------------------------------
    | Currency & Financial Settings
    |--------------------------------------------------------------------------
    |
    | Indian Rupee (₹ INR) standard for all fee billing, court fee registers,
    | advance retainer deposits, and GST invoicing.
    |
    */
    'currency_symbol' => env('LEGAL_CURRENCY_SYMBOL', '₹'),
    'currency_code' => env('LEGAL_CURRENCY_CODE', 'INR'),
    'tax_label' => 'GST (18%)',
    'default_hourly_rate' => 7500.00,

    /*
    |--------------------------------------------------------------------------
    | Indian Judicial Forums, Courts & Tribunals
    |--------------------------------------------------------------------------
    |
    | Standard Indian courts where advocates practice and file dockets.
    |
    */
    'courts' => [
        'Supreme Court of India, New Delhi',
        'High Court of Delhi, New Delhi',
        'High Court of Judicature at Bombay',
        'High Court of Karnataka, Bengaluru',
        'National Company Law Tribunal (NCLT), Principal Bench',
        'National Company Law Appellate Tribunal (NCLAT)',
        'District & Sessions Court, Patiala House, New Delhi',
        'District & Sessions Court, Saket, New Delhi',
        'Debt Recovery Tribunal (DRT), Delhi Bench',
        'National Consumer Disputes Redressal Commission (NCDRC)',
        'Arbitration Tribunal, New Delhi',
    ],

    /*
    |--------------------------------------------------------------------------
    | Indian Legal Practice Focus Areas
    |--------------------------------------------------------------------------
    |
    | Practice disciplines recognized across State Bar Councils.
    |
    */
    'practice_areas' => [
        'Commercial Litigation & Arbitration',
        'Corporate & Insolvency (IBC / NCLT)',
        'Criminal Defense & Bail Applications',
        'Banking, Cheque Bounce (Sec 138 NI Act) & DRT',
        'Constitutional & Writ Petitions (Art 226/32)',
        'Intellectual Property & Trademark Disputes',
        'Taxation, Customs & GST Appeals',
        'Real Estate, RERA & Property Disputes',
        'Family Law & Matrimonial Matters',
        'Labor & Industrial Disputes',
    ],

    /*
    |--------------------------------------------------------------------------
    | Standard Case Types & Nomenclature
    |--------------------------------------------------------------------------
    |
    | Abbreviations commonly used in Indian Court cause lists and case files.
    |
    */
    'case_types' => [
        'CS (COMM)' => 'Commercial Suit',
        'W.P.(C)' => 'Writ Petition (Civil)',
        'SLP (C)' => 'Special Leave Petition (Civil)',
        'CP (IB)' => 'Company Petition (Insolvency)',
        'ARB.P.' => 'Arbitration Petition',
        'BAIL APPLN.' => 'Bail Application',
        'CC' => 'Criminal Complaint (Sec 138 NI Act)',
        'O.M.P.' => 'Original Miscellaneous Petition',
        'FAO' => 'First Appeal from Order',
    ],

    /*
    |--------------------------------------------------------------------------
    | 1-Click Showcase Demo Profiles (Indian Personas)
    |--------------------------------------------------------------------------
    |
    | Dedicated Indian legal personas for instant 1-click evaluation.
    |
    */
    'demo_profiles' => [
        'partner' => [
            'name' => 'Adv. Rajesh Sharma',
            'title' => 'Senior Advocate & Managing Partner',
            'email' => 'rajesh@sharmalegal.in',
            'password' => 'password123',
            'hourly_rate' => 12000.00,
            'role' => 'partner',
            'bar_enrollment' => 'D/1420/2005 (Bar Council of Delhi)',
            'avatar_url' => 'https://images.unsplash.com/photo-1556157382-97eda2d62296?w=150&auto=format&fit=crop&q=80',
        ],
        'associate' => [
            'name' => 'Adv. Priya Nair',
            'title' => 'Senior Associate Counsel',
            'email' => 'priya@sharmalegal.in',
            'password' => 'password123',
            'hourly_rate' => 5500.00,
            'role' => 'associate',
            'bar_enrollment' => 'D/2891/2016 (Bar Council of Delhi)',
            'avatar_url' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=150&auto=format&fit=crop&q=80',
        ],
        'clerk' => [
            'name' => 'Amit Verma',
            'title' => 'Law Clerk & Court Munshi',
            'email' => 'amit@sharmalegal.in',
            'password' => 'password123',
            'hourly_rate' => 1500.00,
            'role' => 'paralegal',
            'bar_enrollment' => 'Delhi High Court Clerk Reg. #4921',
            'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
        ],
        'client' => [
            'name' => 'Vikram Malhotra',
            'title' => 'Managing Director, Malhotra Enterprises Pvt Ltd',
            'email' => 'vikram@malhotragroup.in',
            'password' => 'password123',
            'role' => 'client',
            'avatar_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80',
        ],
    ],
];
