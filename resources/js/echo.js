import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Configuration globale de Pusher pour Reverb
window.Pusher = Pusher;

// Configuration et initialisation d'Echo
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 4010,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 4010,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json',
        },
    },
});

// Debug pour vérifier la configuration
console.log('🔊 Laravel Echo initialisé avec Reverb');
console.log('📡 Configuration:', {
    key: import.meta.env.VITE_REVERB_APP_KEY,
    host: import.meta.env.VITE_REVERB_HOST,
    port: import.meta.env.VITE_REVERB_PORT,
    scheme: import.meta.env.VITE_REVERB_SCHEME,
});

export default window.Echo;
