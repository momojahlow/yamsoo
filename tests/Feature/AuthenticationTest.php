<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de connexion réussie avec des identifiants valides
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@yamsoo.test',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@yamsoo.test',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test de connexion échouée avec des identifiants invalides
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@yamsoo.test',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@yamsoo.test',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Test de connexion avec email inexistant
     */
    public function test_user_cannot_login_with_nonexistent_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@yamsoo.test',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Test de déconnexion
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);
        
        $response = $this->post('/logout');
        
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * Test de validation des champs requis
     */
    public function test_login_requires_email_and_password(): void
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    /**
     * Test de validation du format email
     */
    public function test_login_requires_valid_email_format(): void
    {
        $response = $this->post('/login', [
            'email' => 'invalid-email',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test de redirection après connexion réussie
     */
    public function test_authenticated_user_redirected_to_dashboard(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/login');
        
        $response->assertRedirect('/dashboard');
    }

    /**
     * Test d'accès aux pages protégées sans authentification
     */
    public function test_guest_cannot_access_protected_pages(): void
    {
        $protectedRoutes = [
            '/dashboard',
            '/famille',
            '/messages',
            '/settings',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    /**
     * Test de la fonction "Se souvenir de moi"
     */
    public function test_remember_me_functionality(): void
    {
        $user = User::factory()->create([
            'email' => 'test@yamsoo.test',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@yamsoo.test',
            'password' => 'password',
            'remember' => true,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        
        // Vérifier que le cookie "remember" est défini
        $this->assertNotNull($response->getCookie('remember_web_' . sha1(config('app.key'))));
    }

    /**
     * Test de limitation des tentatives de connexion (rate limiting)
     */
    public function test_login_rate_limiting(): void
    {
        $user = User::factory()->create([
            'email' => 'test@yamsoo.test',
            'password' => Hash::make('password'),
        ]);

        // Faire plusieurs tentatives de connexion échouées
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'email' => 'test@yamsoo.test',
                'password' => 'wrong-password',
            ]);
        }

        // La prochaine tentative devrait être bloquée
        $response = $this->post('/login', [
            'email' => 'test@yamsoo.test',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertStringContainsString('Too many login attempts', $response->getSession()->get('errors')->first('email'));
    }
}
