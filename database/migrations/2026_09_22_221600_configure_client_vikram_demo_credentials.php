<?php

use App\Models\Client;
use App\Models\Firm;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations to configure Client Vikram credentials for demo quick-fill.
     */
    public function up(): void
    {
        $firm = Firm::first();
        if (! $firm) {
            return;
        }

        $user = User::where('email', 'hritik.srivastava28@gmail.com')->first();

        if ($user) {
            $user->update([
                'name' => 'Vikram Malhotra',
                'role' => 'client',
                'password' => Hash::make('12345678'),
                'firm_id' => $user->firm_id ?: $firm->id,
                'status' => 'active',
            ]);
        } else {
            $oldUser = User::where('email', 'vikram@malhotragroup.in')->first();
            if ($oldUser) {
                $oldUser->update([
                    'email' => 'hritik.srivastava28@gmail.com',
                    'password' => Hash::make('12345678'),
                    'role' => 'client',
                    'status' => 'active',
                ]);
                $user = $oldUser;
            } else {
                $user = User::create([
                    'name' => 'Vikram Malhotra',
                    'email' => 'hritik.srivastava28@gmail.com',
                    'password' => Hash::make('12345678'),
                    'role' => 'client',
                    'firm_id' => $firm->id,
                    'status' => 'active',
                ]);
            }
        }

        $client = Client::where('user_id', $user->id)
            ->orWhere('email', 'hritik.srivastava28@gmail.com')
            ->orWhere('email', 'vikram@malhotragroup.in')
            ->first();

        if ($client) {
            $client->update([
                'user_id' => $user->id,
                'email' => 'hritik.srivastava28@gmail.com',
                'portal_status' => 'active',
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
