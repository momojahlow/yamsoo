import React, { createContext, useContext, useReducer, useEffect, ReactNode } from 'react';

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
            const response = await fetch('/api/messenger/conversations-summary', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                }
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

    // Ajouter un nouveau message
    const addMessage = (conversationId: number, message: any, currentUserId: number) => {
        dispatch({ 
            type: 'ADD_MESSAGE', 
            payload: { 
                conversationId, 
                message: { ...message, currentUserId } 
            } 
        });
    };

    // Initialisation
    useEffect(() => {
        fetchConversations();

        // Actualiser périodiquement
        const interval = setInterval(fetchConversations, 60000); // Toutes les minutes

        return () => clearInterval(interval);
    }, [currentUser.id]);

    // Écouter les nouveaux messages via Echo
    useEffect(() => {
        if (window.Echo && state.conversations.length > 0) {
            console.log('🔊 Abonnement Echo pour', state.conversations.length, 'conversations');

            const channels: any[] = [];

            state.conversations.forEach(conversation => {
                try {
                    const channel = window.Echo.private(`conversation.${conversation.id}`)
                        .listen('.message.sent', (event: any) => {
                            console.log('📨 Message Echo reçu pour conversation', conversation.id);
                            addMessage(conversation.id, event.message, currentUser.id);
                        });

                    channels.push(channel);
                } catch (error) {
                    console.error(`❌ Erreur abonnement conversation ${conversation.id}:`, error);
                }
            });

            return () => {
                console.log('🔇 Nettoyage abonnements Echo Messenger');
                channels.forEach((_, index) => {
                    try {
                        window.Echo.leave(`conversation.${state.conversations[index]?.id}`);
                    } catch (error) {
                        console.error('❌ Erreur nettoyage Echo:', error);
                    }
                });
            };
        }
    }, [state.conversations, currentUser.id]);

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
