<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageRedirectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;
    protected User $user3;
    protected Conversation $privateConversation;
    protected Conversation $groupConversation;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer des utilisateurs de test
        $this->user1 = User::factory()->create(['name' => 'Alice']);
        $this->user2 = User::factory()->create(['name' => 'Bob']);
        $this->user3 = User::factory()->create(['name' => 'Charlie']);

        // Créer une conversation privée
        $this->privateConversation = Conversation::create([
            'type' => 'private',
            'created_by' => $this->user1->id,
            'last_message_at' => now(),
        ]);

        $this->privateConversation->participants()->attach([
            $this->user1->id => ['joined_at' => now(), 'role' => 'member', 'status' => 'active'],
            $this->user2->id => ['joined_at' => now(), 'role' => 'member', 'status' => 'active'],
        ]);

        // Créer une conversation de groupe
        $this->groupConversation = Conversation::create([
            'name' => 'Groupe Test',
            'type' => 'group',
            'created_by' => $this->user1->id,
            'last_message_at' => now(),
        ]);

        $this->groupConversation->participants()->attach([
            $this->user1->id => ['joined_at' => now(), 'role' => 'owner', 'status' => 'active'],
            $this->user2->id => ['joined_at' => now(), 'role' => 'member', 'status' => 'active'],
            $this->user3->id => ['joined_at' => now(), 'role' => 'member', 'status' => 'active'],
        ]);
    }

    /** @test */
    public function message_sent_in_private_conversation_redirects_to_private_conversation()
    {
        $this->actingAs($this->user1);

        $response = $this->post('/messagerie/send', [
            'conversation_id' => $this->privateConversation->id,
            'message' => 'Hello Bob!',
        ]);

        // Vérifier que le message a été créé
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $this->privateConversation->id,
            'user_id' => $this->user1->id,
            'content' => 'Hello Bob!',
        ]);

        // Vérifier la redirection vers la conversation privée
        $response->assertRedirect("/messagerie?selectedContactId={$this->user2->id}");
    }

    /** @test */
    public function message_sent_in_group_conversation_redirects_to_group_conversation()
    {
        $this->actingAs($this->user1);

        $response = $this->post('/messagerie/send', [
            'conversation_id' => $this->groupConversation->id,
            'message' => 'Hello everyone!',
        ]);

        // Vérifier que le message a été créé
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $this->groupConversation->id,
            'user_id' => $this->user1->id,
            'content' => 'Hello everyone!',
        ]);

        // Vérifier la redirection vers la conversation de groupe
        $response->assertRedirect("/messagerie?selectedGroupId={$this->groupConversation->id}");
    }

    /** @test */
    public function ajax_request_returns_json_response_instead_of_redirect()
    {
        $this->actingAs($this->user1);

        $response = $this->postJson('/messagerie/send', [
            'conversation_id' => $this->groupConversation->id,
            'message' => 'AJAX message',
        ]);

        // Vérifier que le message a été créé
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $this->groupConversation->id,
            'user_id' => $this->user1->id,
            'content' => 'AJAX message',
        ]);

        // Vérifier la réponse JSON
        $response->assertJson([
            'success' => true,
            'conversation_type' => 'group',
            'conversation_id' => $this->groupConversation->id,
        ]);

        $response->assertJsonStructure([
            'success',
            'message' => [
                'id',
                'content',
                'user_id',
                'created_at',
            ],
            'conversation_type',
            'conversation_id',
        ]);
    }

    /** @test */
    public function inertia_request_returns_json_response_instead_of_redirect()
    {
        $this->actingAs($this->user1);

        $response = $this->post('/messagerie/send', [
            'conversation_id' => $this->privateConversation->id,
            'message' => 'Inertia message',
        ], [
            'X-Inertia' => 'true',
            'Accept' => 'application/json',
        ]);

        // Vérifier que le message a été créé
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $this->privateConversation->id,
            'user_id' => $this->user1->id,
            'content' => 'Inertia message',
        ]);

        // Vérifier la réponse JSON
        $response->assertJson([
            'success' => true,
            'conversation_type' => 'private',
            'conversation_id' => $this->privateConversation->id,
        ]);
    }

    /** @test */
    public function user_cannot_send_message_to_conversation_they_are_not_part_of()
    {
        // Créer un utilisateur qui ne fait pas partie des conversations
        $outsideUser = User::factory()->create(['name' => 'Outsider']);
        $this->actingAs($outsideUser);

        $response = $this->post('/messagerie/send', [
            'conversation_id' => $this->privateConversation->id,
            'message' => 'Unauthorized message',
        ]);

        // Vérifier que le message n'a pas été créé
        $this->assertDatabaseMissing('messages', [
            'conversation_id' => $this->privateConversation->id,
            'user_id' => $outsideUser->id,
            'content' => 'Unauthorized message',
        ]);

        // Vérifier la redirection avec erreur
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Accès non autorisé');
    }

    /** @test */
    public function message_updates_conversation_last_message_timestamp()
    {
        $this->actingAs($this->user1);

        $originalTimestamp = $this->groupConversation->last_message_at;

        // Attendre un peu pour s'assurer que le timestamp change
        sleep(1);

        $this->post('/messagerie/send', [
            'conversation_id' => $this->groupConversation->id,
            'message' => 'Timestamp test',
        ]);

        // Recharger la conversation
        $this->groupConversation->refresh();

        // Vérifier que le timestamp a été mis à jour
        $this->assertNotEquals($originalTimestamp, $this->groupConversation->last_message_at);
        $this->assertTrue($this->groupConversation->last_message_at > $originalTimestamp);
    }
}
