import { useState, useEffect, useCallback, useRef } from 'react';

interface User {
    id: number;
    name: string;
    avatar?: string;
}

interface LastMessage {
    content: string;
    created_at: string;
    user_name: string;
    is_own: boolean;
}

interface ConversationSummary {
    id: number;
    name: string;
    type: 'private' | 'group';
    avatar?: string;
    last_message?: LastMessage;
    unread_count: number;
    is_online?: boolean;
    participants_count?: number;
    other_participant?: User;
}

interface MessengerData {
    conversations: ConversationSummary[];
    total_unread_count: number;
    user: User;
}

declare global {
    interface Window {
        Echo: any;
    }
}

export function useMessengerState(currentUser: User) {
    const [conversations, setConversations] = useState<ConversationSummary[]>([]);
    const [totalUnreadCount, setTotalUnreadCount] = useState(0);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const lastFetchRef = useRef<number>(0);
    const isSubscribedRef = useRef(false);

    // Récupérer les conversations depuis l'API
    const fetchConversations = useCallback(async () => {
        // Éviter les appels trop fréquents
        const now = Date.now();
        if (now - lastFetchRef.current < 1000) {
            return;
        }
        lastFetchRef.current = now;

        try {
            setError(null);
            const response = await fetch('/api/messenger/conversations-summary', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data: MessengerData = await response.json();
            
            setConversations(data.conversations);
            setTotalUnreadCount(data.total_unread_count);
            
            console.log('📨 Conversations mises à jour:', data.conversations.length, 'conversations,', data.total_unread_count, 'messages non lus');
        } catch (err) {
            console.error('❌ Erreur lors de la récupération des conversations:', err);
            setError(err instanceof Error ? err.message : 'Erreur inconnue');
        } finally {
            setLoading(false);
        }
    }, []);

    // Gérer les nouveaux messages reçus via Echo
    const handleNewMessage = useCallback((event: any) => {
        const message = event.message;
        
        // Ne pas traiter ses propres messages
        if (message.user.id === currentUser.id) {
            return;
        }

        console.log('📨 Nouveau message reçu via Echo:', message);

        // Mettre à jour les conversations
        setConversations(prevConversations => {
            const updatedConversations = prevConversations.map(conv => {
                if (conv.id === message.conversation_id) {
                    return {
                        ...conv,
                        last_message: {
                            content: message.content,
                            created_at: message.created_at,
                            user_name: message.user.name,
                            is_own: false
                        },
                        unread_count: conv.unread_count + 1
                    };
                }
                return conv;
            });

            // Si la conversation n'existe pas dans la liste, la récupérer
            const conversationExists = updatedConversations.some(conv => conv.id === message.conversation_id);
            if (!conversationExists) {
                // Récupérer les conversations à nouveau pour inclure la nouvelle
                setTimeout(fetchConversations, 500);
            }

            return updatedConversations;
        });

        // Mettre à jour le compteur total
        setTotalUnreadCount(prev => prev + 1);
    }, [currentUser.id, fetchConversations]);

    // Marquer une conversation comme lue
    const markConversationAsRead = useCallback((conversationId: number) => {
        setConversations(prevConversations => {
            return prevConversations.map(conv => {
                if (conv.id === conversationId) {
                    const unreadBefore = conv.unread_count;
                    setTotalUnreadCount(prev => Math.max(0, prev - unreadBefore));
                    return {
                        ...conv,
                        unread_count: 0
                    };
                }
                return conv;
            });
        });
    }, []);

    // Initialisation et abonnements Echo
    useEffect(() => {
        // Récupérer les conversations initiales
        fetchConversations();

        // S'abonner aux notifications globales via Echo
        if (window.Echo && !isSubscribedRef.current) {
            console.log('🔊 Abonnement aux notifications globales Messenger');
            
            try {
                // S'abonner au canal privé de l'utilisateur pour recevoir toutes les notifications
                const userChannel = window.Echo.private(`App.Models.User.${currentUser.id}`)
                    .notification((notification: any) => {
                        console.log('🔔 Notification reçue:', notification);
                        
                        // Si c'est une notification de nouveau message
                        if (notification.type === 'App\\Notifications\\NewMessage') {
                            handleNewMessage({ message: notification.message });
                        }
                    });

                // S'abonner aussi directement aux événements MessageSent
                conversations.forEach(conversation => {
                    window.Echo.private(`conversation.${conversation.id}`)
                        .listen('.message.sent', handleNewMessage);
                });

                isSubscribedRef.current = true;

                return () => {
                    console.log('🔇 Nettoyage des abonnements Messenger');
                    try {
                        window.Echo.leave(`App.Models.User.${currentUser.id}`);
                        conversations.forEach(conversation => {
                            window.Echo.leave(`conversation.${conversation.id}`);
                        });
                    } catch (error) {
                        console.error('❌ Erreur lors du nettoyage Echo:', error);
                    }
                    isSubscribedRef.current = false;
                };
            } catch (error) {
                console.error('❌ Erreur lors de l\'abonnement Echo:', error);
            }
        }

        // Actualiser périodiquement les conversations (fallback)
        const interval = setInterval(fetchConversations, 30000); // Toutes les 30 secondes

        return () => {
            clearInterval(interval);
        };
    }, [currentUser.id, fetchConversations, handleNewMessage, conversations]);

    // Actualiser les conversations quand la liste change
    useEffect(() => {
        if (window.Echo && isSubscribedRef.current && conversations.length > 0) {
            // Réabonner aux nouvelles conversations
            conversations.forEach(conversation => {
                try {
                    window.Echo.private(`conversation.${conversation.id}`)
                        .listen('.message.sent', handleNewMessage);
                } catch (error) {
                    console.error(`❌ Erreur abonnement conversation ${conversation.id}:`, error);
                }
            });
        }
    }, [conversations, handleNewMessage]);

    return {
        conversations,
        totalUnreadCount,
        loading,
        error,
        fetchConversations,
        markConversationAsRead
    };
}
