<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Cliente Juan', 'email' => 'cliente1@example.com', 'is_cliente' => true],
            ['name' => 'Cliente María', 'email' => 'cliente2@example.com', 'is_cliente' => true],
            ['name' => 'Cliente Pedro', 'email' => 'cliente3@example.com', 'is_cliente' => true],
            ['name' => 'Cajero 1', 'email' => 'cajero1@example.com', 'is_cajero' => true],
            ['name' => 'Cajero 2', 'email' => 'cajero2@example.com', 'is_cajero' => true],
            ['name' => 'Mixto 1', 'email' => 'mixto1@example.com', 'is_cliente' => true, 'is_cajero' => true],
            ['name' => 'Mixto 2', 'email' => 'mixto2@example.com', 'is_cliente' => true, 'is_cajero' => true],
            ['name' => 'Gerente Principal', 'email' => 'gerente@example.com', 'is_gerente' => true],
        ];

        foreach ($users as $data) {
            User::create(array_merge([
                'password' => Hash::make('password123'),
                'is_cliente' => false,
                'is_cajero' => false,
                'is_gerente' => false,
            ], $data));
        }
    }
}
