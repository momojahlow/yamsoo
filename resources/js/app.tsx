// Imports principaux
import '../css/app.css';
import './i18n/config';

// Imports React et Inertia
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

// Imports UI
import { Toaster } from '@/components/ui/toaster';

// Import Echo configuration
import './echo';

// Configuration des variables globales
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Service Worker pour PWA (production uniquement)
if ('serviceWorker' in navigator && import.meta.env.PROD) {
    window.addEventListener('load', async () => {
        try {
            const registration = await navigator.serviceWorker.register('/sw.js');
            console.log('📱 Service Worker enregistré:', registration.scope);

            // Gestion des mises à jour
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                if (newWorker) {
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            console.log('🔄 Nouvelle version disponible');
                        }
                    });
                }
            });
        } catch (error) {
            console.error('❌ Erreur Service Worker:', error);
        }
    });
}

// Initialisation de l'application Inertia
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
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
    progress: {
        color: '#f97316', // Orange pour la barre de progression
        showSpinner: true,
    },
});

console.log(`🚀 Application ${appName} initialisée avec succès`);
