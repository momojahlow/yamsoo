import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { Toaster } from '@/components/ui/toaster';
import './i18n/config';
// Configuration Echo - sera activée une fois les packages installés
try {
    // Vérifier si les packages sont disponibles avant de les importer
    if (typeof window !== 'undefined') {
        // Les imports dynamiques seront ajoutés après installation des packages
        console.log('Echo configuration will be loaded after package installation');
    }
} catch (error) {
    console.warn('Echo packages not yet installed:', error);
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
