<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Branding
            [
                'key' => 'app_name',
                'value' => 'HELPTICKET',
                'type' => 'string',
                'group' => 'branding',
                'label' => 'Nom de l\'application',
                'description' => 'Le nom affiché dans l\'interface',
            ],
            [
                'key' => 'app_tagline',
                'value' => 'Un ticket, un déclic, HELPTICKET c\'est magique',
                'type' => 'string',
                'group' => 'branding',
                'label' => 'Slogan',
                'description' => 'Le slogan de l\'application',
            ],
            [
                'key' => 'app_logo',
                'value' => 'logos/helpticket-logo.png',
                'type' => 'file',
                'group' => 'branding',
                'label' => 'Logo',
                'description' => 'Le logo de l\'application',
            ],

            // General
            [
                'key' => 'tickets_per_page',
                'value' => '15',
                'type' => 'integer',
                'group' => 'general',
                'label' => 'Tickets par page',
                'description' => 'Nombre de tickets affichés par page',
            ],
            [
                'key' => 'enable_duplicate_detection',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'general',
                'label' => 'Activer la détection de doublons',
                'description' => 'Détecter automatiquement les tickets similaires',
            ],
            [
                'key' => 'duplicate_threshold',
                'value' => '70',
                'type' => 'integer',
                'group' => 'general',
                'label' => 'Seuil de similarité (%)',
                'description' => 'Pourcentage de similarité pour détecter les doublons',
            ],

            // Notifications
            [
                'key' => 'enable_email_notifications',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'label' => 'Activer les notifications email',
                'description' => 'Envoyer des notifications par email',
            ],
            [
                'key' => 'enable_database_notifications',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'label' => 'Activer les notifications dans l\'application',
                'description' => 'Afficher les notifications dans l\'interface',
            ],

            // Email
            [
                'key' => 'email_from_name',
                'value' => 'HELPTICKET',
                'type' => 'string',
                'group' => 'email',
                'label' => 'Nom de l\'expéditeur',
                'description' => 'Le nom affiché comme expéditeur des emails',
            ],
            [
                'key' => 'email_from_address',
                'value' => 'noreply@helpticket.com',
                'type' => 'string',
                'group' => 'email',
                'label' => 'Adresse email de l\'expéditeur',
                'description' => 'L\'adresse email utilisée pour envoyer les notifications',
            ],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
