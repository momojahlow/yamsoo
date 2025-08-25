import { useEffect, useRef } from 'react';

interface Message {
    id: number;
    content: string;
    type: 'text' | 'image' | 'file' | 'audio' | 'video';
    file_url?: string;
    file_name?: string;
    file_size?: string;
    created_at: string;
    is_edited: boolean;
    edited_at?: string;
    user: {
        id: number;
        name: string;
        avatar?: string;
    };
    reply_to?: any;
    reactions: any[];
}

export const useMessagePolling = (
    conversationId: number | null,
    currentMessages: Message[],
    onNewMessages: (messages: Message[]) => void,
    enabled: boolean = false // Désactivé par défaut, utilisé seulement si Reverb ne fonctionne pas
) => {
    const intervalRef = useRef<NodeJS.Timeout | null>(null);
    const lastMessageIdRef = useRef<number | null>(null);

    useEffect(() => {
        if (!enabled || !conversationId) {
            return;
        }

        // Initialiser avec le dernier message ID
        if (currentMessages.length > 0) {
            lastMessageIdRef.current = Math.max(...currentMessages.map(m => m.id));
        }

        const pollMessages = async () => {
            try {
                const response = await fetch(`/api/conversations/${conversationId}/messages/since/${lastMessageIdRef.current || 0}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.messages && data.messages.length > 0) {
                        console.log('📨 Nouveaux messages via polling:', data.messages);
                        onNewMessages(data.messages);
                        lastMessageIdRef.current = Math.max(...data.messages.map((m: Message) => m.id));
                    }
                }
            } catch (error) {
                console.error('Erreur lors du polling des messages:', error);
            }
        };

        // Polling toutes les 2 secondes
        intervalRef.current = setInterval(pollMessages, 2000);

        return () => {
            if (intervalRef.current) {
                clearInterval(intervalRef.current);
            }
        };
    }, [conversationId, enabled, onNewMessages]);

    // Nettoyer l'intervalle
    useEffect(() => {
        return () => {
            if (intervalRef.current) {
                clearInterval(intervalRef.current);
            }
        };
    }, []);
};
