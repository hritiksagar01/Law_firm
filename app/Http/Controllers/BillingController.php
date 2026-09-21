<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Matter;
use App\Models\TimeEntry;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    /**
     * Display the financial, billing, expense, and ledger hub.
     */
    public function index(Request $request)
    {
        $firmId = Auth::user()->firm_id ?? 1;
        $tab = $request->input('tab', 'invoices');

        $timeEntries = TimeEntry::where('firm_id', $firmId)
            ->with(['matter.client', 'user'])
            ->latest('entry_date')
            ->get();

        $expenses = Expense::where('firm_id', $firmId)
            ->with(['matter.client', 'user'])
            ->latest('date')
            ->get();

        $invoices = Invoice::where('firm_id', $firmId)
            ->with(['client', 'matter'])
            ->latest('issue_date')
            ->get();

        $transactions = Transaction::where('firm_id', $firmId)
            ->with(['matter', 'client', 'invoice'])
            ->latest('date')
            ->get();

        $matters = Matter::where('firm_id', $firmId)->get();
        $clients = Client::where('firm_id', $firmId)->get();

        // Financial KPIs
        $unbilledHoursAmount = TimeEntry::where('firm_id', $firmId)->where('status', 'unbilled')->sum('total_amount');
        $unbilledExpensesAmount = Expense::where('firm_id', $firmId)->where('status', 'unbilled')->where('is_billable', true)->sum('amount');
        $outstandingInvoicesAmount = Invoice::where('firm_id', $firmId)->whereIn('status', ['sent', 'overdue'])->sum('total_amount') - Invoice::where('firm_id', $firmId)->whereIn('status', ['sent', 'overdue'])->sum('amount_paid');
        $totalCollected = Transaction::where('firm_id', $firmId)->where('type', 'payment')->sum('amount');

        return view('billing.index', compact(
            'tab',
            'timeEntries',
            'expenses',
            'invoices',
            'transactions',
            'matters',
            'clients',
            'unbilledHoursAmount',
            'unbilledExpensesAmount',
            'outstandingInvoicesAmount',
            'totalCollected'
        ));
    }

    /**
     * Store a Billable Time Entry
     */
    public function storeTimeEntry(Request $request)
    {
        $firmId = Auth::user()->firm_id ?? 1;

        $validated = $request->validate([
            'matter_id' => 'required|exists:matters,id',
            'hours' => 'required|numeric|min:0.1',
            'activity_code' => 'required|string',
            'narrative' => 'nullable|string',
            'is_billable' => 'nullable',
            'entry_date' => 'required|date',
        ]);

        $activityNames = [
            'L120' => 'Analysis & Strategy',
            'L110' => 'Fact Investigation & Client Conference',
            'L330' => 'Depositions & Oral Arguments',
            'A104' => 'Document & Evidence Review',
            'B110' => 'Written Pleadings & Notice Drafting',
            'L240' => 'Court Registry Filing & Compliance',
        ];

        $rate = Auth::user()->hourly_rate ?? 500.00;
        $hours = (float) $validated['hours'];

        TimeEntry::create([
            'firm_id' => $firmId,
            'matter_id' => $validated['matter_id'],
            'user_id' => Auth::id(),
            'hours' => $hours,
            'rate' => $rate,
            'total_amount' => $hours * $rate,
            'activity_code' => $validated['activity_code'],
            'activity_name' => $activityNames[$validated['activity_code']] ?? 'Legal Services Rendered',
            'narrative' => $validated['narrative'] ?? 'Professional advocacy services rendered.',
            'is_billable' => $request->has('is_billable'),
            'status' => 'unbilled',
            'entry_date' => $validated['entry_date'],
        ]);

        return redirect()->route('billing.index', ['tab' => 'time-entries'])
            ->with('success', "Logged {$hours} hrs under {$validated['activity_code']}.");
    }

    /**
     * Store a Case-Related Expense
     */
    public function storeExpense(Request $request)
    {
        $firmId = Auth::user()->firm_id ?? 1;

        $validated = $request->validate([
            'matter_id' => 'nullable|exists:matters,id',
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'is_billable' => 'nullable',
            'receipt' => 'nullable|file|max:10240', // 10MB
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'local');
        }

        Expense::create([
            'firm_id' => $firmId,
            'matter_id' => $validated['matter_id'] ?? null,
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'description' => $validated['description'],
            'receipt_path' => $receiptPath,
            'is_billable' => $request->has('is_billable'),
            'status' => 'unbilled',
        ]);

        return redirect()->route('billing.index', ['tab' => 'expenses'])
            ->with('success', 'Expense of ₹'.number_format($validated['amount'], 2).' logged.');
    }

    /**
     * Generate an Invoice from Unbilled WIP Hours and Expenses
     */
    public function generateInvoice(Request $request)
    {
        $firmId = Auth::user()->firm_id ?? 1;

        $validated = $request->validate([
            'matter_id' => 'required|exists:matters,id',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $matter = Matter::where('firm_id', $firmId)->with('client')->findOrFail($validated['matter_id']);

        // Aggregate unbilled hours
        $unbilledHours = TimeEntry::where('matter_id', $matter->id)
            ->where('status', 'unbilled')
            ->where('is_billable', true)
            ->sum('total_amount');

        // Aggregate unbilled expenses
        $unbilledExpenses = Expense::where('matter_id', $matter->id)
            ->where('status', 'unbilled')
            ->where('is_billable', true)
            ->sum('amount');

        $subtotal = $unbilledHours + $unbilledExpenses;

        if ($subtotal <= 0) {
            $subtotal = 15000.00; // Baseline professional fee retainer if no specific WIP
        }

        $taxRate = (float) ($validated['tax_rate'] ?? 18.00); // Default 18% GST in India
        $taxAmount = ($subtotal * $taxRate) / 100;
        $totalAmount = $subtotal + $taxAmount;

        $year = date('Y');
        $randomSeq = str_pad((string) (Invoice::where('firm_id', $firmId)->count() + 1), 4, '0', STR_PAD_LEFT);
        $invoiceNumber = "INV-{$year}-{$randomSeq}";

        $invoice = Invoice::create([
            'firm_id' => $firmId,
            'matter_id' => $matter->id,
            'client_id' => $matter->client_id,
            'invoice_number' => $invoiceNumber,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'amount_paid' => 0.00,
            'status' => 'sent',
        ]);

        // Mark associated WIP entries as billed
        TimeEntry::where('matter_id', $matter->id)->where('status', 'unbilled')->update(['status' => 'billed']);
        Expense::where('matter_id', $matter->id)->where('status', 'unbilled')->update(['status' => 'billed']);

        return redirect()->route('billing.index', ['tab' => 'invoices'])
            ->with('success', "Invoice {$invoiceNumber} for ₹".number_format($totalAmount, 2)." successfully generated for {$matter->client->name}.");
    }

    /**
     * Settle an Invoice via Payment Method
     */
    public function settleInvoice(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $amount = (float) $validated['amount'];
        $newAmountPaid = $invoice->amount_paid + $amount;
        $status = ($newAmountPaid >= $invoice->total_amount) ? 'paid' : 'sent';

        $invoice->update([
            'amount_paid' => $newAmountPaid,
            'status' => $status,
        ]);

        // Record Transaction in Ledger
        Transaction::create([
            'firm_id' => $invoice->firm_id,
            'matter_id' => $invoice->matter_id,
            'client_id' => $invoice->client_id,
            'invoice_id' => $invoice->id,
            'type' => 'payment',
            'amount' => $amount,
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'] ?? ('TXN-'.time()),
            'date' => now()->toDateString(),
            'notes' => $validated['notes'] ?? "Settlement for {$invoice->invoice_number}",
        ]);

        return redirect()->route('billing.index', ['tab' => 'invoices'])
            ->with('success', 'Payment of ₹'.number_format($amount, 2)." credited toward {$invoice->invoice_number}.");
    }

    /**
     * Record a Manual Transaction or Client Advance Retainer
     */
    public function storeTransaction(Request $request)
    {
        $firmId = Auth::user()->firm_id ?? 1;

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'matter_id' => 'nullable|exists:matters,id',
            'type' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $txn = Transaction::create([
            'firm_id' => $firmId,
            'client_id' => $validated['client_id'],
            'matter_id' => $validated['matter_id'] ?? null,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'] ?? ('ADV-'.time()),
            'date' => $validated['date'],
            'notes' => $validated['notes'],
        ]);

        // Update Client Trust / Advance Balance
        if ($validated['type'] === 'advance_deposit') {
            $client = Client::find($validated['client_id']);
            if ($client) {
                $client->increment('trust_balance', $validated['amount']);
            }
        }

        return redirect()->route('billing.index', ['tab' => 'ledger'])
            ->with('success', 'Transaction of ₹'.number_format($validated['amount'], 2).' registered in chambers ledger.');
    }
}
