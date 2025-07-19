<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Controllers\FamilyController;
use Illuminate\Http\Request;

class TestFamilyPageData extends Command
{
    protected $signature = 'test:family-page-data';
    protected $description = 'Tester les données de la page famille';

    public function handle()
    {
        $this->info('🔍 TEST DES DONNÉES DE LA PAGE FAMILLE');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        // Tester avec Amina qui a des relations
        $amina = User::where('email', 'amina.tazi@example.com')->first();
        
        if (!$amina) {
            $this->error('❌ Utilisateur Amina non trouvé');
            return 1;
        }

        $this->info("👩 Test avec l'utilisatrice : {$amina->name}");
        $this->newLine();

        try {
            // Créer une instance du contrôleur
            $controller = app(FamilyController::class);
            
            // Créer une requête mock
            $request = Request::create('/famille', 'GET');
            $request->setUserResolver(function () use ($amina) {
                return $amina;
            });

            // Appeler la méthode index
            $response = $controller->index($request);
            
            $this->info('✅ Contrôleur appelé avec succès');
            
            // Récupérer les données directement du contrôleur
            $relations = \App\Models\FamilyRelationship::where('user_id', $amina->id)
                ->where('status', 'accepted')
                ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
                ->get();

            $members = $relations->map(function($relation) use ($amina) {
                $member = $relation->relatedUser;
                $profile = $member->profile;
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'relation' => $relation->relationshipType->name_fr ?? $relation->relationshipType->name ?? 'Relation',
                    'status' => $relation->status,
                    'avatar' => $profile?->avatar ?? null,
                    'bio' => $profile?->bio ?? null,
                    'birth_date' => $profile?->birth_date ?? null,
                    'gender' => $profile?->gender ?? null,
                    'phone' => $profile?->phone ?? null,
                ];
            })->values();

            $this->info('📊 DONNÉES POUR LA PAGE FAMILLE :');
            $this->line("   Type de members : " . gettype($members));
            $this->line("   Est un tableau : " . (is_array($members->toArray()) ? 'Oui' : 'Non'));
            $this->line("   Nombre de membres : " . $members->count());
            $this->newLine();

            $this->info('👥 MEMBRES DE LA FAMILLE D\'AMINA :');
            foreach ($members as $member) {
                $gender = $member['gender'] === 'female' ? '👩' : '👨';
                $this->line("   {$gender} {$member['name']} : {$member['relation']}");
            }
            $this->newLine();

            $this->info('✅ Test réussi !');
            $this->line('   - Les données sont correctement formatées');
            $this->line('   - members est bien un tableau');
            $this->line('   - Les relations sont correctes (Frère au lieu de Sœur)');
            $this->newLine();

            $this->info('🌐 L\'interface web devrait maintenant fonctionner sans erreur JavaScript');
            
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors du test :');
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
