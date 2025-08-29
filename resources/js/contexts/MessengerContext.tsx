import React, { createContext, useContext, useReducer, useEffect, useCallback, useRef, ReactNode } from 'react';

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

interface MessengerState {
    conversations: ConversationSummary[];
    totalUnreadCount: number;
    loading: boolean;
    error: string | null;
    lastUpdated: number;
}

type MessengerAction =
    | { type: 'SET_LOADING'; payload: boolean }
    | { type: 'SET_ERROR'; payload: string | null }
    | { type: 'SET_CONVERSATIONS'; payload: ConversationSummary[] }
    | { type: 'SET_TOTAL_UNREAD'; payload: number }
    | { type: 'ADD_MESSAGE'; payload: { conversationId: number; message: any } }
    | { type: 'MARK_AS_READ'; payload: number }
    | { type: 'UPDATE_CONVERSATION'; payload: { id: number; updates: Partial<ConversationSummary> } };

const initialState: MessengerState = {
    conversations: [],
    totalUnreadCount: 0,
    loading: true,
    error: null,
    lastUpdated: 0
};

function messengerReducer(state: MessengerState, action: MessengerAction): MessengerState {
    switch (action.type) {
        case 'SET_LOADING':
            return { ...state, loading: action.payload };

        case 'SET_ERROR':
            return { ...state, error: action.payload, loading: false };

        case 'SET_CONVERSATIONS':
            const totalUnread = action.payload.reduce((sum, conv) => sum + conv.unread_count, 0);
            return {
                ...state,
                conversations: action.payload,
                totalUnreadCount: totalUnread,
                loading: false,
                error: null,
                lastUpdated: Date.now()
            };

        case 'SET_TOTAL_UNREAD':
            return { ...state, totalUnreadCount: action.payload };

        case 'ADD_MESSAGE':
            const { conversationId, message } = action.payload;
            const updatedConversations = state.conversations.map(conv => {
                if (conv.id === conversationId) {
                    return {
                        ...conv,
                        last_message: {
                            content: message.content,
                            created_at: message.created_at,
                            user_name: message.user.name,
                            is_own: message.user.id === message.currentUserId
                        },
                        unread_count: message.user.id !== message.currentUserId
                            ? conv.unread_count + 1
                            : conv.unread_count
                    };
                }
                return conv;
            });

            const newTotalUnread = updatedConversations.reduce((sum, conv) => sum + conv.unread_count, 0);

            return {
                ...state,
                conversations: updatedConversations,
                totalUnreadCount: newTotalUnread
            };

        case 'MARK_AS_READ':
            const conversationIdToRead = action.payload;
            const conversationsAfterRead = state.conversations.map(conv => {
                if (conv.id === conversationIdToRead) {
                    return { ...conv, unread_count: 0 };
                }
                return conv;
            });

            const totalAfterRead = conversationsAfterRead.reduce((sum, conv) => sum + conv.unread_count, 0);

            return {
                ...state,
                conversations: conversationsAfterRead,
                totalUnreadCount: totalAfterRead
            };

        case 'UPDATE_CONVERSATION':
            const { id, updates } = action.payload;
            const updatedConvs = state.conversations.map(conv => {
                if (conv.id === id) {
                    return { ...conv, ...updates };
                }
                return conv;
            });

            return {
                ...state,
                conversations: updatedConvs
            };

        default:
            return state;
    }
}

interface MessengerContextType {
    state: MessengerState;
    dispatch: React.Dispatch<MessengerAction>;
    fetchConversations: () => Promise<void>;
    markAsRead: (conversationId: number) => void;
    addMessage: (conversationId: number, message: any, currentUserId: number) => void;
}

const MessengerContext = createContext<MessengerContextType | undefined>(undefined);

interface MessengerProviderProps {
    children: ReactNode;
    currentUser: User;
}

export function MessengerProvider({ children, currentUser }: MessengerProviderProps) {
    const [state, dispatch] = useReducer(messengerReducer, initialState);

    // Récupérer les conversations depuis l'API
    const fetchConversations = async () => {
        dispatch({ type: 'SET_LOADING', payload: true });

        try {
            const response = await fetch('/messenger/conversations-summary', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin' // Inclure les cookies de session
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            dispatch({ type: 'SET_CONVERSATIONS', payload: data.conversations });

            console.log('📨 Conversations Messenger mises à jour:', data.conversations.length);
        } catch (error) {
            console.error('❌ Erreur fetchConversations:', error);
            dispatch({ type: 'SET_ERROR', payload: error instanceof Error ? error.message : 'Erreur inconnue' });
        }
    };

    // Marquer une conversation comme lue
    const markAsRead = (conversationId: number) => {
        dispatch({ type: 'MARK_AS_READ', payload: conversationId });
    };

    // Ajouter un nouveau message (stable avec useCallback)
    const addMessage = useCallback((conversationId: number, message: any, currentUserId: number) => {
        dispatch({
            type: 'ADD_MESSAGE',
            payload: {
                conversationId,
                message: { ...message, currentUserId }
            }
        });
    }, []); // Pas de dépendances - fonction stable

    // Initialisation
    useEffect(() => {
        fetchConversations();

        // Actualiser périodiquement
        const interval = setInterval(fetchConversations, 60000); // Toutes les minutes

        return () => clearInterval(interval);
    }, [currentUser.id]);



    // Référence pour tracker les canaux actifs
    const activeChannels = useRef<Map<number, any>>(new Map());

    // Écouter les nouveaux messages via Echo avec Pusher
    useEffect(() => {
        console.log('🔄 useEffect Echo déclenché', {
            hasEcho: !!window.Echo,
            conversationsCount: state.conversations.length,
            conversations: state.conversations.map(c => c.id)
        });

        if (!window.Echo) {
            console.log('❌ window.Echo non disponible');
            return;
        }

        if (!state.conversations.length) {
            console.log('❌ Aucune conversation disponible');
            return;
        }

        console.log('🔊 Mise à jour des abonnements Echo');
        const newChannels = new Map();

        // Abonnement aux canaux privés pour chaque conversation
        state.conversations.forEach(conversation => {
            const conversationId = conversation.id;

            // Si déjà abonné, on conserve le canal
            if (activeChannels.current.has(conversationId)) {
                const existingChannel = activeChannels.current.get(conversationId);
                newChannels.set(conversationId, existingChannel);
                console.log(`✓ Canal conversation.${conversationId} déjà abonné`);
                return;
            }

            // Nouvel abonnement
            try {
                console.log(`🔗 Nouvel abonnement: conversation.${conversationId}`);

                const channel = window.Echo.private(`conversation.${conversationId}`)
                    .listen('.message.sent', (event: any) => {
                        console.log('📨 Message reçu', conversationId, event);
                        addMessage(conversationId, event.message, currentUser.id);
                    });

                newChannels.set(conversationId, channel);
                console.log(`✅ Abonné: conversation.${conversationId}`);
            } catch (error) {
                console.error(`❌ Erreur abonnement ${conversationId}:`, error);
            }
        });

        // Nettoyer seulement les canaux qui ne sont plus dans les conversations
        activeChannels.current.forEach((channel, conversationId) => {
            if (!newChannels.has(conversationId)) {
                try {
                    console.log(`🧹 Désabonnement: conversation.${conversationId}`);
                    channel.stopListening('.message.sent');
                } catch (error) {
                    console.error(`❌ Erreur désabonnement ${conversationId}:`, error);
                }
            }
        });

        // Mettre à jour la référence
        activeChannels.current = newChannels;

        // Gestion de la reconnexion Pusher
        const handlePusherConnected = () => {
            console.log('🔄 Pusher reconnecté - vérification des abonnements');
            // Réabonner tous les canaux actifs en cas de reconnexion
            activeChannels.current.forEach((channel, conversationId) => {
                try {
                    channel.stopListening('.message.sent');
                    channel.listen('.message.sent', (event: any) => {
                        console.log('📨 Message reçu après reconnexion', conversationId, event);
                        addMessage(conversationId, event.message, currentUser.id);
                    });
                } catch (error) {
                    console.error(`❌ Erreur réabonnement ${conversationId}:`, error);
                }
            });
        };

        window.Echo.connector.pusher.connection.bind('connected', handlePusherConnected);

        return () => {
            // Détacher le listener de connexion
            window.Echo.connector.pusher.connection.unbind('connected', handlePusherConnected);
        };
    }, [state.conversations, addMessage, currentUser.id]);

    const contextValue: MessengerContextType = {
        state,
        dispatch,
        fetchConversations,
        markAsRead,
        addMessage
    };

    return (
        <MessengerContext.Provider value={contextValue}>
            {children}
        </MessengerContext.Provider>
    );
}

export function useMessenger() {
    const context = useContext(MessengerContext);
    if (context === undefined) {
        throw new Error('useMessenger must be used within a MessengerProvider');
    }
    return context;
}
