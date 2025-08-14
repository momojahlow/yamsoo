# Laravel Reverb - Configuration pour Messagerie Instantanée

## ✅ Configuration Actuelle

Laravel Reverb est déjà installé et configuré dans votre application Yamsoo. Voici l'état actuel :

### 📋 Variables d'environnement (.env)
```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=yamsoo
REVERB_APP_KEY=yamsoo-key
REVERB_APP_SECRET=yamsoo-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### 🔧 Packages installés
- ✅ `laravel/reverb` (serveur WebSocket)
- ✅ `laravel-echo` (client JavaScript)
- ✅ `pusher-js` (driver de transport)

### ⚙️ Configuration Echo (app.tsx)
```typescript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

## 🚀 Démarrage du serveur Reverb

### 1. Démarrer le serveur WebSocket
```bash
php artisan reverb:start
```

### 2. Démarrer en mode debug (recommandé pour développement)
```bash
php artisan reverb:start --debug
```

### 3. Démarrer sur un port spécifique
```bash
php artisan reverb:start --port=8080
```

## 💬 Utilisation pour la Messagerie

### 1. Créer un Event pour les messages
```php
// app/Events/MessageSent.php
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public $message,
        public $user,
        public $conversationId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->conversationId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'user' => $this->user,
            'timestamp' => now()->toISOString(),
        ];
    }
}
```

### 2. Écouter les messages côté client (React)
```typescript
// Dans votre composant de messagerie
useEffect(() => {
    const channel = window.Echo.private(`conversation.${conversationId}`)
        .listen('MessageSent', (e) => {
            console.log('Nouveau message reçu:', e);
            // Ajouter le message à l'interface
            setMessages(prev => [...prev, e.message]);
        });

    return () => {
        window.Echo.leaveChannel(`conversation.${conversationId}`);
    };
}, [conversationId]);
```

### 3. Envoyer un message (côté serveur)
```php
// Dans votre MessageController
public function store(Request $request)
{
    $message = Message::create([
        'content' => $request->content,
        'user_id' => auth()->id(),
        'conversation_id' => $request->conversation_id,
    ]);

    // Diffuser l'événement
    broadcast(new MessageSent($message, auth()->user(), $request->conversation_id));

    return response()->json($message);
}
```

## 🔐 Authentification des Channels

### 1. Définir les routes de broadcast (routes/channels.php)
```php
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    // Vérifier que l'utilisateur peut accéder à cette conversation
    return Conversation::where('id', $conversationId)
        ->whereHas('participants', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->exists();
});
```

## 📊 Monitoring et Debug

### 1. Vérifier le statut du serveur
```bash
curl http://localhost:8080/app/yamsoo-key
```

### 2. Logs de debug
Le serveur Reverb affiche les connexions et messages en temps réel quand lancé avec `--debug`.

### 3. Tester la connexion WebSocket
```javascript
// Dans la console du navigateur
window.Echo.connector.pusher.connection.state
// Devrait retourner "connected"
```

## 🛠️ Dépannage

### Problème : Serveur ne démarre pas
- Vérifier que le port 8080 n'est pas utilisé
- Vérifier les variables d'environnement
- Redémarrer avec `php artisan reverb:restart`

### Problème : Messages ne sont pas reçus
- Vérifier que le serveur Reverb est démarré
- Vérifier l'authentification des channels
- Vérifier la console du navigateur pour les erreurs

### Problème : Connexion WebSocket échoue
- Vérifier les variables VITE_* dans .env
- Rebuilder les assets : `npm run build`
- Vérifier les CORS si nécessaire

## 🎯 Prochaines étapes

1. **Démarrer le serveur Reverb** : `php artisan reverb:start --debug`
2. **Tester la connexion** dans la console du navigateur
3. **Implémenter les Events** pour vos messages
4. **Ajouter l'écoute** dans vos composants React
5. **Tester la messagerie** en temps réel

## 📝 Notes importantes

- Le serveur Reverb doit être démarré pour que la messagerie instantanée fonctionne
- En production, utilisez un gestionnaire de processus comme Supervisor
- Configurez HTTPS en production pour les connexions sécurisées
- Surveillez les performances et la mémoire du serveur Reverb
