<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Gratis',
                'slug' => 'gratis',
                'company_limit' => 1,
                'price' => 0,
                'description' => 'Probar el sistema',
            ],
            [
                'name' => 'Básico',
                'slug' => 'basico',
                'company_limit' => 20,
                'price' => 9900,
                'description' => 'Contadores independientes',
            ],
            [
                'name' => 'Profesional',
                'slug' => 'profesional',
                'company_limit' => 50,
                'price' => 15000,
                'description' => 'Carteras medianas',
            ],
            [
                'name' => 'Avanzado',
                'slug' => 'avanzado',
                'company_limit' => 100,
                'price' => 30000,
                'description' => 'Estudios contables',
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
