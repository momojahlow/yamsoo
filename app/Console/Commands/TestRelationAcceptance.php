<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\RelationshipRequest;
use App\Services\FamilyRelationService;
use App\Services\EventService;
use Illuminate\Console\Command;

class TestRelationAcceptance extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:relation-acceptance';

    /**
     * The console command description.
     */
    protected $description = 'Teste l\'acceptation d\'une demande de relation pour vérifier l\'erreur TypeError';

    protected FamilyRelationService $familyRelationService;
    protected EventService $eventService;

    public function __construct(FamilyRelationService $familyRelationService, EventService $eventService)
    {
        parent::__construct();
        $this->familyRelationService = $familyRelationService;
        $this->eventService = $eventService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🧪 Test d'acceptation de demande de relation");
        $this->newLine();
        
        // Trouver Mohammed et Ahmed
        $mohammed = User::find(3);
        $ahmed = User::find(1);
        
        if (!$mohammed || !$ahmed) {
            $this->error("Utilisateurs non trouvés");
            return;
        }
        
        $this->info("👤 Demandeur : {$mohammed->name}");
        $this->info("👤 Cible : {$ahmed->name}");
        
        // Vérifier s'il y a des demandes existantes
        $existingRequest = RelationshipRequest::where('requester_id', $mohammed->id)
            ->where('target_user_id', $ahmed->id)
            ->where('status', 'pending')
            ->first();
            
        if (!$existingRequest) {
            $this->info("📝 Création d'une demande de test...");
            
            try {
                $existingRequest = $this->familyRelationService->createRelationshipRequest(
                    $mohammed,
                    $ahmed->id,
                    1, // Type de relation (par exemple, frère)
                    "Test d'acceptation de demande"
                );
                
                $this->info("✅ Demande créée avec succès (ID: {$existingRequest->id})");
                
            } catch (\Exception $e) {
                $this->error("❌ Erreur lors de la création : " . $e->getMessage());
                return;
            }
        } else {
            $this->info("📋 Demande existante trouvée (ID: {$existingRequest->id})");
        }
        
        $this->newLine();
        $this->info("🔄 Test d'acceptation de la demande...");
        
        try {
            // Accepter la relation
            $this->familyRelationService->acceptRelationshipRequest($existingRequest);
            $this->info("✅ Relation acceptée avec succès !");
            
            // Déclencher l'événement (c'est ici que l'erreur se produisait)
            $this->info("📧 Test de l'envoi d'email...");
            $this->eventService->handleRelationshipAccepted($existingRequest);
            $this->info("✅ Email envoyé sans erreur !");
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de l'acceptation : " . $e->getMessage());
            $this->line("Trace : " . $e->getTraceAsString());
            return;
        }
        
        $this->newLine();
        $this->info("🎯 Test terminé avec succès !");
        $this->info("💡 L'erreur TypeError avec htmlspecialchars() a été corrigée.");
        $this->info("📧 Les emails de notification fonctionnent correctement.");
    }
}
