import { useEffect } from 'react';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import Ably from 'ably';

export const useEchoInitialization = () => {
    useEffect(() => {
        // Configuration des variables globales
        (window as any).Pusher = Pusher;
        (window as any).Ably = Ably;

        // Variables d'environnement
        const broadcastDriver = import.meta.env.VITE_BROADCAST_CONNECTION || 'ably';
        const reverbHost = import.meta.env.VITE_REVERB_HOST || 'localhost';
        const reverbPort = parseInt(import.meta.env.VITE_REVERB_PORT) || 443;
        const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || 'http';

        // Logs de configuration (maintenant côté client)
        console.log('🚀 Initialisation Laravel Echo côté client');
        console.log('📡 Driver de broadcast:', broadcastDriver);
        console.log('🔧 Configuration:', {
            driver: broadcastDriver,
            ably_key: import.meta.env.VITE_ABLY_PUBLIC_KEY ? '✅ Configuré' : '❌ Manquant',
            reverb_host: reverbHost,
            reverb_port: reverbPort,
            reverb_scheme: reverbScheme,
        });

        try {
            // Configuration conditionnelle selon le driver
            if (broadcastDriver === 'ably') {
                console.log('🟢 Configuration Echo avec Ably');
                
                if (!import.meta.env.VITE_ABLY_PUBLIC_KEY) {
                    throw new Error('VITE_ABLY_PUBLIC_KEY manquant pour Ably');
                }

                (window as any).Echo = new Echo({
                    broadcaster: 'ably',
                    key: import.meta.env.VITE_ABLY_PUBLIC_KEY,
                    authEndpoint: '/broadcasting/auth',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                    },
                });

                console.log('✅ Echo configuré avec Ably Native Driver');
                console.log('🔑 Clé Ably:', import.meta.env.VITE_ABLY_PUBLIC_KEY);
                
            } else {
                console.log('🔵 Configuration Echo avec Reverb');
                
                (window as any).Echo = new Echo({
                    broadcaster: 'reverb',
                    key: import.meta.env.VITE_REVERB_APP_KEY || 'yamsoo-key-secure-2024',
                    wsHost: reverbHost,
                    wsPort: reverbPort,
                    wssPort: reverbPort,
                    forceTLS: reverbScheme === 'https',
                    encrypted: reverbScheme === 'https',
                    enabledTransports: ['ws', 'wss'],
                    authEndpoint: '/broadcasting/auth',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                    },
                    reconnectionAttempts: 3,
                    reconnectionDelay: 3000,
                    cluster: undefined,
                });

                console.log('✅ Echo configuré avec Reverb');
                console.log(`🌐 Host: ${reverbHost}:${reverbPort} (${reverbScheme})`);
            }

            // Gestion des événements de connexion
            if ((window as any).Echo.connector && (window as any).Echo.connector.pusher) {
                const connection = (window as any).Echo.connector.pusher.connection;
                
                connection.bind('connected', () => {
                    console.log(`✅ WebSocket connecté (${broadcastDriver})`);
                });

                connection.bind('disconnected', () => {
                    console.log(`❌ WebSocket déconnecté (${broadcastDriver})`);
                });

                connection.bind('error', (error: any) => {
                    console.error(`🚨 Erreur WebSocket (${broadcastDriver}):`, error);
                });

                connection.bind('state_change', (states: any) => {
                    console.log(`🔄 État WebSocket (${broadcastDriver}): ${states.previous} → ${states.current}`);
                });

                if (broadcastDriver === 'ably') {
                    connection.bind('connecting', () => {
                        console.log('🔄 Connexion à Ably en cours...');
                    });
                }
            }

            console.log(`🎯 Laravel Echo initialisé avec succès (${broadcastDriver.toUpperCase()})`);

        } catch (error) {
            console.error('🚨 ERREUR CRITIQUE lors de l\'initialisation d\'Echo:', error);
            
            // Créer un Echo factice robuste pour éviter les crashes
            (window as any).Echo = {
                channel: () => ({ 
                    listen: () => ({ error: () => {} }), 
                    whisper: () => {},
                    error: () => {},
                    stopListening: () => {}
                }),
                private: () => ({ 
                    listen: () => ({ error: () => {} }), 
                    whisper: () => {},
                    error: () => {},
                    stopListening: () => {}
                }),
                join: () => ({ 
                    listen: () => ({ error: () => {} }), 
                    whisper: () => {},
                    error: () => {},
                    stopListening: () => {}
                }),
                leave: () => {},
                disconnect: () => {},
                connector: null
            };
            
            console.warn('⚠️ Echo factice créé - fonctionnalités temps réel désactivées');
        }
    }, []); // Exécuter une seule fois au montage du composant
};
