# Services Yamsoo - Documentation

Ce document décrit les services ajoutés à l'application Yamsoo pour améliorer la gestion des connexions familiales.

## Services Ajoutés

### 1. NetworkService
Gère les connexions réseau entre utilisateurs.

**Fonctionnalités :**
- Créer des connexions entre utilisateurs
- Accepter/rejeter des demandes de connexion
- Rechercher des utilisateurs
- Gérer le réseau d'un utilisateur

**Méthodes principales :**
- `getUserNetwork(User $user)` - Récupère le réseau d'un utilisateur
- `createConnection(User $user, int $connectedUserId)` - Crée une connexion
- `acceptConnection(Network $network)` - Accepte une connexion
- `searchUsers(User $user, string $query)` - Recherche des utilisateurs

### 2. SuggestionService
Gère les suggestions de connexions familiales intelligentes.

**Fonctionnalités :**
- Générer des suggestions basées sur différents critères
- Gérer le cycle de vie des suggestions
- Analyser les patterns de connexions

**Méthodes principales :**
- `getUserSuggestions(User $user)` - Récupère les suggestions d'un utilisateur
- `generateSuggestions(User $user)` - Génère de nouvelles suggestions
- `acceptSuggestion(Suggestion $suggestion)` - Accepte une suggestion

### 3. FamilyRelationService
Gère les relations familiales complexes avec types de relations.

**Fonctionnalités :**
- Gérer les demandes de relations familiales
- Créer des arbres généalogiques
- Gérer les types de relations (père, mère, frère, etc.)
- Statistiques familiales

**Méthodes principales :**
- `createRelationshipRequest(User $requester, int $targetUserId, int $relationshipTypeId)` - Crée une demande
- `acceptRelationshipRequest(RelationshipRequest $request)` - Accepte une relation
- `getFamilyTree(User $user)` - Génère l'arbre généalogique
- `getFamilyStatistics(User $user)` - Statistiques familiales

### 4. EmailService
Gère l'envoi d'emails pour les notifications et communications.

**Fonctionnalités :**
- Emails de bienvenue
- Notifications de relations
- Rappels et invitations
- Emails de réinitialisation de mot de passe

**Méthodes principales :**
- `sendWelcomeEmail(User $user)` - Email de bienvenue
- `sendRelationshipRequestEmail(User $requester, User $target, string $relationshipType)` - Notification de demande
- `sendFamilyInvitationEmail(User $inviter, string $inviteeEmail, string $familyName)` - Invitation familiale

### 5. FileUploadService
Gère l'upload et le stockage des fichiers.

**Fonctionnalités :**
- Upload de photos de profil
- Gestion des pièces jointes de messages
- Validation des types de fichiers
- Génération de miniatures

**Méthodes principales :**
- `uploadProfilePicture(UploadedFile $file, int $userId)` - Upload photo de profil
- `validateImage(UploadedFile $file)` - Validation d'image
- `deleteFile(string $filePath)` - Suppression de fichier

### 6. SearchService
Gère la recherche d'utilisateurs et de contenu.

**Fonctionnalités :**
- Recherche d'utilisateurs par nom/email
- Recherche par localisation
- Recherche par âge
- Recherche avancée avec critères multiples

**Méthodes principales :**
- `searchUsers(string $query, User $currentUser)` - Recherche d'utilisateurs
- `searchByLocation(string $location, User $currentUser)` - Recherche par localisation
- `advancedSearch(array $criteria, User $currentUser)` - Recherche avancée

### 7. AnalyticsService
Gère les statistiques et analytics de l'application.

**Fonctionnalités :**
- Statistiques utilisateur
- Statistiques familiales
- Analytics d'activité
- Croissance de l'application

**Méthodes principales :**
- `getUserStats(User $user)` - Statistiques utilisateur
- `getFamilyStats(Family $family)` - Statistiques familiales
- `getUserActivity(User $user, int $days)` - Activité utilisateur
- `getGlobalStats()` - Statistiques globales

### 8. EventService
Gère les événements et notifications en temps réel.

**Fonctionnalités :**
- Gestion des événements système
- Notifications en temps réel
- Logs d'événements
- Déclenchement d'actions automatiques

**Méthodes principales :**
- `handleNewMessage(Message $message)` - Nouveau message
- `handleRelationshipRequest(RelationshipRequest $request)` - Nouvelle demande de relation
- `handleUserRegistration(User $user)` - Nouvelle inscription

## Modèles Ajoutés

### 1. FamilyRelationship
Gère les relations familiales entre utilisateurs.

**Champs :**
- `user_id` - ID de l'utilisateur principal
- `related_user_id` - ID de l'utilisateur lié
- `relationship_type_id` - Type de relation
- `status` - Statut de la relation (pending, accepted, rejected)
- `mother_name` - Nom de la mère (pour certaines relations)
- `accepted_at` - Date d'acceptation

### 2. RelationshipRequest
Gère les demandes de relations familiales.

**Champs :**
- `requester_id` - ID du demandeur
- `target_user_id` - ID de la cible
- `relationship_type_id` - Type de relation demandée
- `message` - Message de la demande
- `status` - Statut de la demande
- `responded_at` - Date de réponse

### 3. RelationshipType
Définit les types de relations familiales.

**Champs :**
- `name` - Nom du type de relation
- `description` - Description
- `requires_mother_name` - Si le nom de la mère est requis

## Configuration

Les services sont configurés dans `config/services.php` avec les options suivantes :

```php
'file_upload' => [
    'max_size' => 10 * 1024 * 1024, // 10MB
    'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
],

'notifications' => [
    'email_enabled' => true,
    'push_enabled' => false,
],

'family' => [
    'max_members' => 100,
    'auto_suggestions' => true,
],
```

## Utilisation

### Dans les Contrôleurs

```php
class DashboardController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService,
        private NetworkService $networkService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $stats = $this->analyticsService->getUserStats($user);
        $network = $this->networkService->getUserNetwork($user);

        return Inertia::render('dashboard', [
            'stats' => $stats,
            'network' => $network,
        ]);
    }
}
```

### Dans les Services

```php
class EventService
{
    public function __construct(
        private NotificationService $notificationService,
        private EmailService $emailService
    ) {}

    public function handleNewMessage(Message $message): void
    {
        // Créer une notification
        $this->notificationService->createNotification(
            $message->receiver,
            'new_message',
            "Nouveau message de {$message->sender->name}"
        );

        // Envoyer un email
        $this->emailService->sendNewMessageEmail($message->sender, $message->receiver);
    }
}
```

## Routes Ajoutées

Les nouvelles routes sont définies dans `routes/web.php` :

```php
// Relations familiales
Route::get('family-relations', [FamilyRelationController::class, 'index']);
Route::post('family-relations', [FamilyRelationController::class, 'store']);
Route::post('family-relations/{requestId}/accept', [FamilyRelationController::class, 'accept']);
Route::post('family-relations/{requestId}/reject', [FamilyRelationController::class, 'reject']);

// Réseaux
Route::get('networks', [NetworkController::class, 'index']);
Route::post('networks', [NetworkController::class, 'store']);
Route::delete('networks/{network}', [NetworkController::class, 'destroy']);
Route::get('networks/search', [NetworkController::class, 'search']);
```

## Prochaines Étapes

1. **Tests** : Créer des tests unitaires pour chaque service
2. **Interface** : Développer les composants React pour les nouvelles fonctionnalités
3. **Optimisation** : Optimiser les requêtes de base de données
4. **Sécurité** : Ajouter des validations et permissions supplémentaires
5. **Performance** : Implémenter le cache pour les requêtes fréquentes 
