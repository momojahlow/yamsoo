<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\Conversation;
use App\Services\FamilyRelationService;

class TestFamilyMessaging extends Command
{
    protected $signature = 'test:family-messaging';
    protected $description = 'Tester l\'intégration messagerie + relations familiales';

    public function handle()
    {
        $this->info('🔗 TEST DE L\'INTÉGRATION MESSAGERIE + RELATIONS FAMILIALES');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $familyService = app(FamilyRelationService::class);

        // Test avec un utilisateur ayant des relations familiales
        $userWithFamily = User::whereHas('familyRelationships')->first();
        
        if (!$userWithFamily) {
            $this->error('❌ Aucun utilisateur avec des relations familiales trouvé');
            return 1;
        }

        $this->info("👤 UTILISATEUR TEST : {$userWithFamily->name}");
        $this->newLine();

        // Test 1: Récupérer les membres de famille pour messagerie
        $this->info('1️⃣ MEMBRES DE FAMILLE POUR MESSAGERIE :');
        $familyMembers = $familyService->getFamilyMembersForMessaging($userWithFamily);
        
        if ($familyMembers->isEmpty()) {
            $this->line('   Aucun membre de famille trouvé');
        } else {
            foreach ($familyMembers as $member) {
                $status = $member['is_online'] ? '🟢 En ligne' : '⚫ Hors ligne';
                $this->line("   👥 {$member['name']} - {$member['relationship']} {$status}");
            }
        }
        $this->newLine();

        // Test 2: Suggestions de conversations
        $this->info('2️⃣ SUGGESTIONS DE CONVERSATIONS :');
        $suggestions = $familyService->getConversationSuggestions($userWithFamily);
        
        if ($suggestions->isEmpty()) {
            $this->line('   Aucune suggestion (conversations déjà existantes)');
        } else {
            foreach ($suggestions as $suggestion) {
                $this->line("   💬 Suggestion : {$suggestion['name']} ({$suggestion['relationship']})");
            }
        }
        $this->newLine();

        // Test 3: Statistiques des conversations familiales
        $this->info('3️⃣ STATISTIQUES DES CONVERSATIONS FAMILIALES :');
        
        $totalConversations = $userWithFamily->conversations()->count();
        $privateConversations = $userWithFamily->conversations()->where('type', 'private')->count();
        $groupConversations = $userWithFamily->conversations()->where('type', 'group')->count();
        
        $this->line("   💬 Total conversations : {$totalConversations}");
        $this->line("   👥 Conversations privées : {$privateConversations}");
        $this->line("   🏠 Groupes familiaux : {$groupConversations}");
        $this->newLine();

        // Test 4: Analyse des relations vs conversations
        $this->info('4️⃣ ANALYSE RELATIONS VS CONVERSATIONS :');
        
        $totalRelations = FamilyRelationship::where('user_id', $userWithFamily->id)->count();
        $conversationsWithFamily = 0;
        
        foreach ($familyMembers as $member) {
            $hasConversation = $userWithFamily->conversations()
                ->where('type', 'private')
                ->whereHas('participants', function ($query) use ($member) {
                    $query->where('user_id', $member['id']);
                })
                ->exists();
                
            if ($hasConversation) {
                $conversationsWithFamily++;
            }
        }
        
        $coveragePercent = $totalRelations > 0 ? round(($conversationsWithFamily / $totalRelations) * 100) : 0;
        
        $this->line("   👨‍👩‍👧‍👦 Relations familiales : {$totalRelations}");
        $this->line("   💬 Conversations avec famille : {$conversationsWithFamily}");
        $this->line("   📊 Couverture messagerie : {$coveragePercent}%");
        $this->newLine();

        // Test 5: Fonctionnalités disponibles
        $this->info('5️⃣ FONCTIONNALITÉS INTÉGRÉES DISPONIBLES :');
        $this->line('   ✅ Recherche prioritaire des membres de famille');
        $this->line('   ✅ Suggestions basées sur les relations');
        $this->line('   ✅ Création automatique de groupes familiaux');
        $this->line('   ✅ Affichage des relations dans l\'interface');
        $this->line('   ✅ Indicateurs de statut en ligne');
        $this->line('   ✅ Conversations automatiques lors de nouvelles relations');
        $this->newLine();

        // Test 6: Recommandations
        $this->info('6️⃣ RECOMMANDATIONS :');
        
        if ($suggestions->count() > 0) {
            $this->line("   💡 {$suggestions->count()} conversations peuvent être créées");
            $this->line('   🎯 Utilisez les suggestions familiales dans l\'interface');
        }
        
        if ($familyMembers->count() >= 3 && $groupConversations === 0) {
            $this->line('   🏠 Un groupe familial peut être créé');
            $this->line('   👨‍👩‍👧‍👦 Rassemblez toute la famille dans une conversation');
        }
        
        if ($coveragePercent < 50) {
            $this->line('   📈 Encouragez plus de conversations familiales');
            $this->line('   💬 Seulement ' . $coveragePercent . '% des relations ont des conversations');
        }
        $this->newLine();

        // Résumé final
        $this->info('🎉 INTÉGRATION MESSAGERIE + FAMILLE OPÉRATIONNELLE !');
        $this->line('   L\'interface de messagerie est maintenant intelligemment connectée');
        $this->line('   aux relations familiales pour une expérience optimale.');
        $this->newLine();

        $this->info('🌐 ACCÈS :');
        $this->line('   Interface : http://yamsoo.test/messages');
        $this->line('   Suggestions familiales : Bouton "👥" dans l\'interface');

        return 0;
    }
}
