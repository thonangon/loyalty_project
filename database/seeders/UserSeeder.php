<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $authorRole = Role::firstOrCreate(['name' => 'author']);

        $admin = User::firstOrCreate([
            'email' => 'superadmin@example.com',
        ], [
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($adminRole);

        $author = User::firstOrCreate([
            'email' => 'author@example.com',
        ], [
            'first_name' => 'test',
            'last_name' => 'User',
            'password' => bcrypt('password'),
        ]);
        $author->assignRole($authorRole);

        
    }
    
}
