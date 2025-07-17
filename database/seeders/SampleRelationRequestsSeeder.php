<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\RelationshipRequest;
use App\Models\RelationshipType;
use Illuminate\Database\Seeder;

class SampleRelationRequestsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Ce seeder crée quelques exemples de demandes de relation en attente
     * pour tester l'interface. Vous pouvez l'exécuter optionnellement.
     */
    public function run(): void
    {
        // Récupérer les types de relations
        $brotherType = RelationshipType::where('code', 'brother')->first();
        $sisterType = RelationshipType::where('code', 'sister')->first();
        $husbandType = RelationshipType::where('code', 'husband')->first();
        $wifeType = RelationshipType::where('code', 'wife')->first();

        // Récupérer les utilisateurs
        $users = User::all();

        // Créer quelques demandes d'exemple (optionnel)
        $sampleRequests = [
            [
                'requester_email' => 'adil.benslimane@example.com',
                'target_email' => 'rachid.alaoui@example.com',
                'relationship_type' => $brotherType,
                'message' => 'Bonjour, je pense que nous pourrions être frères. Notre père nous a parlé de vous.',
            ],
            [
                'requester_email' => 'nadia.berrada@example.com',
                'target_email' => 'zineb.elkhayat@example.com',
                'relationship_type' => $sisterType,
                'message' => 'Salut ! Je crois que nous sommes sœurs. J\'ai trouvé des photos de famille où nous apparaissons ensemble.',
            ],
            [
                'requester_email' => 'mohammed.alami@example.com',
                'target_email' => 'leila.mansouri@example.com',
                'relationship_type' => $wifeType,
                'message' => 'Ma chère épouse, créons notre lien familial sur Yamsoo !',
            ],
        ];

        foreach ($sampleRequests as $requestData) {
            $requester = $users->where('email', $requestData['requester_email'])->first();
            $target = $users->where('email', $requestData['target_email'])->first();

            if ($requester && $target && $requestData['relationship_type']) {
                // Vérifier qu'il n'y a pas déjà une demande
                $existingRequest = RelationshipRequest::where(function($query) use ($requester, $target) {
                    $query->where('requester_id', $requester->id)->where('target_user_id', $target->id);
                })->orWhere(function($query) use ($requester, $target) {
                    $query->where('requester_id', $target->id)->where('target_user_id', $requester->id);
                })->exists();

                if (!$existingRequest) {
                    RelationshipRequest::create([
                        'requester_id' => $requester->id,
                        'target_user_id' => $target->id,
                        'relationship_type_id' => $requestData['relationship_type']->id,
                        'message' => $requestData['message'],
                        'status' => 'pending',
                    ]);

                    $this->command->info("Demande créée : {$requester->name} → {$target->name} ({$requestData['relationship_type']->display_name})");
                }
            }
        }

        $this->command->info('');
        $this->command->info('Demandes d\'exemple créées ! Vous pouvez maintenant :');
        $this->command->info('1. Vous connecter avec un des comptes utilisateur');
        $this->command->info('2. Aller sur la page Relations Familiales ou Réseaux');
        $this->command->info('3. Voir les demandes en attente et les accepter/refuser');
        $this->command->info('4. Créer de nouvelles demandes de relation');
    }
}
