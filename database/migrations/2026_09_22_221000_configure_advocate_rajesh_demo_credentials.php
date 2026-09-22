<?php

use App\Models\Firm;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations to configure Advocate Rajesh credentials for demo quick-fill.
     */
    public function up(): void
    {
        $firm = Firm::first();
        if (! $firm) {
            return;
        }

        $user = User::where('email', 'hritiksagar.tech@gmail.com')->first();

        if ($user) {
            $user->update([
                'name' => 'Adv. Rajesh Sharma',
                'role' => 'partner',
                'password' => Hash::make('12345678'),
                'firm_id' => $user->firm_id ?: $firm->id,
                'status' => 'active',
            ]);
        } else {
            User::create([
                'name' => 'Adv. Rajesh Sharma',
                'email' => 'hritiksagar.tech@gmail.com',
                'password' => Hash::make('12345678'),
                'role' => 'partner',
                'firm_id' => $firm->id,
                'status' => 'active',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Preserved
    }
};
