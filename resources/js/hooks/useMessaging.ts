import { useState, useEffect, useCallback } from 'react';
import axios from 'axios';

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
    reply_to?: {
        id: number;
        content: string;
        user_name: string;
    };
    reactions: Array<{
        emoji: string;
        count: number;
        users: string[];
    }>;
}

interface Conversation {
    id: number;
    name: string;
    type: 'private' | 'group';
    avatar?: string;
    participants: Array<{
        id: number;
        name: string;
        avatar?: string;
        is_online: boolean;
    }>;
}

export function useMessaging(conversationId?: number) {
    const [messages, setMessages] = useState<Message[]>([]);
    const [conversation, setConversation] = useState<Conversation | null>(null);
    const [loading, setLoading] = useState(false);
    const [sending, setSending] = useState(false);
    const [error, setError] = useState<string | null>(null);

    // Charger les messages d'une conversation
    const loadMessages = useCallback(async (id: number) => {
        if (!id) return;

        setLoading(true);
        setError(null);
        
        try {
            const response = await axios.get(`/api/conversations/${id}/messages`);
            setMessages(response.data.messages);
            setConversation(response.data.conversation);
        } catch (err) {
            setError('Erreur lors du chargement des messages');
            console.error('Erreur lors du chargement des messages:', err);
        } finally {
            setLoading(false);
        }
    }, []);

    // Envoyer un message
    const sendMessage = useCallback(async (
        conversationId: number,
        content: string,
        file?: File,
        replyToId?: number
    ) => {
        setSending(true);
        setError(null);

        try {
            const formData = new FormData();
            if (content.trim()) {
                formData.append('content', content.trim());
            }
            if (file) {
                formData.append('file', file);
            }
            if (replyToId) {
                formData.append('reply_to_id', replyToId.toString());
            }

            const response = await axios.post(`/api/conversations/${conversationId}/messages`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            // Ajouter le nouveau message à la liste
            setMessages(prev => [...prev, response.data.message]);
            
            return response.data.message;
        } catch (err) {
            setError('Erreur lors de l\'envoi du message');
            console.error('Erreur lors de l\'envoi du message:', err);
            throw err;
        } finally {
            setSending(false);
        }
    }, []);

    // Créer une nouvelle conversation
    const createConversation = useCallback(async (userId: number, type: 'private' | 'group' = 'private') => {
        try {
            const response = await axios.post('/api/conversations', {
                user_id: userId,
                type
            });
            return response.data.conversation_id;
        } catch (err) {
            setError('Erreur lors de la création de la conversation');
            console.error('Erreur lors de la création de la conversation:', err);
            throw err;
        }
    }, []);

    // Ajouter un message reçu en temps réel
    const addMessage = useCallback((message: Message) => {
        setMessages(prev => {
            // Éviter les doublons
            if (prev.some(m => m.id === message.id)) {
                return prev;
            }
            return [...prev, message];
        });
    }, []);

    // Marquer les messages comme lus
    const markAsRead = useCallback(async (conversationId: number) => {
        try {
            await axios.post(`/api/conversations/${conversationId}/mark-read`);
        } catch (err) {
            console.error('Erreur lors du marquage comme lu:', err);
        }
    }, []);

    // Charger les messages au changement de conversation
    useEffect(() => {
        if (conversationId) {
            loadMessages(conversationId);
        }
    }, [conversationId, loadMessages]);

    return {
        messages,
        conversation,
        loading,
        sending,
        error,
        loadMessages,
        sendMessage,
        createConversation,
        addMessage,
        markAsRead,
        setError
    };
}

// Hook pour la recherche d'utilisateurs
export function useUserSearch() {
    const [users, setUsers] = useState<Array<{
        id: number;
        name: string;
        email: string;
        avatar?: string;
        is_online: boolean;
    }>>([]);
    const [loading, setLoading] = useState(false);

    const searchUsers = useCallback(async (query: string) => {
        if (query.length < 2) {
            setUsers([]);
            return;
        }

        setLoading(true);
        try {
            const response = await axios.get('/api/users/search', {
                params: { q: query }
            });
            setUsers(response.data.users);
        } catch (err) {
            console.error('Erreur lors de la recherche:', err);
            setUsers([]);
        } finally {
            setLoading(false);
        }
    }, []);

    return {
        users,
        loading,
        searchUsers
    };
}
