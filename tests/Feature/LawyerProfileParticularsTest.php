<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MultiFirmSjmSeeder;
use Tests\TestCase;

class LawyerProfileParticularsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed(DatabaseSeeder::class);
        $this->seed(MultiFirmSjmSeeder::class);
    }

    /**
     * Test /profile renders successfully and displays the professional credentials, practice areas, and chamber roles.
     */
    public function test_lawyer_profile_renders_all_professional_credentials_and_fields(): void
    {
        $lawyer = User::where('role', 'partner')->firstOrFail();

        $response = $this->actingAs($lawyer)->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Practice Areas');
        $response->assertSee('Practice Department');
        $response->assertSee('Office Location');
        $response->assertSee('Timezone');
        $response->assertSee('Attorney Bar Number');
        $response->assertSee('Jurisdiction / Bar Council');
        $response->assertSee('Admission Date');
        $response->assertSee('Chamber Role');

        // Check that requested roles are present in the select options
        $response->assertSee('Managing Attorney');
        $response->assertSee('Attorney');
        $response->assertSee('Associate');
        $response->assertSee('Paralegal');
        $response->assertSee('Legal Assistant');
        $response->assertSee('Staff');
    }

    /**
     * Test updating lawyer profile with all requested professional particulars.
     */
    public function test_lawyer_profile_updates_professional_credentials_and_chamber_role(): void
    {
        $lawyer = User::where('role', 'partner')->firstOrFail();

        $updateData = [
            'name' => 'Adv. Rajeshwar Sharma',
            'email' => $lawyer->email,
            'secondary_email' => 'rajeshwar.chambers@lawfirm.in',
            'phone' => '+91 98765 43210',
            'title' => 'Senior Dispute Resolution Counsel',
            'role' => 'managing_attorney',
            'department' => 'Dispute Resolution & Appellate Litigation',
            'office_location' => 'Chamber 402, High Court Lawyers Chambers, New Delhi',
            'timezone' => 'Asia/Kolkata',
            'bar_number' => 'D/4819/2012',
            'jurisdiction' => 'Bar Council of Delhi & Supreme Court Bar Association',
            'admission_date' => '2012-08-24',
            'practice_areas' => [
                'Commercial Litigation & Arbitration',
                'Corporate & Insolvency (IBC / NCLT)',
                'Constitutional & Writ Jurisdiction',
            ],
        ];

        $response = $this->actingAs($lawyer)->put(route('profile.update'), $updateData);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        $lawyer->refresh();

        $this->assertEquals('Adv. Rajeshwar Sharma', $lawyer->name);
        $this->assertEquals('rajeshwar.chambers@lawfirm.in', $lawyer->secondary_email);
        $this->assertEquals('managing_attorney', $lawyer->role);
        $this->assertEquals('Dispute Resolution & Appellate Litigation', $lawyer->department);
        $this->assertEquals('Chamber 402, High Court Lawyers Chambers, New Delhi', $lawyer->office_location);
        $this->assertEquals('Asia/Kolkata', $lawyer->timezone);
        $this->assertEquals('D/4819/2012', $lawyer->bar_number);
        $this->assertEquals('Bar Council of Delhi & Supreme Court Bar Association', $lawyer->jurisdiction);
        $this->assertEquals('2012-08-24', $lawyer->admission_date?->format('Y-m-d'));
        $this->assertIsArray($lawyer->practice_areas);
        $this->assertContains('Commercial Litigation & Arbitration', $lawyer->practice_areas);
        $this->assertContains('Corporate & Insolvency (IBC / NCLT)', $lawyer->practice_areas);
        $this->assertContains('Constitutional & Writ Jurisdiction', $lawyer->practice_areas);
    }

    /**
     * Test that all 6 chamber roles can be saved and verified in the database.
     */
    public function test_lawyer_profile_accepts_all_requested_chamber_roles(): void
    {
        $lawyer = User::where('role', 'partner')->firstOrFail();

        $rolesToTest = [
            'managing_attorney',
            'attorney',
            'associate',
            'paralegal',
            'legal_assistant',
            'staff',
        ];

        foreach ($rolesToTest as $role) {
            $response = $this->actingAs($lawyer)->put(route('profile.update'), [
                'name' => $lawyer->name,
                'email' => $lawyer->email,
                'role' => $role,
            ]);

            $response->assertRedirect(route('profile.show'));
            $response->assertSessionHas('success');

            $lawyer->refresh();
            $this->assertEquals($role, $lawyer->role);
        }
    }
}
