<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * Create role admin and user
         */
        $admin = Role::create(['name' => 'admin']);
        $user = Role::create(['name' => 'user']);

        /**
         * Default admin account
         */
        $admin = User::create([
            'name' => 'IT Mekar Armada Jaya',
            'email' => 'itmekararmadajaya@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $admin->assignRole('admin');
    }
}
