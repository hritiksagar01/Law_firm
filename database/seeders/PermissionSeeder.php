<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Define all granular permissions across legal modules
        $permissions = [
            // Matters / Cases
            ['name' => 'View Matters', 'slug' => 'matters.view', 'module' => 'Matters', 'description' => 'View case files and dossier details'],
            ['name' => 'View Matters (Singular)', 'slug' => 'matter.view', 'module' => 'Matters', 'description' => 'View case files and dossier details'],
            ['name' => 'Create Matters', 'slug' => 'matters.create', 'module' => 'Matters', 'description' => 'Open new legal cases and dossiers'],
            ['name' => 'Create Matters (Singular)', 'slug' => 'matter.create', 'module' => 'Matters', 'description' => 'Open new legal cases and dossiers'],
            ['name' => 'Edit Matters', 'slug' => 'matters.edit', 'module' => 'Matters', 'description' => 'Update case stages, court information, and notes'],
            ['name' => 'Edit Matters (Singular)', 'slug' => 'matter.edit', 'module' => 'Matters', 'description' => 'Update case stages, court information, and notes'],
            ['name' => 'Close Matters', 'slug' => 'matters.close', 'module' => 'Matters', 'description' => 'Close and conclude resolved matters'],
            ['name' => 'Close Matters (Singular)', 'slug' => 'matter.close', 'module' => 'Matters', 'description' => 'Close and conclude resolved matters'],
            ['name' => 'Delete Matters', 'slug' => 'matters.delete', 'module' => 'Matters', 'description' => 'Archive or delete legal cases'],
            ['name' => 'Assign Attorneys', 'slug' => 'matters.assign', 'module' => 'Matters', 'description' => 'Assign lead attorneys and team to cases'],

            // Clients
            ['name' => 'View Clients', 'slug' => 'clients.view', 'module' => 'Clients', 'description' => 'Access client directory and profile records'],
            ['name' => 'View Clients (Singular)', 'slug' => 'client.view', 'module' => 'Clients', 'description' => 'Access client directory and profile records'],
            ['name' => 'Create Clients', 'slug' => 'clients.create', 'module' => 'Clients', 'description' => 'Onboard new individual or corporate clients'],
            ['name' => 'Edit Clients', 'slug' => 'clients.edit', 'module' => 'Clients', 'description' => 'Modify client contact and legal details'],
            ['name' => 'Delete Clients', 'slug' => 'clients.delete', 'module' => 'Clients', 'description' => 'Remove or deactivate client accounts'],

            // Documents Vault
            ['name' => 'View Documents', 'slug' => 'documents.view', 'module' => 'Documents', 'description' => 'Read and preview case documents'],
            ['name' => 'View Documents (Singular)', 'slug' => 'document.view', 'module' => 'Documents', 'description' => 'Read and preview case documents'],
            ['name' => 'Upload Documents', 'slug' => 'documents.upload', 'module' => 'Documents', 'description' => 'Upload briefs, petitions, and evidence'],
            ['name' => 'Upload Documents (Singular)', 'slug' => 'document.upload', 'module' => 'Documents', 'description' => 'Upload briefs, petitions, and evidence'],
            ['name' => 'Download Documents', 'slug' => 'documents.download', 'module' => 'Documents', 'description' => 'Download original document files'],
            ['name' => 'Download Documents (Singular)', 'slug' => 'document.download', 'module' => 'Documents', 'description' => 'Download original document files'],
            ['name' => 'Share Documents', 'slug' => 'documents.share', 'module' => 'Documents', 'description' => 'Share documents with clients or external counsel'],
            ['name' => 'Share Documents (Singular)', 'slug' => 'document.share', 'module' => 'Documents', 'description' => 'Share documents with clients or external counsel'],
            ['name' => 'Request Documents', 'slug' => 'documents.request', 'module' => 'Documents', 'description' => 'Request documents from clients and third parties'],
            ['name' => 'Request Documents (Singular)', 'slug' => 'document.request', 'module' => 'Documents', 'description' => 'Request documents from clients and third parties'],
            ['name' => 'Delete Documents', 'slug' => 'documents.delete', 'module' => 'Documents', 'description' => 'Remove files from the document vault'],
            ['name' => 'Delete Documents (Singular)', 'slug' => 'document.delete', 'module' => 'Documents', 'description' => 'Remove files from the document vault'],

            // Communication / Messages
            ['name' => 'View Messages', 'slug' => 'message.view', 'module' => 'Communication', 'description' => 'View internal and client communications'],
            ['name' => 'Send Messages', 'slug' => 'message.send', 'module' => 'Communication', 'description' => 'Send internal messages and client updates'],

            // Tasks
            ['name' => 'View Tasks', 'slug' => 'tasks.view', 'module' => 'Tasks', 'description' => 'View workflow tasks and checklists'],
            ['name' => 'View Tasks (Singular)', 'slug' => 'task.view', 'module' => 'Tasks', 'description' => 'View workflow tasks and checklists'],
            ['name' => 'Create Tasks', 'slug' => 'tasks.create', 'module' => 'Tasks', 'description' => 'Create tasks and procedural steps'],
            ['name' => 'Create Tasks (Singular)', 'slug' => 'task.create', 'module' => 'Tasks', 'description' => 'Create tasks and procedural steps'],
            ['name' => 'Edit Tasks', 'slug' => 'tasks.edit', 'module' => 'Tasks', 'description' => 'Update tasks and deadlines'],
            ['name' => 'Assign Tasks', 'slug' => 'tasks.assign', 'module' => 'Tasks', 'description' => 'Delegate tasks to team members'],
            ['name' => 'Assign Tasks (Singular)', 'slug' => 'task.assign', 'module' => 'Tasks', 'description' => 'Delegate tasks to team members'],
            ['name' => 'Complete Tasks', 'slug' => 'tasks.complete', 'module' => 'Tasks', 'description' => 'Mark workflow tasks as completed'],
            ['name' => 'Complete Tasks (Singular)', 'slug' => 'task.complete', 'module' => 'Tasks', 'description' => 'Mark workflow tasks as completed'],
            ['name' => 'Delete Tasks', 'slug' => 'tasks.delete', 'module' => 'Tasks', 'description' => 'Remove tasks'],

            // Notes / Work
            ['name' => 'View Notes', 'slug' => 'note.view', 'module' => 'Work', 'description' => 'View case and client notes'],
            ['name' => 'Create Notes', 'slug' => 'note.create', 'module' => 'Work', 'description' => 'Record case notes and research memos'],

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
            ['name' => 'View Users', 'slug' => 'users.view', 'module' => 'Administration', 'description' => 'View advocate and staff directory'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'module' => 'Administration', 'description' => 'Add new staff and invite team members'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'module' => 'Administration', 'description' => 'Modify roles, titles, and team groups'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'module' => 'Administration', 'description' => 'Deactivate or remove staff accounts'],
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
                    'matters.view', 'matter.view', 'matters.create', 'matter.create', 'matters.edit', 'matter.edit', 'matters.close', 'matter.close', 'matters.assign',
                    'clients.view', 'client.view', 'clients.create', 'clients.edit',
                    'documents.view', 'document.view', 'documents.upload', 'document.upload', 'documents.download', 'document.download', 'documents.share', 'document.share', 'documents.request', 'document.request',
                    'message.view', 'message.send',
                    'tasks.view', 'task.view', 'tasks.create', 'task.create', 'tasks.edit', 'tasks.assign', 'task.assign', 'tasks.complete', 'task.complete',
                    'note.view', 'note.create',
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
                    'matters.view', 'matter.view', 'clients.view', 'client.view',
                    'documents.view', 'document.view', 'documents.upload', 'document.upload', 'documents.download', 'document.download',
                    'message.view',
                    'tasks.view', 'task.view', 'tasks.create', 'task.create', 'tasks.edit', 'tasks.complete', 'task.complete',
                    'note.view', 'note.create',
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
