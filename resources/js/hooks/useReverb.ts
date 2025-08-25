import { useEffect } from 'react';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
    interface Window {
        Pusher: typeof Pusher;
        Echo: Echo;
    }
}

// Configuration de Pusher pour Reverb
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

export const useReverb = () => {
    return window.Echo;
};

export const useConversationChannel = (
    conversationId: number | null,
    onMessageReceived: (message: any) => void
) => {
    useEffect(() => {
        if (!conversationId) return;

        console.log(`Listening to conversation.${conversationId}`);
        
        const channel = window.Echo.private(`conversation.${conversationId}`)
            .listen('.message.sent', (e: any) => {
                console.log('Message reçu via Reverb:', e);
                onMessageReceived(e.message);
            });

        return () => {
            console.log(`Leaving conversation.${conversationId}`);
            window.Echo.leave(`conversation.${conversationId}`);
        };
    }, [conversationId, onMessageReceived]);
};
