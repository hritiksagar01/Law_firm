<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TestManagementSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TestManagementController extends Controller
{
    protected TestManagementSystem $testSystem;

    public function __construct(TestManagementSystem $testSystem)
    {
        $this->testSystem = $testSystem;
    }

    /**
     * Display the Automated Test Management Dashboard.
     */
    public function index(Request $request)
    {
        $results = Cache::get('sjm_test_results');

        if (! $results || $request->has('run')) {
            $results = $this->testSystem->runAll();
            Cache::put('sjm_test_results', $results, now()->addHours(24));
        }

        return view('admin.tests.index', compact('results'));
    }

    /**
     * Run all test suites and return JSON or redirect back.
     */
    public function runAll(Request $request)
    {
        $results = $this->testSystem->runAll();
        Cache::put('sjm_test_results', $results, now()->addHours(24));

        if ($request->wantsJson()) {
            return response()->json($results);
        }

        return back()->with('success', "Audit completed: {$results['passed']}/{$results['total_tests']} tests passed in {$results['duration_ms']}ms.");
    }
}
