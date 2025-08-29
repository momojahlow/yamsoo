import React, { useState, useEffect, useRef } from 'react';
import { Head, router } from '@inertiajs/react';
import { Search, Phone, Video, MoreVertical, Paperclip, Smile, Send, ArrowLeft, Settings, Plus, Users } from 'lucide-react';
import { KwdDashboardLayout } from '@/Layouts/modern';
import { useTranslation } from '@/hooks/useTranslation';
import { useGlobalNotifications } from '@/hooks/useGlobalNotifications';
import ConversationList from '@/components/messaging/ConversationList';
import ChatArea from '@/components/messaging/ChatArea';
import UserSearch from '@/components/messaging/UserSearch';
import MessageSearch from '@/components/messaging/MessageSearch';
import MessageSettings from '@/components/messaging/MessageSettings';
import NotificationSettings from '@/components/messaging/NotificationSettings';


interface User {
    id: number;
    name: string;
    avatar?: string;
}

interface Conversation {
    id: number;
    name: string;
    type: 'private' | 'group';
    avatar?: string;
    last_message?: {
        content: string;
        created_at: string;
        user_name: string;
        is_own: boolean;
    };
    unread_count: number;
    is_online: boolean;
}

interface Message {
    id: number;
    content: string;
    type: 'text' | 'image' | 'file' | 'audio' | 'video';
    file_url?: string;
    file_name?: string;
    created_at: string;
    user: User;
}

interface MessagingProps {
    conversations: Conversation[];
    selectedConversation?: Conversation | null;
    messages: Message[];
    targetUser?: User | null;
    user: User;
    notificationsEnabled?: boolean;
    newMessage?: {
        id: number;
        content: string;
        user_id: number;
        created_at: string;
        user: User;
    };
}

export default function Messaging({ conversations = [], selectedConversation: initialSelectedConversation, messages = [], targetUser, user, notificationsEnabled = true, newMessage }: MessagingProps) {
    const { t } = useTranslation();
    const [selectedConversation, setSelectedConversation] = useState<Conversation | null>(initialSelectedConversation || null);
    const [currentMessages, setCurrentMessages] = useState<Message[]>(messages);
    const [showUserSearch, setShowUserSearch] = useState(false);
    const [showMessageSearch, setShowMessageSearch] = useState(false);
    const [showSettings, setShowSettings] = useState(false);
    const [showNotificationSettings, setShowNotificationSettings] = useState(false);
    const [isMobile, setIsMobile] = useState(false);
    const [showMobileChat, setShowMobileChat] = useState(false);

    // Hook global pour les notifications sonores sur toutes les conversations
    useGlobalNotifications({
        currentUser: user,
        conversations: conversations,
        enabled: true, // Activé globalement, les préférences individuelles sont gérées par conversation
        activeConversationId: selectedConversation?.id || null // Éviter les notifications pour la conversation active
    });

    // Abonnements Echo pour les messages en temps réel
    useEffect(() => {
        console.log('🔄 Echo useEffect dans Messaging/Index', {
            hasEcho: !!window.Echo,
            conversationsCount: conversations.length,
            conversations: conversations.map(c => c.id)
        });

        if (!window.Echo || !conversations.length) {
            console.log('❌ Echo ou conversations manquants');
            return;
        }

        console.log('🔊 Abonnement Echo dans Messaging/Index');
        const channels: any[] = [];

        conversations.forEach(conversation => {
            try {
                console.log(`🔗 Abonnement: conversation.${conversation.id}`);

                const channel = window.Echo.private(`conversation.${conversation.id}`)
                    .listen('.message.sent', (event: any) => {
                        console.log('📨 Message reçu dans Index:', conversation.id, event);

                        // Ajouter le message à la liste actuelle
                        if (selectedConversation?.id === conversation.id) {
                            setCurrentMessages(prev => [...prev, event.message]);
                        }
                    });

                channels.push(channel);
                console.log(`✅ Abonné: conversation.${conversation.id}`);
            } catch (error) {
                console.error(`❌ Erreur abonnement ${conversation.id}:`, error);
            }
        });

        return () => {
            console.log('🧹 Nettoyage abonnements Index');
            channels.forEach(channel => {
                try {
                    if (channel && typeof channel.stopListening === 'function') {
                        channel.stopListening();
                    }
                } catch (error) {
                    console.error('❌ Erreur nettoyage:', error);
                }
            });
        };
    }, [conversations, selectedConversation?.id]);

    useEffect(() => {
        const checkMobile = () => {
            setIsMobile(window.innerWidth < 768);
        };

        checkMobile();
        window.addEventListener('resize', checkMobile);
        return () => window.removeEventListener('resize', checkMobile);
    }, []);

    // Effet pour gérer la sélection automatique d'une conversation
    useEffect(() => {
        if (initialSelectedConversation) {
            setSelectedConversation(initialSelectedConversation);
            // Si on est sur mobile et qu'une conversation est sélectionnée, afficher directement le chat
            if (isMobile) {
                setShowMobileChat(true);
            }
        }
    }, [initialSelectedConversation, isMobile]);

    const handleConversationSelect = (conversation: Conversation) => {
        console.log('Selecting conversation:', conversation);
        console.log('Type:', conversation.type, 'Other participant ID:', conversation.other_participant_id);

        setSelectedConversation(conversation);
        if (isMobile) {
            setShowMobileChat(true);
        }

        // Mettre à jour l'URL selon le type de conversation
        if (conversation.type === 'group') {
            console.log('Navigating to group:', `/messagerie?selectedGroupId=${conversation.id}`);
            router.get(`/messagerie?selectedGroupId=${conversation.id}`, {}, {
                preserveState: false,
                preserveScroll: false,
                replace: true
            });
        } else if (conversation.other_participant_id) {
            console.log('Navigating to private:', `/messagerie?selectedContactId=${conversation.other_participant_id}`);
            router.get(`/messagerie?selectedContactId=${conversation.other_participant_id}`, {}, {
                preserveState: false,
                preserveScroll: false,
                replace: true
            });
        }
    };

    const handleBackToList = () => {
        setShowMobileChat(false);
        setSelectedConversation(null);
    };

    const handleMessageSent = (newMessage: Message) => {
        setCurrentMessages(prevMessages => [...prevMessages, newMessage]);
    };

    // Mettre à jour les messages quand la conversation change
    useEffect(() => {
        setCurrentMessages(messages);
    }, [messages, selectedConversation?.id]);

    // Gérer l'ajout du nouveau message depuis la session
    useEffect(() => {
        if (newMessage && selectedConversation) {
            const messageToAdd: Message = {
                id: newMessage.id,
                content: newMessage.content,
                type: 'text',
                created_at: newMessage.created_at,
                is_edited: false,
                user: newMessage.user,
                reactions: []
            };

            // Vérifier si le message n'existe pas déjà
            setCurrentMessages(prevMessages => {
                const exists = prevMessages.some(msg => msg.id === newMessage.id);
                if (!exists) {
                    return [...prevMessages, messageToAdd];
                }
                return prevMessages;
            });
        }
    }, [newMessage, selectedConversation?.id]);

    return (
        <KwdDashboardLayout title={t('messaging')}>
            <Head title={t('messaging')} />

            <div className="h-screen bg-gray-50 flex">
                {/* Sidebar - Liste des conversations */}
                <div className={`
                    ${isMobile ? (showMobileChat ? 'hidden' : 'w-full') : 'w-80'}
                    bg-white border-r border-gray-200 flex flex-col min-h-full
                `}>
                    {/* Header de la sidebar */}
                    <div className="p-4 border-b border-gray-200">
                        <div className="flex items-center justify-between mb-4">
                            <h1 className="text-xl font-semibold text-gray-900">Messages</h1>
                            <div className="flex items-center space-x-2">
                                <button
                                    onClick={() => {
                                        console.log('Navigating to groups/create');
                                        try {
                                            router.get('/groups/create');
                                        } catch (error) {
                                            console.error('Router navigation failed:', error);
                                            // Fallback to window.location
                                            window.location.href = '/groups/create';
                                        }
                                    }}
                                    className="p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors"
                                    title="Créer un groupe"
                                >
                                    <Users className="w-5 h-5" />
                                </button>
                                <button
                                    onClick={() => setShowMessageSearch(true)}
                                    className="p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors"
                                    title="Rechercher dans les messages"
                                >
                                    <Search className="w-5 h-5" />
                                </button>
                                <button
                                    onClick={() => setShowSettings(true)}
                                    className="p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors"
                                    title="Paramètres"
                                >
                                    <Settings className="w-5 h-5" />
                                </button>
                                <button
                                    onClick={() => setShowUserSearch(true)}
                                    className="p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors"
                                    title="Nouvelle conversation"
                                >
                                    <MoreVertical className="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        {/* Navigation Messages / Groupes */}
                        <div className="flex mb-4 bg-gray-100 rounded-lg p-1">
                            <button
                                onClick={() => router.get('/messagerie')}
                                className={`flex-1 px-3 py-2 text-sm font-medium rounded-md transition-colors ${
                                    route().current('messaging.index')
                                        ? 'bg-white text-orange-600 shadow-sm'
                                        : 'text-gray-600 hover:text-gray-900'
                                }`}
                            >
                                💬 Messages
                            </button>
                            <button
                                onClick={() => router.get('/groups')}
                                className={`flex-1 px-3 py-2 text-sm font-medium rounded-md transition-colors ${
                                    route().current('groups.*')
                                        ? 'bg-white text-orange-600 shadow-sm'
                                        : 'text-gray-600 hover:text-gray-900'
                                }`}
                            >
                                👥 Groupes
                            </button>
                        </div>

                        {/* Barre de recherche */}
                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                            <input
                                type="text"
                                placeholder="Rechercher un contact..."
                                className="w-full pl-10 pr-4 py-2 bg-gray-100 border-0 rounded-lg focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all"
                            />
                        </div>
                    </div>

                    {/* Section Conversations */}
                    <div className="flex-1 overflow-hidden">
                        <div className="p-4">
                            <div className="flex items-center justify-between mb-3">
                                <h2 className="text-sm font-medium text-gray-500 uppercase tracking-wide">
                                    Conversations
                                </h2>
                                <span className="text-xs text-gray-400">
                                    {conversations.length}
                                </span>
                            </div>
                        </div>

                        <ConversationList
                            conversations={conversations}
                            selectedConversation={selectedConversation}
                            onConversationSelect={handleConversationSelect}
                        />
                    </div>

                    {/* Section Groupes */}
                    <div className="p-4 border-t border-gray-100">
                        <div className="flex items-center justify-between">
                            <h2 className="text-sm font-medium text-gray-500 uppercase tracking-wide">
                                Groupes
                            </h2>
                            <button className="text-xs text-orange-600 hover:text-orange-700 font-medium">
                                Créer un groupe
                            </button>
                        </div>
                    </div>
                </div>

                {/* Zone de chat principale */}
                <div className={`
                    flex-1 flex flex-col
                    ${isMobile ? (showMobileChat ? 'block' : 'hidden') : 'block'}
                `}>
                    {selectedConversation ? (
                        <ChatArea
                            conversation={selectedConversation}
                            messages={currentMessages}
                            user={user}
                            onBack={isMobile ? handleBackToList : undefined}
                            onMessageSent={handleMessageSent}
                            notificationsEnabled={notificationsEnabled}
                        />
                    ) : (
                        <div className="flex-1 flex items-center justify-center bg-gray-50">
                            <div className="text-center max-w-md mx-auto px-6">
                                <div className="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <svg className="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <h3 className="text-xl font-semibold text-gray-900 mb-3">
                                    Aucune suggestion
                                </h3>
                                <p className="text-gray-500 mb-6 leading-relaxed">
                                    Vous n'avez pas encore reçu de suggestions de relations familiales.
                                    Explorez les réseaux pour découvrir de nouveaux utilisateurs.
                                </p>
                                <button
                                    onClick={() => window.location.href = '/family-relations/suggestions'}
                                    className="inline-flex items-center px-6 py-3 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-all duration-200 transform hover:scale-105 font-medium"
                                >
                                    <Plus className="w-5 h-5 mr-2" />
                                    Explorer les Réseaux
                                </button>
                            </div>
                        </div>
                    )}
                </div>

                {/* Modals */}
                {showUserSearch && (
                    <UserSearch
                        onClose={() => setShowUserSearch(false)}
                        onConversationCreated={(conversationId) => {
                            setShowUserSearch(false);
                            // Recharger les conversations ou naviguer vers la nouvelle conversation
                            window.location.reload();
                        }}
                    />
                )}

                {showMessageSearch && (
                    <MessageSearch
                        isOpen={showMessageSearch}
                        onClose={() => setShowMessageSearch(false)}
                        onMessageSelect={(conversationId, messageId) => {
                            setShowMessageSearch(false);
                            // Naviguer vers la conversation et le message
                            const conversation = conversations.find(c => c.id === conversationId);
                            if (conversation) {
                                setSelectedConversation(conversation);
                                if (isMobile) {
                                    setShowMobileChat(true);
                                }
                            }
                        }}
                    />
                )}

                {showSettings && (
                    <MessageSettings
                        isOpen={showSettings}
                        onClose={() => setShowSettings(false)}
                    />
                )}


            </div>
        </KwdDashboardLayout>
    );
}
