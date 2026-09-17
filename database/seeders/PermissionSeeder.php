<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Define all granular permissions across legal modules
        $permissions = [
            // Matters / Cases
            ['name' => 'View Matters', 'slug' => 'matters.view', 'module' => 'Matters', 'description' => 'View case files and dossier details'],
            ['name' => 'Create Matters', 'slug' => 'matters.create', 'module' => 'Matters', 'description' => 'Open new legal cases and dossiers'],
            ['name' => 'Edit Matters', 'slug' => 'matters.edit', 'module' => 'Matters', 'description' => 'Update case stages, court information, and notes'],
            ['name' => 'Delete Matters', 'slug' => 'matters.delete', 'module' => 'Matters', 'description' => 'Archive or delete legal cases'],
            ['name' => 'Assign Attorneys', 'slug' => 'matters.assign', 'module' => 'Matters', 'description' => 'Assign lead attorneys and team to cases'],

            // Clients
            ['name' => 'View Clients', 'slug' => 'clients.view', 'module' => 'Clients', 'description' => 'Access client directory and profile records'],
            ['name' => 'Create Clients', 'slug' => 'clients.create', 'module' => 'Clients', 'description' => 'Onboard new individual or corporate clients'],
            ['name' => 'Edit Clients', 'slug' => 'clients.edit', 'module' => 'Clients', 'description' => 'Modify client contact and legal details'],
            ['name' => 'Delete Clients', 'slug' => 'clients.delete', 'module' => 'Clients', 'description' => 'Remove or deactivate client accounts'],

            // Documents Vault
            ['name' => 'View Documents', 'slug' => 'documents.view', 'module' => 'Documents', 'description' => 'Read and download case documents'],
            ['name' => 'Upload Documents', 'slug' => 'documents.upload', 'module' => 'Documents', 'description' => 'Upload briefs, petitions, and evidence'],
            ['name' => 'Delete Documents', 'slug' => 'documents.delete', 'module' => 'Documents', 'description' => 'Remove files from the document vault'],

            // Tasks
            ['name' => 'View Tasks', 'slug' => 'tasks.view', 'module' => 'Tasks', 'description' => 'View workflow tasks and checklists'],
            ['name' => 'Create Tasks', 'slug' => 'tasks.create', 'module' => 'Tasks', 'description' => 'Create tasks and procedural steps'],
            ['name' => 'Edit Tasks', 'slug' => 'tasks.edit', 'module' => 'Tasks', 'description' => 'Mark complete or update tasks'],
            ['name' => 'Assign Tasks', 'slug' => 'tasks.assign', 'module' => 'Tasks', 'description' => 'Delegate tasks to team members'],
            ['name' => 'Delete Tasks', 'slug' => 'tasks.delete', 'module' => 'Tasks', 'description' => 'Remove tasks'],

            // Legal Opinions
            ['name' => 'View Opinions', 'slug' => 'opinions.view', 'module' => 'Opinions', 'description' => 'Read legal advice and opinions'],
            ['name' => 'Create Opinions', 'slug' => 'opinions.create', 'module' => 'Opinions', 'description' => 'Draft legal opinions and case strategies'],
            ['name' => 'Review Opinions', 'slug' => 'opinions.review', 'module' => 'Opinions', 'description' => 'Review and critique drafted opinions'],
            ['name' => 'Publish Opinions', 'slug' => 'opinions.publish', 'module' => 'Opinions', 'description' => 'Approve and deliver formal legal opinions'],

            // Calendar, Hearings & Appointments
            ['name' => 'View Calendar', 'slug' => 'calendar.view', 'module' => 'Calendar', 'description' => 'View cause list, hearings, and schedule'],
            ['name' => 'Manage Hearings', 'slug' => 'hearings.manage', 'module' => 'Calendar', 'description' => 'Schedule and log court hearings'],
            ['name' => 'Manage Appointments', 'slug' => 'appointments.manage', 'module' => 'Calendar', 'description' => 'Book client consultations and meetings'],

            // Reports
            ['name' => 'View Reports', 'slug' => 'reports.view', 'module' => 'Reports', 'description' => 'Access case and client operational reports'],
            ['name' => 'Export Reports', 'slug' => 'reports.export', 'module' => 'Reports', 'description' => 'Export analytics to PDF and CSV'],

            // Settings
            ['name' => 'View Settings', 'slug' => 'settings.view', 'module' => 'Settings', 'description' => 'View firm practice preferences'],
            ['name' => 'Edit Settings', 'slug' => 'settings.edit', 'module' => 'Settings', 'description' => 'Configure practice areas, templates, and holidays'],

            // User & Practice Group Admin
            ['name' => 'View Users', 'slug' => 'users.view', 'module' => 'User Admin', 'description' => 'View advocate and staff directory'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'module' => 'User Admin', 'description' => 'Add new staff and invite team members'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'module' => 'User Admin', 'description' => 'Modify roles, titles, and team groups'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'module' => 'User Admin', 'description' => 'Deactivate or remove staff accounts'],
        ];

        foreach ($permissions as $permData) {
            Permission::firstOrCreate(
                ['slug' => $permData['slug']],
                $permData
            );
        }

        // 2. Define standard System Roles
        $roles = [
            [
                'name' => 'Firm Administrator / Managing Partner',
                'slug' => 'admin',
                'description' => 'Full administrative authority over firm matters, advocates, settings, and reports.',
                'is_system' => true,
                'permissions' => '*', // all permissions
            ],
            [
                'name' => 'Advocate / Legal Counsel',
                'slug' => 'lawyer',
                'description' => 'Manages legal cases, drafts opinions, attends court hearings, and interacts with clients.',
                'is_system' => true,
                'permissions' => [
                    'matters.view', 'matters.create', 'matters.edit', 'matters.assign',
                    'clients.view', 'clients.create', 'clients.edit',
                    'documents.view', 'documents.upload',
                    'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign',
                    'opinions.view', 'opinions.create', 'opinions.review', 'opinions.publish',
                    'calendar.view', 'hearings.manage', 'appointments.manage',
                    'reports.view', 'reports.export',
                ],
            ],
            [
                'name' => 'Paralegal / Research Associate',
                'slug' => 'paralegal',
                'description' => 'Assists advocates with legal research, document preparation, court filings, and task tracking.',
                'is_system' => true,
                'permissions' => [
                    'matters.view', 'clients.view',
                    'documents.view', 'documents.upload',
                    'tasks.view', 'tasks.create', 'tasks.edit',
                    'opinions.view', 'opinions.create',
                    'calendar.view', 'hearings.manage', 'appointments.manage',
                ],
            ],
            [
                'name' => 'Chamber Support Staff',
                'slug' => 'support_staff',
                'description' => 'Registry, reception, court registry liaison, and scheduling coordination.',
                'is_system' => true,
                'permissions' => [
                    'clients.view',
                    'calendar.view', 'appointments.manage',
                    'tasks.view',
                ],
            ],
            [
                'name' => 'Client',
                'slug' => 'client',
                'description' => 'Client portal user with read-only visibility into their own cases, hearings, and documents.',
                'is_system' => true,
                'permissions' => [],
            ],
        ];

        $allPermissionIds = Permission::pluck('id')->toArray();

        foreach ($roles as $roleData) {
            $perms = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::firstOrCreate(
                ['firm_id' => null, 'slug' => $roleData['slug']],
                $roleData
            );

            if ($perms === '*') {
                $role->permissions()->sync($allPermissionIds);
            } elseif (is_array($perms)) {
                $permIds = Permission::whereIn('slug', $perms)->pluck('id')->toArray();
                $role->permissions()->sync($permIds);
            }
        }

        // 3. Link existing users to their respective roles
        $adminRole = Role::whereNull('firm_id')->where('slug', 'admin')->first();
        $lawyerRole = Role::whereNull('firm_id')->where('slug', 'lawyer')->first();
        $paralegalRole = Role::whereNull('firm_id')->where('slug', 'paralegal')->first();
        $clientRole = Role::whereNull('firm_id')->where('slug', 'client')->first();
        $supportRole = Role::whereNull('firm_id')->where('slug', 'support_staff')->first();

        // Partner / Senior Partner -> Admin
        User::whereIn('role', ['partner', 'senior_partner', 'admin'])
            ->whereNull('role_id')
            ->update(['role_id' => $adminRole?->id]);

        // Associate -> Lawyer
        User::whereIn('role', ['associate', 'lawyer'])
            ->whereNull('role_id')
            ->update(['role_id' => $lawyerRole?->id]);

        // Paralegal -> Paralegal
        User::where('role', 'paralegal')
            ->whereNull('role_id')
            ->update(['role_id' => $paralegalRole?->id]);

        // Client -> Client
        User::where('role', 'client')
            ->whereNull('role_id')
            ->update(['role_id' => $clientRole?->id]);

        // Support / Finance -> Support Staff
        User::whereIn('role', ['support_staff', 'support', 'finance'])
            ->whereNull('role_id')
            ->update(['role_id' => $supportRole?->id]);
    }
}
