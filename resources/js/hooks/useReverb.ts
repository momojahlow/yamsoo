import { useEffect } from 'react';

declare global {
    interface Window {
        Echo: any;
    }
}

export const useReverb = () => {
    return window.Echo;
};

export const useConversationChannel = (
    conversationId: number | null,
    onMessageReceived: (message: any) => void
) => {
    useEffect(() => {
        if (!conversationId) {
            console.log('useConversationChannel: Pas de conversationId');
            return;
        }

        console.log(`🔊 Écoute de conversation.${conversationId}`);

        try {
            const channel = window.Echo.private(`conversation.${conversationId}`)
                .listen('.message.sent', (e: any) => {
                    console.log('📨 Message reçu via Reverb:', e);
                    if (e.message) {
                        onMessageReceived(e.message);
                    }
                })
                .error((error: any) => {
                    console.error('❌ Erreur channel Reverb:', error);
                });

            // Test de connexion
            console.log('✅ Channel créé:', channel);

            return () => {
                console.log(`👋 Quitte conversation.${conversationId}`);
                try {
                    window.Echo.leave(`conversation.${conversationId}`);
                } catch (error) {
                    console.error('Erreur lors de la déconnexion:', error);
                }
            };
        } catch (error) {
            console.error('❌ Erreur lors de la création du channel:', error);
        }
    }, [conversationId, onMessageReceived]);
};
