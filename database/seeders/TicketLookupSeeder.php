<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketLookupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed ticket types
        $types = [
            ['name' => 'Incident', 'description' => 'Problème technique ou panne'],
            ['name' => 'Demande de service', 'description' => 'Demande d\'assistance ou de support'],
            ['name' => 'Assistance', 'description' => 'Aide ou conseil'],
        ];

        foreach ($types as $type) {
            DB::table('ticket_types')->updateOrInsert(['name' => $type['name']], $type);
        }

        // Seed ticket channels
        $channels = [
            ['name' => 'Téléphone'],
            ['name' => 'Email'],
            ['name' => 'Application'],
            ['name' => 'Autre'],
        ];

        foreach ($channels as $channel) {
            DB::table('ticket_channels')->updateOrInsert($channel, $channel);
        }

        // Seed ticket priorities
        $priorities = [
            ['name' => 'Urgent', 'level' => 4, 'color' => '#DC2626'],
            ['name' => 'Haut', 'level' => 3, 'color' => '#F59E0B'],
            ['name' => 'Moyen', 'level' => 2, 'color' => '#3B82F6'],
            ['name' => 'Bas', 'level' => 1, 'color' => '#6B7280'],
        ];

        foreach ($priorities as $priority) {
            DB::table('ticket_priorities')->updateOrInsert(['name' => $priority['name']], $priority);
        }

        // Seed ticket statuses
        $statuses = [
            ['name' => 'Nouveau', 'color' => '#3B82F6', 'is_closed' => false],
            ['name' => 'En cours', 'color' => '#F59E0B', 'is_closed' => false],
            ['name' => 'En attente', 'color' => '#8B5CF6', 'is_closed' => false],
            ['name' => 'Résolu', 'color' => '#10B981', 'is_closed' => false],
            ['name' => 'Fermé', 'color' => '#6B7280', 'is_closed' => true],
        ];

        foreach ($statuses as $status) {
            DB::table('ticket_statuses')->updateOrInsert(['name' => $status['name']], $status);
        }
    }
}
