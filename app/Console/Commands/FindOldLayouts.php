<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FindOldLayouts extends Command
{
    protected $signature = 'find:old-layouts';
    protected $description = 'Trouver tous les composants React qui utilisent encore AppLayout ou AuthenticatedLayout au lieu de AppSidebarLayout';

    public function handle()
    {
        $this->info('🔍 RECHERCHE DES ANCIENS LAYOUTS');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        $pagesDir = resource_path('js/Pages');
        $componentsDir = resource_path('js/components');
        
        $oldLayoutUsages = [];
        
        // Rechercher dans les pages
        $this->info('1️⃣ RECHERCHE DANS LES PAGES :');
        $this->searchInDirectory($pagesDir, $oldLayoutUsages);
        
        // Rechercher dans les composants
        $this->info('2️⃣ RECHERCHE DANS LES COMPOSANTS :');
        $this->searchInDirectory($componentsDir, $oldLayoutUsages);
        
        $this->newLine();
        
        if (empty($oldLayoutUsages)) {
            $this->info('✅ AUCUN ANCIEN LAYOUT TROUVÉ !');
            $this->line('   Tous les composants utilisent AppSidebarLayout ou n\'utilisent pas de layout.');
        } else {
            $this->warn('⚠️  ANCIENS LAYOUTS TROUVÉS :');
            $this->line('   Les fichiers suivants utilisent encore AppLayout ou AuthenticatedLayout :');
            $this->newLine();
            
            foreach ($oldLayoutUsages as $file => $layouts) {
                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);
                $this->line("   📄 {$relativePath}");
                foreach ($layouts as $layout) {
                    $this->line("      • {$layout['type']} (ligne {$layout['line']})");
                }
                $this->newLine();
            }
            
            $this->info('💡 RECOMMANDATIONS :');
            $this->line('   Remplacez ces imports par :');
            $this->line('   import AppSidebarLayout from \'@/Layouts/app/app-sidebar-layout\';');
            $this->newLine();
            $this->line('   Et utilisez <AppSidebarLayout> au lieu de <AppLayout> ou <AuthenticatedLayout>');
        }
        
        $this->newLine();
        $this->info('🎯 RECHERCHE TERMINÉE !');

        return 0;
    }
    
    private function searchInDirectory(string $directory, array &$oldLayoutUsages): void
    {
        if (!File::exists($directory)) {
            $this->line("   ⚠️  Répertoire non trouvé : {$directory}");
            return;
        }
        
        $files = File::allFiles($directory);
        $foundFiles = 0;
        
        foreach ($files as $file) {
            if (!in_array($file->getExtension(), ['tsx', 'ts', 'jsx', 'js'])) {
                continue;
            }
            
            $content = File::get($file->getPathname());
            $lines = explode("\n", $content);
            $fileLayouts = [];
            
            foreach ($lines as $lineNumber => $line) {
                $lineNumber++; // Les numéros de ligne commencent à 1
                
                // Rechercher les imports d'anciens layouts
                if (preg_match('/import.*AppLayout.*from/', $line)) {
                    $fileLayouts[] = [
                        'type' => 'Import AppLayout',
                        'line' => $lineNumber,
                        'content' => trim($line)
                    ];
                }
                
                if (preg_match('/import.*AuthenticatedLayout.*from/', $line)) {
                    $fileLayouts[] = [
                        'type' => 'Import AuthenticatedLayout',
                        'line' => $lineNumber,
                        'content' => trim($line)
                    ];
                }
                
                // Rechercher l'utilisation des anciens layouts
                if (preg_match('/<AppLayout[>\s]/', $line)) {
                    $fileLayouts[] = [
                        'type' => 'Utilisation <AppLayout>',
                        'line' => $lineNumber,
                        'content' => trim($line)
                    ];
                }
                
                if (preg_match('/<AuthenticatedLayout[>\s]/', $line)) {
                    $fileLayouts[] = [
                        'type' => 'Utilisation <AuthenticatedLayout>',
                        'line' => $lineNumber,
                        'content' => trim($line)
                    ];
                }
                
                if (preg_match('/<\/AppLayout>/', $line)) {
                    $fileLayouts[] = [
                        'type' => 'Fermeture </AppLayout>',
                        'line' => $lineNumber,
                        'content' => trim($line)
                    ];
                }
                
                if (preg_match('/<\/AuthenticatedLayout>/', $line)) {
                    $fileLayouts[] = [
                        'type' => 'Fermeture </AuthenticatedLayout>',
                        'line' => $lineNumber,
                        'content' => trim($line)
                    ];
                }
            }
            
            if (!empty($fileLayouts)) {
                $oldLayoutUsages[$file->getPathname()] = $fileLayouts;
                $foundFiles++;
            }
        }
        
        $this->line("   📊 {$foundFiles} fichier(s) avec anciens layouts trouvé(s)");
    }
}
