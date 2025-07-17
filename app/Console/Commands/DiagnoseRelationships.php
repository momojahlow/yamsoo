<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\RelationshipRequest;
use App\Models\User;
use App\Models\RelationshipType;

class DiagnoseRelationships extends Command
{
    protected $signature = 'diagnose:relationships';
    protected $description = 'Diagnostique complet des relations familiales';

    public function handle()
    {
        $this->info('=== DIAGNOSTIC COMPLET ===');
        
        // 1. Vérifier la connexion DB
        try {
            DB::connection()->getPdo();
            $this->info('✓ Connexion base de données OK');
        } catch (\Exception $e) {
            $this->error('✗ Erreur connexion DB: ' . $e->getMessage());
            return;
        }
        
        // 2. Vérifier les tables
        $tables = ['users', 'relationship_types', 'relationship_requests'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $this->info("✓ Table {$table}: {$count} enregistrements");
                
                if ($table === 'relationship_requests') {
                    $columns = Schema::getColumnListing($table);
                    $this->info("  Colonnes: " . implode(', ', $columns));
                }
            } else {
                $this->error("✗ Table {$table} manquante");
            }
        }
        
        // 3. Vérifier les types de relations
        $types = RelationshipType::all();
        $this->info("Types de relations disponibles: {$types->count()}");
        foreach ($types as $type) {
            $this->info("  - ID: {$type->id}, Nom: {$type->name}");
        }
        
        // 4. Test de création directe
        $this->info('Test de création directe...');
        try {
            $users = User::take(2)->get();
            if ($users->count() >= 2 && $types->count() > 0) {
                
                // Test avec DB::table (plus bas niveau)
                $insertData = [
                    'requester_id' => $users[0]->id,
                    'target_user_id' => $users[1]->id,
                    'relationship_type_id' => $types->first()->id,
                    'message' => 'Test diagnostic',
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                $inserted = DB::table('relationship_requests')->insert($insertData);
                $this->info($inserted ? '✓ Insert DB::table réussi' : '✗ Insert DB::table échoué');
                
                if ($inserted) {
                    $lastId = DB::table('relationship_requests')->latest('id')->first()->id;
                    $this->info("  ID créé: {$lastId}");
                    
                    // Nettoyer
                    DB::table('relationship_requests')->where('id', $lastId)->delete();
                    $this->info("  Nettoyage effectué");
                }
                
            } else {
                $this->error('✗ Pas assez de données pour le test');
            }
        } catch (\Exception $e) {
            $this->error('✗ Erreur test: ' . $e->getMessage());
        }
        
        // 5. Vérifier les demandes existantes
        $existing = RelationshipRequest::count();
        $this->info("Demandes existantes: {$existing}");
        
        if ($existing > 0) {
            $recent = RelationshipRequest::latest()->take(3)->get();
            foreach ($recent as $req) {
                $this->info("  - ID: {$req->id}, Status: {$req->status}, Créé: {$req->created_at}");
            }
        }
    }
}