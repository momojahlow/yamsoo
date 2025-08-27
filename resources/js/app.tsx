import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { Toaster } from '@/components/ui/toaster';
import './i18n/config';
// Configuration Echo pour le temps réel
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Configuration globale de Pusher pour Reverb
(window as any).Pusher = Pusher;

// Configuration et initialisation d'Echo adaptative pour Herd
try {
    const reverbHost = import.meta.env.VITE_REVERB_HOST || 'localhost';

    // Forcer HTTP pour Reverb en développement local
    const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || 'http';

    // Configuration des ports - toujours utiliser le port configuré pour Reverb
    const reverbPort = parseInt(import.meta.env.VITE_REVERB_PORT) || 4010;

    console.log('🔧 Configuration Echo:', {
        host: reverbHost,
        port: reverbPort,
        scheme: reverbScheme,
        forceTLS: reverbScheme === 'https'
    });

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY || 'yamsoo-key-secure-2024',
        wsHost: reverbHost,
        wsPort: reverbPort,
        wssPort: reverbPort,
        forceTLS: reverbScheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
        },
        // Options de reconnexion pour une meilleure stabilité
        reconnectionAttempts: 3,
        reconnectionDelay: 3000,
        // Options spécifiques pour Herd
        cluster: undefined, // Pas de cluster pour Reverb
        encrypted: reverbScheme === 'https',
    });

    // Gestion des événements de connexion
    window.Echo.connector.pusher.connection.bind('connected', () => {
        console.log('✅ WebSocket connecté à Reverb');
    });

    window.Echo.connector.pusher.connection.bind('disconnected', () => {
        console.log('❌ WebSocket déconnecté de Reverb');
    });

    window.Echo.connector.pusher.connection.bind('error', (error: any) => {
        console.error('🚨 Erreur WebSocket:', error);
    });

    console.log('✅ Laravel Echo initialisé avec Reverb');
} catch (error) {
    console.error('🚨 Erreur lors de l\'initialisation d\'Echo:', error);
    // Créer un Echo factice pour éviter les erreurs
    window.Echo = {
        channel: () => ({ listen: () => {}, whisper: () => {} }),
        private: () => ({ listen: () => {}, whisper: () => {} }),
        join: () => ({ listen: () => {}, whisper: () => {} }),
        leave: () => {},
        disconnect: () => {},
    };
}

// Enregistrement du Service Worker
if ('serviceWorker' in navigator && import.meta.env.PROD) {
    window.addEventListener('load', async () => {
        try {
            const registration = await navigator.serviceWorker.register('/sw.js');
            console.log('Service Worker enregistré avec succès:', registration.scope);

            // Écouter les mises à jour du Service Worker
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                if (newWorker) {
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            // Nouvelle version disponible
                            console.log('Nouvelle version de l\'application disponible');
                            // Ici on pourrait afficher une notification à l'utilisateur
                        }
                    });
                }
            });
        } catch (error) {
            console.error('Erreur lors de l\'enregistrement du Service Worker:', error);
        }
    });
}

createInertiaApp({
  resolve: (name) => resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),
  setup({ el, App, props }) {
    const root = createRoot(el);
    root.render(
      <>
        <App {...props} />
        <Toaster />
      </>
    );
  },
});
