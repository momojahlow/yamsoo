<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckMissingIcons extends Command
{
    protected $signature = 'check:missing-icons';
    protected $description = 'Vérifier les icônes Lucide manquantes dans les composants React';

    public function handle()
    {
        $this->info('🔍 VÉRIFICATION DES ICÔNES MANQUANTES');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        $pagesDir = resource_path('js/Pages');
        $componentsDir = resource_path('js/components');
        
        $missingIcons = [];
        
        // Rechercher dans les pages
        $this->info('1️⃣ VÉRIFICATION DES PAGES :');
        $this->checkDirectory($pagesDir, $missingIcons);
        
        // Rechercher dans les composants
        $this->info('2️⃣ VÉRIFICATION DES COMPOSANTS :');
        $this->checkDirectory($componentsDir, $missingIcons);
        
        $this->newLine();
        
        if (empty($missingIcons)) {
            $this->info('✅ AUCUNE ICÔNE MANQUANTE TROUVÉE !');
            $this->line('   Toutes les icônes Lucide sont correctement importées.');
        } else {
            $this->warn('⚠️  ICÔNES MANQUANTES TROUVÉES :');
            $this->newLine();
            
            foreach ($missingIcons as $file => $icons) {
                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);
                $this->line("   📄 {$relativePath}");
                foreach ($icons as $icon) {
                    $this->line("      • {$icon['icon']} (ligne {$icon['line']})");
                }
                $this->newLine();
            }
            
            $this->info('💡 SOLUTION :');
            $this->line('   Ajoutez les icônes manquantes aux imports lucide-react :');
            $this->line('   import { ExistingIcons, MissingIcon } from \'lucide-react\';');
        }
        
        $this->newLine();
        $this->info('🎯 VÉRIFICATION TERMINÉE !');

        return 0;
    }
    
    private function checkDirectory(string $directory, array &$missingIcons): void
    {
        if (!File::exists($directory)) {
            $this->line("   ⚠️  Répertoire non trouvé : {$directory}");
            return;
        }
        
        $files = File::allFiles($directory);
        $checkedFiles = 0;
        $issuesFound = 0;
        
        foreach ($files as $file) {
            if (!in_array($file->getExtension(), ['tsx', 'ts', 'jsx', 'js'])) {
                continue;
            }
            
            $content = File::get($file->getPathname());
            $lines = explode("\n", $content);
            $fileIcons = [];
            
            // Extraire les imports lucide-react
            $importedIcons = [];
            foreach ($lines as $lineNumber => $line) {
                if (preg_match('/import\s*{([^}]+)}\s*from\s*[\'"]lucide-react[\'"]/', $line, $matches)) {
                    $icons = array_map('trim', explode(',', $matches[1]));
                    $importedIcons = array_merge($importedIcons, $icons);
                }
            }
            
            // Chercher l'utilisation d'icônes
            foreach ($lines as $lineNumber => $line) {
                $lineNumber++; // Les numéros de ligne commencent à 1
                
                // Rechercher les patterns d'utilisation d'icônes : <IconName ou IconName className
                if (preg_match_all('/<([A-Z][a-zA-Z]*)\s|([A-Z][a-zA-Z]*)\s+className=/', $line, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $iconName = $match[1] ?: $match[2];
                        
                        // Ignorer les composants React communs
                        $commonComponents = [
                            'Head', 'Link', 'Card', 'CardContent', 'CardHeader', 'CardTitle', 
                            'Button', 'Input', 'Badge', 'Avatar', 'AvatarFallback', 'AvatarImage',
                            'Select', 'SelectContent', 'SelectItem', 'SelectTrigger', 'SelectValue',
                            'ScrollArea', 'Textarea', 'Label', 'Checkbox', 'RadioGroup', 'RadioGroupItem',
                            'Dialog', 'DialogContent', 'DialogHeader', 'DialogTitle', 'DialogTrigger',
                            'DropdownMenu', 'DropdownMenuContent', 'DropdownMenuItem', 'DropdownMenuTrigger',
                            'Popover', 'PopoverContent', 'PopoverTrigger', 'Tooltip', 'TooltipContent',
                            'TooltipProvider', 'TooltipTrigger', 'Sheet', 'SheetContent', 'SheetHeader',
                            'SheetTitle', 'SheetTrigger', 'Tabs', 'TabsContent', 'TabsList', 'TabsTrigger',
                            'AppSidebarLayout', 'AppLayout', 'AuthenticatedLayout', 'ConversationList',
                            'ChatArea', 'UserSearch', 'MessageSearch', 'MessageSettings', 'MessageStats',
                            'FamilySuggestions', 'TreeNode', 'TreeConnections', 'FamilyMemberCard',
                            'NotificationsBadge', 'EmptySuggestions', 'SuggestionActions'
                        ];
                        
                        if (in_array($iconName, $commonComponents)) {
                            continue;
                        }
                        
                        // Vérifier si l'icône est importée
                        if (!in_array($iconName, $importedIcons)) {
                            $fileIcons[] = [
                                'icon' => $iconName,
                                'line' => $lineNumber
                            ];
                        }
                    }
                }
            }
            
            if (!empty($fileIcons)) {
                $missingIcons[$file->getPathname()] = $fileIcons;
                $issuesFound++;
            }
            
            $checkedFiles++;
        }
        
        $this->line("   📊 {$checkedFiles} fichier(s) vérifiés, {$issuesFound} avec des icônes manquantes");
    }
}
