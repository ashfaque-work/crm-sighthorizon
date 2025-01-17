<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Utility;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arrPermissions = [
            'Manage Content Marketting',
            'Manage Lead Database',
            'Manage Revenue',
            'Manage Vendors and Contractor Details',
            'Manage Sales Pipeline',
            'Manage Customer Feedback',
            'Manage Audit',
            'Manage Legal',
            'Manage Team Members',
            'Manage Users',
            'Create User',
            'Edit User',
            'Delete User',
            'Manage Roles',
            'Create Role',
            'Edit Role',
            'Delete Role',
            'Manage Permissions',
            'Create Permission',
            'Edit Permission',
            'Delete Permission',
            'Manage Languages',
            'Create Language',
            'Edit Language',
            'System Settings',
            'Manage Pipelines',
            'Create Pipeline',
            'Edit Pipeline',
            'Delete Pipeline',
            'Manage Sources',
            'Create Source',
            'Edit Source',
            'Delete Source',
            'Manage Payments',
            'Create Payment',
            'Edit Payment',
            'Delete Payment',
            'Manage Expense Categories',
            'Create Expense Category',
            'Edit Expense Category',
            'Delete Expense Category',
            'Manage Stages',
            'Create Stage',
            'Edit Stage',
            'Delete Stage',
            'Manage Lead Stages',
            'Create Lead Stage',
            'Edit Lead Stage',
            'Delete Lead Stage',
            'Manage Leads',
            'Create Lead',
            'Edit Lead',
            'Delete Lead',
            'View Lead',
            'Move Lead',
            'Create Label',
            'Manage Labels',
            'Edit Label',
            'Delete Label',
            'Manage Custom Fields',
            'Create Custom Field',
            'Edit Custom Field',
            'Delete Custom Field',
            'Manage Products',
            'Create Product',
            'Edit Product',
            'Delete Product',
            'Manage Clients',
            'Create Client',
            'Edit Client',
            'Delete Client',
            'Manage Deals',
            'Create Deal',
            'Edit Deal',
            'Delete Deal',
            'Move Deal',
            'Manage Tasks',
            'Create Task',
            'Edit Task',
            'Delete Task',
            'Manage Expenses',
            'Create Expense',
            'Edit Expense',
            'Delete Expense',
            'Manage Invoices',
            'Create Invoice',
            'Edit Invoice',
            'Delete Invoice',
            'Manage Taxes',
            'Create Tax',
            'Edit Tax',
            'Delete Tax',
            'Manage Contract Types',
            'Create Contract Type',
            'Edit Contract Type',
            'Delete Contract Type',
            'Manage Contracts',
            'Create Contract',
            'Edit Contract',
            'Delete Contract',
            'View Contract',
            'View Invoice',
            'Invoice Add Product',
            'Create Invoice Payment',
            'Invoice Edit Product',
            'Invoice Delete Product',
            'Manage Invoice Payments',
            'View Deal',
            'View Task',
            'Convert Lead To Deal',
            'Manage Deal Emails',
            'Create Deal Email',
            'Manage Deal Calls',
            'Create Deal Call',
            'Edit Deal Call',
            'Delete Deal Call',
            'Manage Lead Emails',
            'Create Lead Email',
            'Manage Lead Calls',
            'Create Lead Call',
            'Edit Lead Call',
            'Delete Lead Call',
            'Manage Estimations',
            'Create Estimation',
            'Edit Estimation',
            'Delete Estimation',
            'View Estimation',
            'Estimation Add Product',
            'Estimation Edit Product',
            'Estimation Delete Product',
            'Manage Email Templates',
            'Create Email Template',
            'Edit Email Template',
            'On-Off Email Template',
            'Edit Email Template Lang',
            // MDF Module
            'Manage MDFs',
            'Request MDF',
            'Edit MDF',
            'Delete MDF',
            'View MDF',
            'Manage MDF Types',
            'Create MDF Type',
            'Edit MDF Type',
            'Delete MDF Type',
            'Manage MDF Sub Types',
            'Create MDF Sub Type',
            'Edit MDF Sub Type',
            'Delete MDF Sub Type',
            'Manage MDF Status',
            'Create MDF Status',
            'Edit MDF Status',
            'Delete MDF Status',
            'Create MDF Payment',
            'MDF Add Expense',
            'MDF Edit Expense',
            'MDF Delete Expense',
            'Create Comment',
            'Create Note',
            'Delete Comment',
            'Delete Note',
            'Manage Form Builders',
            'Create Form Builder',
            'Edit Form Builder',
            'Delete Form Builder',
            'Manage Form Field',
            'Create Form Field',
            'Edit Form Field',
            'Delete Form Field',
            'View Form Response',
            'Invoice Report',
            'Expense Report',
            'Income vs Expense Report',
            'Manage Company Settings',
        ];

        foreach ($arrPermissions as $ap) {
            Permission::create(['name' => $ap]);
        }

        $adminRole = Role::create(
            [
                'name' => 'Owner',
                'created_by' => 0,
            ]
        );

        $adminPermissions = [
            'Manage Content Marketting',
            'Manage Lead Database',
            'Manage Revenue',
            'Manage Vendors and Contractor Details',
            'Manage Sales Pipeline',
            'Manage Customer Feedback',
            'Manage Audit',
            'Manage Legal',
            'Manage Team Members',
            'Manage Users',
            'Create User',
            'Edit User',
            'Delete User',
            'Manage Roles',
            'Create Role',
            'Edit Role',
            'Delete Role',
            'Manage Permissions',
            'Create Permission',
            'Edit Permission',
            'Delete Permission',
            'Manage Languages',
            'Create Language',
            'Edit Language',
            'System Settings',
            'Manage Pipelines',
            'Create Pipeline',
            'Edit Pipeline',
            'Delete Pipeline',
            'Manage Sources',
            'Create Source',
            'Edit Source',
            'Delete Source',
            'Manage Payments',
            'Create Payment',
            'Edit Payment',
            'Delete Payment',
            'Manage Expense Categories',
            'Create Expense Category',
            'Edit Expense Category',
            'Delete Expense Category',
            'Manage Stages',
            'Create Stage',
            'Edit Stage',
            'Delete Stage',
            'Manage Lead Stages',
            'Create Lead Stage',
            'Edit Lead Stage',
            'Delete Lead Stage',
            'Manage Leads',
            'Create Lead',
            'Edit Lead',
            'Delete Lead',
            'View Lead',
            'Move Lead',
            'Create Label',
            'Manage Labels',
            'Edit Label',
            'Delete Label',
            'Manage Custom Fields',
            'Create Custom Field',
            'Edit Custom Field',
            'Delete Custom Field',
            'Manage Products',
            'Create Product',
            'Edit Product',
            'Delete Product',
            'Manage Clients',
            'Create Client',
            'Edit Client',
            'Delete Client',
            'Manage Deals',
            'Create Deal',
            'Edit Deal',
            'Delete Deal',
            'Move Deal',
            'Manage Tasks',
            'Create Task',
            'Edit Task',
            'Delete Task',
            'Manage Expenses',
            'Create Expense',
            'Edit Expense',
            'Delete Expense',
            'Manage Invoices',
            'Create Invoice',
            'Edit Invoice',
            'Delete Invoice',
            'Manage Taxes',
            'Create Tax',
            'Edit Tax',
            'Delete Tax',
            'Manage Contract Types',
            'Create Contract Type',
            'Edit Contract Type',
            'Delete Contract Type',
            'Manage Contracts',
            'Create Contract',
            'Edit Contract',
            'Delete Contract',
            'View Contract',
            'View Invoice',
            'Invoice Add Product',
            'Create Invoice Payment',
            'Invoice Edit Product',
            'Invoice Delete Product',
            'Manage Invoice Payments',
            'View Deal',
            'View Task',
            'Convert Lead To Deal',
            'Manage Deal Emails',
            'Create Deal Email',
            'Manage Deal Calls',
            'Create Deal Call',
            'Edit Deal Call',
            'Delete Deal Call',
            'Manage Lead Emails',
            'Create Lead Email',
            'Manage Lead Calls',
            'Create Lead Call',
            'Edit Lead Call',
            'Delete Lead Call',
            'Manage Estimations',
            'Create Estimation',
            'Edit Estimation',
            'Delete Estimation',
            'View Estimation',
            'Estimation Add Product',
            'Estimation Edit Product',
            'Estimation Delete Product',
            'Manage Email Templates',
            'Create Email Template',
            'Edit Email Template',
            'On-Off Email Template',
            'Edit Email Template Lang',
            // MDF Module
            'Manage MDFs',
            'Request MDF',
            'Edit MDF',
            'Delete MDF',
            'View MDF',
            'Manage MDF Types',
            'Create MDF Type',
            'Edit MDF Type',
            'Delete MDF Type',
            'Manage MDF Sub Types',
            'Create MDF Sub Type',
            'Edit MDF Sub Type',
            'Delete MDF Sub Type',
            'Manage MDF Status',
            'Create MDF Status',
            'Edit MDF Status',
            'Delete MDF Status',
            'Create MDF Payment',
            'MDF Add Expense',
            'MDF Edit Expense',
            'MDF Delete Expense',
            'Create Comment',
            'Create Note',
            'Delete Comment',
            'Delete Note',
            'Manage Form Builders',
            'Create Form Builder',
            'Edit Form Builder',
            'Delete Form Builder',
            'Manage Form Field',
            'Create Form Field',
            'Edit Form Field',
            'Delete Form Field',
            'View Form Response',
            'Invoice Report',
            'Expense Report',
            'Income vs Expense Report',
            'Manage Company Settings',
        ];

        foreach ($adminPermissions as $ap) {
            $permission = Permission::findByName($ap);
            $adminRole->givePermissionTo($permission);
        }
        $admin = User::create(
            [
                'name' => 'Admin',
                'email' => 'admin@sighthorizon.com',
                'password' => Hash::make('Horizon@123'),
                'type' => 'Owner',
                'lang' => 'en',
                'created_by' => 0,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole($adminRole);
        $admin->defaultEmail();
        $admin->userDefaultData();

        $clientRole = Role::create(
            [
                'name' => 'Client',
                'created_by' => 0,
            ]
        );
        $clientPermissions = [
            "Manage Deals",
            "Manage Invoices",
            "View Invoice",
            "Manage Estimations",
            "View Estimation",
            "View Deal",
            "Manage Invoice Payments",
            'Create Comment',
            'Create Note',
            'Delete Comment',
            'Delete Note',
            'Manage Contracts',
            'View Contract',

        ];
        foreach ($clientPermissions as $ap) {
            $permission = Permission::findByName($ap);
            $clientRole->givePermissionTo($permission);
        }

        $userRole = Role::create(
            [
                'name' => 'Manager',
                'created_by' => $admin->id,
            ]
        );
        $userPermissions = [
            'Manage Deals',
            'Create Deal',
            'Edit Deal',
            'Delete Deal',
            'Move Deal',
            'View Deal',
            'Manage Leads',
            'Create Lead',
            'Edit Lead',
            'Delete Lead',
            'View Lead',
            'Move Lead',
            'Manage Tasks',
            'Create Task',
            'Edit Task',
            'Delete Task',
            'View Task',
            'Manage Invoices',
            'Create Invoice',
            'Edit Invoice',
            'Delete Invoice',
            "View Invoice",
            'Manage Products',
            'Create Product',
            'Edit Product',
            'Delete Product',
            'Manage Expenses',
            'Create Expense',
            'Edit Expense',
            'Delete Expense',
            'Manage Taxes',
            'Create Tax',
            'Edit Tax',
            'Delete Tax',
            'Manage Invoice Payments',
            'Create Invoice Payment',
            'Invoice Add Product',
            'Invoice Delete Product',
            'Invoice Edit Product',
            'Convert Lead To Deal',
            // MDF Module
            'Manage MDFs',
            'Request MDF',
            'Edit MDF',
            'Delete MDF',
            'View MDF',
            'MDF Add Expense',
            'MDF Edit Expense',
            'MDF Delete Expense',


        ];

        foreach ($userPermissions as $ap) {
            $permission = Permission::findByName($ap);
            $userRole->givePermissionTo($permission);
        }
        Utility::languagecreate();

        $user = User::create(
            [
                'name' => 'Manager',
                'email' => 'manager@sighthorizon.com',
                'password' => Hash::make('Horizon@123'),
                'type' => 'Manager',
                'lang' => 'en',
                'created_by' => $admin->id,
                'email_verified_at' => now(),
            ]
        );
        $user->assignRole($userRole);

        // Vendor role and permissions
        $vendorRole = Role::create(
            [
                'name' => 'Vendor',
                'created_by' => $admin->id,
            ]
        );
        $vendorPermissions = [
            "Manage Deals",
            "Manage Invoices",
            "View Invoice",
            "Manage Estimations",
            "View Estimation",
            "View Deal",
            "Manage Invoice Payments",
            'Create Comment',
            'Create Note',
            'Delete Comment',
            'Delete Note',
            'Manage Contracts',
            'View Contract',


        ];

        foreach ($vendorPermissions as $ap) {
            $permission = Permission::findByName($ap);
            $vendorRole->givePermissionTo($permission);
        }
        Utility::languagecreate();

        $user = User::create(
            [
                'name' => 'Vendor',
                'email' => 'vendor@example.com',
                'password' => Hash::make('Horizon@123'),
                'type' => 'Vendor',
                'lang' => 'en',
                'created_by' => $admin->id,
                'email_verified_at' => now(),
            ]
        );
        $user->assignRole($vendorRole);

        // Contractor role and permissions
        $contractorRole = Role::create(
            [
                'name' => 'Contractor',
                'created_by' => $admin->id,
            ]
        );
        $contractorPermissions = [
            "Manage Deals",
            "Manage Invoices",
            "View Invoice",
            "Manage Estimations",
            "View Estimation",
            "View Deal",
            "Manage Invoice Payments",
            'Create Comment',
            'Create Note',
            'Delete Comment',
            'Delete Note',
            'Manage Contracts',
            'View Contract',


        ];

        foreach ($contractorPermissions as $ap) {
            $permission = Permission::findByName($ap);
            $contractorRole->givePermissionTo($permission);
        }
        Utility::languagecreate();

        $user = User::create(
            [
                'name' => 'contractor',
                'email' => 'contractor@example.com',
                'password' => Hash::make('Horizon@123'),
                'type' => 'Contractor',
                'lang' => 'en',
                'created_by' => $admin->id,
                'email_verified_at' => now(),
            ]
        );
        $user->assignRole($contractorRole);

    }
}
