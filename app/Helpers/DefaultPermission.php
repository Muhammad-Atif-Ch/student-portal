<?php 

namespace App\Helpers;

use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

if (!function_exists('setPermissionsAndModules')) 
{
    function setPermissionsAndModules($scema = null): array
    {
        $allPermissions = Permission::all()->pluck('name')->toArray();

        $roleSchemas = [
            'SAAdmin' => ['modules' => [], 'permissions' => $allPermissions],
            'UnlimitAdmin' => ['modules' => [], 'permissions' => $allPermissions],
            'Vendor' => [
                'modules' => ['Dashboard', 'Product', 'Priceline', 'Designation', 'Contact', 'Employee', 'Supplier', 'Outlet', 'Purchase', 'MainStock', 'Distribution', 'OutletStock', 'Role', 'User', 'ProductGroup', 'ProductGroupList', 'Campaign', 'Reports', 'Ticket', 'Attachment'],
                'permissions' => ['category-view', 'uom-view', 'customer-view', 'brand-view', 'catalog-view', 'country-view', 'city-view', 'tag-type-view', 'vendor-view', 'vendor-edit', 'pledge-view', 'product-type-view', 'permission-view']
            ],
            'Outlet' => [
                'modules' => ['Dashboard', 'Product', 'Priceline', 'Designation', 'Contact', 'Employee', 'Supplier', 'Purchase', 'OutletStock', 'Role', 'User', 'Ticket', 'Reports', 'Attachment'],
                'permissions' => ['category-view', 'uom-view', 'customer-view', 'brand-view', 'catalog-view', 'country-view', 'city-view', 'pledge-view', 'product-type-view', 'permission-view', 'tag-type-view', 'campaign-view', 'distribution-view', 'outlet-view', 'outlet-edit', 'outlet-password-create', 'outlet-password-create-mail']
            ],
        ];

        if (isset($roleSchemas[$scema])) {
            return [
                'permissionModules' => $roleSchemas[$scema]['modules'],
                'permissionNames' => $roleSchemas[$scema]['permissions']
            ];
        }

        return [
            'permissionModules' => [],
            'permissionNames' => []
        ];

    }

}