<?php

namespace App\Http\Controllers;

use App\Models\PlatformSetting;
use Illuminate\View\View;

class PublicContentController extends Controller
{
    /**
     * Display the Chambers & Platform About page.
     */
    public function about(): View
    {
        return view('public.about', [
            'data' => PlatformSetting::footerData(),
            'platformName' => PlatformSetting::platformName(),
            'logoUrl' => PlatformSetting::logoUrl(),
        ]);
    }

    /**
     * Display Chambers Contact & Registry details.
     */
    public function contact(): View
    {
        return view('public.contact', [
            'data' => PlatformSetting::footerData(),
            'platformName' => PlatformSetting::platformName(),
            'logoUrl' => PlatformSetting::logoUrl(),
        ]);
    }

    /**
     * Display Client Privilege & Data Privacy Policy.
     */
    public function privacy(): View
    {
        return view('public.privacy', [
            'data' => PlatformSetting::footerData(),
            'platformName' => PlatformSetting::platformName(),
            'logoUrl' => PlatformSetting::logoUrl(),
        ]);
    }

    /**
     * Display Platform Terms of Service & Governance.
     */
    public function terms(): View
    {
        return view('public.terms', [
            'data' => PlatformSetting::footerData(),
            'platformName' => PlatformSetting::platformName(),
            'logoUrl' => PlatformSetting::logoUrl(),
        ]);
    }
}
