<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Support Technique',
                'description' => 'Service responsable du support technique et de l\'assistance aux utilisateurs',
                'is_active' => true,
            ],
            [
                'name' => 'Service Client',
                'description' => 'Service gérant les relations clients et les demandes commerciales',
                'is_active' => true,
            ],
            [
                'name' => 'Infrastructure IT',
                'description' => 'Service en charge de l\'infrastructure réseau et serveurs',
                'is_active' => true,
            ],
            [
                'name' => 'Développement',
                'description' => 'Équipe de développement logiciel et maintenance applicative',
                'is_active' => true,
            ],
            [
                'name' => 'Administration',
                'description' => 'Service administratif et gestion générale',
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                $service
            );
        }
    }
}
