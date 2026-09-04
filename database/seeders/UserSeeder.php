<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ferreteria.com'],
            [
                'name' => 'Administrador',
                'role' => 'admin',
                'active' => true,
                'password' => 'password',
            ]
        );

        User::updateOrCreate(
            ['email' => 'vendedor@ferreteria.com'],
            [
                'name' => 'Vendedor',
                'role' => 'vendedor',
                'active' => true,
                'password' => 'password',
            ]
        );
    }
}
