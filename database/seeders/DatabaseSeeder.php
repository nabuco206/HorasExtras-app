<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;

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
        // TblFiscalia::factory(10)->create([
        //     'id'=>501,
        //     'gls_fiscalia' => 'Fiscalía de Valparaíso',
        // ]);

        $fiscalias = [
            'Fiscalia de Valparaiso',
            'Fiscalia de Viña del Mar',
            'Fiscalia de Quilpue',
            'Fiscalia de Villa Alemana',
            'Fiscalia de Limache',
            'Fiscalia de Quillota',
            'Fiscalia de La Calera',
            'Fiscalia de San Antonio',
            'Fiscalia de Casablanca',
        ];
        DB::table('tbl_fiscalias')->truncate();
        foreach ($fiscalias as $fiscalia) {
            TblFiscalia::create([
                'gls_fiscalia' => $fiscalia,
            ]);
        }
        



        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
