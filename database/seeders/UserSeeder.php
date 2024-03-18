<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate([
            'email' => 'admin@pathexaminations.com',
        ], [
            'name' => 'Path Examinations',
            'password' => bcrypt('password'),
        ]);

        Role::firstOrCreate(['name' => 'admin']);

        $user->assignRole('admin');
    }
}
