import { useState, useCallback } from 'react';
import { router } from '@inertiajs/react';

interface Message {
    id: number;
    content: string;
    type: 'text' | 'image' | 'file' | 'audio' | 'video';
    created_at: string;
    user: {
        id: number;
        name: string;
        avatar?: string;
    };
}

interface Conversation {
    id: number | null;
    name: string;
    type: 'private' | 'group';
    avatar?: string;
    is_online?: boolean;
    other_participant_id?: number;
    is_new?: boolean;
}

export function useSimpleMessaging() {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    // Démarrer une conversation simple
    const startConversation = useCallback((targetUserId: number, message: string) => {
        setLoading(true);
        setError(null);

        router.post('/messagerie/start', {
            target_user_id: targetUserId,
            message: message
        }, {
            onSuccess: () => {
                setLoading(false);
            },
            onError: (errors) => {
                setLoading(false);
                setError('Erreur lors de la création de la conversation');
                console.error('Erreur:', errors);
            }
        });
    }, []);

    // Envoyer un message simple
    const sendMessage = useCallback((conversationId: number, message: string) => {
        setLoading(true);
        setError(null);

        router.post('/messagerie/send', {
            conversation_id: conversationId,
            message: message
        }, {
            onSuccess: () => {
                setLoading(false);
            },
            onError: (errors) => {
                setLoading(false);
                setError('Erreur lors de l\'envoi du message');
                console.error('Erreur:', errors);
            }
        });
    }, []);

    // Aller à la messagerie avec un contact sélectionné
    const goToConversation = useCallback((targetUserId?: number) => {
        const url = targetUserId 
            ? `/messagerie?selectedContactId=${targetUserId}`
            : '/messagerie';
        router.visit(url);
    }, []);

    // Créer un groupe familial
    const createFamilyGroup = useCallback((groupName?: string) => {
        const url = groupName 
            ? `/messagerie/family-group?name=${encodeURIComponent(groupName)}`
            : '/messagerie/family-group';
        router.visit(url);
    }, []);

    return {
        loading,
        error,
        startConversation,
        sendMessage,
        goToConversation,
        createFamilyGroup
    };
}
