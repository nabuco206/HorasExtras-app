<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\TblFiscalia;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        TblFiscalia::factory(10)->create([
            'id'=>501,
            'gls_fiscalia' => 'Fiscalía de Valparaíso',
        ]);
        



        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
