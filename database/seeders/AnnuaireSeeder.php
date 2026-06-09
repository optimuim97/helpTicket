<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnnuaireSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/annuaire.json');
        if (! is_file($path)) {
            $this->command?->warn("Fichier annuaire introuvable: {$path}");

            return;
        }

        $rows = json_decode(file_get_contents($path), true) ?? [];
        $this->command?->info('Import annuaire: '.count($rows).' lignes.');

        $positionsCache = [];

        DB::transaction(function () use ($rows, &$positionsCache) {
            foreach ($rows as $row) {
                $name = $row['name'] ?? null;
                if (! $name) {
                    continue;
                }

                $positionId = null;
                $fonction = $this->normalizeFonction($row['fonction'] ?? null);
                if ($fonction) {
                    if (! isset($positionsCache[$fonction])) {
                        $positionsCache[$fonction] = Position::firstOrCreate(
                            ['fonction' => $fonction, 'metier' => null],
                        )->id;
                    }
                    $positionId = $positionsCache[$fonction];
                }

                $email = $this->cleanEmail($row['email'] ?? null);
                $matricule = $row['matricule'] ?? null;

                $attributes = [
                    'name' => $this->cleanName($name),
                    'email' => $email,
                    'numero_fixe' => $row['numero_fixe'] ?? null,
                    'numero_flotte' => $row['numero_flotte'] ?? null,
                    'position_id' => $positionId,
                ];

                if ($matricule) {
                    if (! User::where('email', $email)->first()) {
                        User::updateOrCreate(['matricule' => $matricule], $attributes);
                    }

                } elseif ($email) {
                    if (! User::where('email', $email)->first()) {
                        User::updateOrCreate(['email' => $email], $attributes);
                    }
                } else {
                    User::create($attributes);
                }
            }
        });

        $this->command?->info('Annuaire importé: '.User::count().' users, '.Position::count().' positions.');
    }

    private function cleanName(string $name): string
    {
        return Str::of($name)->squish()->title()->toString();
    }

    private function cleanEmail(?string $email): ?string
    {
        if (! $email) {
            return null;
        }
        $email = strtolower(trim($email));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function normalizeFonction(?string $fonction): ?string
    {
        if (! $fonction) {
            return null;
        }
        $clean = preg_replace('/\s+/', ' ', trim($fonction));
        $clean = mb_convert_case($clean, MB_CASE_TITLE, 'UTF-8');

        return $clean !== '' ? $clean : null;
    }
}
