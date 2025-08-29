import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

console.log('🚀 Initialisation Laravel Echo avec Pusher...');

// Vérifier les variables d'environnement
const broadcastDriver = import.meta.env.VITE_BROADCAST_CONNECTION;

console.log('🔧 Configuration:', {
    driver: broadcastDriver,
    pusher_key: import.meta.env.VITE_PUSHER_APP_KEY ? '✅ Configuré' : '❌ Manquant',
    pusher_cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
});

try {
    if (broadcastDriver === 'pusher') {
        console.log('📡 Configuration Pusher...');

        if (!import.meta.env.VITE_PUSHER_APP_KEY) {
            throw new Error('VITE_PUSHER_APP_KEY manquant pour Pusher');
        }

        // Exposer Pusher globalement
        window.Pusher = Pusher;

        // Configuration SIMPLE selon la documentation Laravel officielle
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
            forceTLS: true
        });

        console.log('✅ Echo configuré avec Pusher');
        console.log('🔑 Clé Pusher:', import.meta.env.VITE_PUSHER_APP_KEY);
        console.log('🌍 Cluster:', import.meta.env.VITE_PUSHER_APP_CLUSTER);

    } else {
        console.log('⚠️ Driver de diffusion non supporté:', broadcastDriver);
    }
} catch (error) {
    console.error('🚨 ERREUR CRITIQUE lors de l\'initialisation d\'Echo:', error);
}
