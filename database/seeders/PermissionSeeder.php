<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role1 = Role::create(['guard_name' => 'web', 'name' => 'admin']);
        $role3 = Role::create(['name' => 'student']);

        $user = \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
        ]);
        $user->assignRole($role1);

        $user3 = \App\Models\User::factory()->create([
            'name' => 'Student User',
            'email' => 'student@gmail.com',
            'password' => Hash::make('123456'),
        ]);
        $user3->assignRole($role3);

        $user4 = \App\Models\User::create([
            'device_id' => 'test123',
        ]);
        $user4->assignRole($role3);

        $user4 = \App\Models\User::create([
            'device_id' => 'demo123',
        ]);
        $user4->assignRole($role3);
    }
}
