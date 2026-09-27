<?php

use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $lawyerRole = Role::where('slug', 'lawyer')->first();

        // 1. Fix Adv. Rajesh Sharma (hritiksagar.tech@gmail.com)
        $rajesh = User::where('email', 'hritiksagar.tech@gmail.com')->first();
        if ($rajesh) {
            $rajesh->update([
                'role' => 'partner',
                'role_id' => $adminRole?->id,
            ]);

            // Unlink any Client record pointing to Adv. Rajesh Sharma user_id
            Client::where('user_id', $rajesh->id)->update([
                'user_id' => null,
            ]);

            // If a client has the exact email as Adv. Rajesh, change it so Portal fallback doesn't match
            Client::where('email', 'hritiksagar.tech@gmail.com')->update([
                'email' => 'client.sagar@sharmalegal.in',
            ]);
        }

        // 2. Ensure all partners/admins have admin role_id, and lawyers have lawyer role_id
        if ($adminRole) {
            User::whereIn('role', ['partner', 'senior_partner', 'admin'])
                ->where('role_id', '!=', $adminRole->id)
                ->update(['role_id' => $adminRole->id]);
        }

        if ($lawyerRole) {
            User::whereIn('role', ['associate', 'lawyer', 'attorney'])
                ->where('role_id', '!=', $lawyerRole->id)
                ->update(['role_id' => $lawyerRole->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op
    }
};
