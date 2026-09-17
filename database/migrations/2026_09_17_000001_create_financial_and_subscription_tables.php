<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Subscription Plans (Starter, Professional, Enterprise)
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Enterprise Chambers", "Professional Practice", "Starter Solo"
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('interval')->default('monthly'); // monthly, yearly
            $table->integer('max_users')->default(10);
            $table->integer('max_matters')->default(100);
            $table->integer('max_storage_gb')->default(50);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Firm Subscriptions & Plan Tracking
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('status')->default('active'); // active, trialing, past_due, canceled
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();
        });

        // 3. Case & Firm Expenses (Travel, Court Fees, Administrative, Filing, Process Service)
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('matter_id')->nullable()->constrained('matters')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('category')->default('Court Fees'); // Court Fees, Travel & Conveyance, Administrative, Filing, Evidence/Inspection, Process Service
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->text('description')->nullable();
            $table->string('receipt_path')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->string('status')->default('unbilled'); // unbilled, billed, reimbursed
            $table->timestamps();
        });

        // 4. Financial Transactions & Ledger (Debits, Credits, Client Advances, Settlements)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->foreignId('matter_id')->nullable()->constrained('matters')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('type')->default('payment'); // payment, advance_deposit, expense_reimbursement, refund, adjustment
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('bank_transfer'); // bank_transfer, credit_card, cheque, cash, upi
            $table->string('reference_number')->nullable(); // e.g. UTR / Cheque No / Card Auth
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Firm Bank Accounts (For Bank Activity Reporting & Reconciliation)
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained('firms')->cascadeOnDelete();
            $table->string('bank_name'); // e.g. "State Bank of India", "HDFC Bank", "ICICI Bank"
            $table->string('account_name');
            $table->string('account_number');
            $table->string('ifsc_code')->nullable();
            $table->string('branch')->nullable();
            $table->string('account_type')->default('current'); // current, escrow_trust, savings
            $table->decimal('opening_balance', 14, 2)->default(0.00);
            $table->decimal('current_balance', 14, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};
