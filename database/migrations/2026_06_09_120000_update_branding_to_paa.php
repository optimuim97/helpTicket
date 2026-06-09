<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Force le rebranding PAA même si le seeder a déjà tourné.
        DB::table('app_settings')->where('key', 'app_name')->update([
            'value' => "Port Autonome d'Abidjan",
        ]);

        DB::table('app_settings')->where('key', 'app_tagline')->update([
            'value' => 'Plateforme support & intervention — DSI',
        ]);

        // On vide le chemin du logo BDD pour utiliser le fichier statique
        // /logo/logo_port.jpg embarqué dans `public/`.
        DB::table('app_settings')->where('key', 'app_logo')->update([
            'value' => null,
        ]);
    }

    public function down(): void
    {
        // Pas de rollback : c'est un changement de branding définitif.
    }
};
