<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanupTempFiles extends Command
{
    protected $signature = 'cleanup:temp {--dry-run : Simuler sans déplacer les fichiers}';
    protected $description = 'Déplace tous les fichiers temporaires vers storage/dev_temp';

    public function handle(): void
    {
        $rootPath = base_path(); // Racine du projet
        $targetPath = storage_path('dev_temp');
        $dryRun = $this->option('dry-run');

        // Créer le dossier s'il n'existe pas
        if (!File::exists($targetPath) && !$dryRun) {
            File::makeDirectory($targetPath, 0755, true);
        }

        // Patterns de fichiers temporaires à déplacer
        $patterns = [
            $rootPath . '/check_*',
            $rootPath . '/test_*',
            $rootPath . '/fix_*',
            $rootPath . '/validation_*',
            $rootPath . '/simulation_*',
            $rootPath . '/update_*',
            $rootPath . '/final_*',
            $rootPath . '/debug_*',
            $rootPath . '/CORRECTION_*',
            $rootPath . '/analyze_*',
            $rootPath . '/*.md', // rapport temporaires markdown
        ];

        $moved = 0;

        foreach ($patterns as $pattern) {
            foreach (glob($pattern) as $file) {
                if (is_file($file)) {
                    $filename = basename($file);

                    if ($dryRun) {
                        $this->line("[SIMULATION] " . $filename . " serait déplacé vers storage/dev_temp/");
                    } else {
                        File::move($file, $targetPath . '/' . $filename);
                        $this->info("Déplacé : " . $filename);
                    }

                    $moved++;
                }
            }
        }

        if ($moved === 0) {
            $this->warn('Aucun fichier temporaire trouvé.');
        } else {
            $this->info($dryRun
                ? "$moved fichiers seraient déplacés (simulation)."
                : "$moved fichiers déplacés vers storage/dev_temp."
            );
        }
    }
}
