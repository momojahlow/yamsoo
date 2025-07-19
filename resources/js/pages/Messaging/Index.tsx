import React, { useState, useEffect, useRef } from 'react';
import { Head } from '@inertiajs/react';
import { Search, Phone, Video, MoreVertical, Paperclip, Smile, Send, ArrowLeft, Settings, BarChart3, Users } from 'lucide-react';
import AuthenticatedLayout from '@/layouts/app-layout';
import ConversationList from '@/components/messaging/ConversationList';
import ChatArea from '@/components/messaging/ChatArea';
import UserSearch from '@/components/messaging/UserSearch';
import MessageSearch from '@/components/messaging/MessageSearch';
import MessageSettings from '@/components/messaging/MessageSettings';
import MessageStats from '@/components/messaging/MessageStats';
import FamilySuggestions from '@/components/messaging/FamilySuggestions';

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

interface MessagingProps {
    conversations: Conversation[];
    user: User;
}

export default function Messaging({ conversations, user }: MessagingProps) {
    const [selectedConversation, setSelectedConversation] = useState<Conversation | null>(null);
    const [showUserSearch, setShowUserSearch] = useState(false);
    const [showMessageSearch, setShowMessageSearch] = useState(false);
    const [showSettings, setShowSettings] = useState(false);
    const [showStats, setShowStats] = useState(false);
    const [showFamilySuggestions, setShowFamilySuggestions] = useState(false);
    const [isMobile, setIsMobile] = useState(false);
    const [showMobileChat, setShowMobileChat] = useState(false);

    useEffect(() => {
        const checkMobile = () => {
            setIsMobile(window.innerWidth < 768);
        };

        checkMobile();
        window.addEventListener('resize', checkMobile);
        return () => window.removeEventListener('resize', checkMobile);
    }, []);

    const handleConversationSelect = (conversation: Conversation) => {
        setSelectedConversation(conversation);
        if (isMobile) {
            setShowMobileChat(true);
        }
    };

    const handleBackToList = () => {
        setShowMobileChat(false);
        setSelectedConversation(null);
    };

    return (
        <AuthenticatedLayout>
            <Head title="Messagerie - Yamsoo" />

            <div className="h-screen bg-gray-50 flex">
                {/* Sidebar - Liste des conversations */}
                <div className={`
                    ${isMobile ? (showMobileChat ? 'hidden' : 'w-full') : 'w-80'}
                    bg-white border-r border-gray-200 flex flex-col
                `}>
                    {/* Header de la sidebar */}
                    <div className="p-4 border-b border-gray-200">
                        <div className="flex items-center justify-between mb-4">
                            <h1 className="text-xl font-semibold text-gray-900">Messages</h1>
                            <div className="flex items-center space-x-2">
                                <button
                                    onClick={() => setShowMessageSearch(true)}
                                    className="p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors"
                                    title="Rechercher dans les messages"
                                >
                                    <Search className="w-5 h-5" />
                                </button>
                                <button
                                    onClick={() => setShowStats(true)}
                                    className="p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors"
                                    title="Statistiques"
                                >
                                    <BarChart3 className="w-5 h-5" />
                                </button>
                                <button
                                    onClick={() => setShowSettings(true)}
                                    className="p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors"
                                    title="Paramètres"
                                >
                                    <Settings className="w-5 h-5" />
                                </button>
                                <button
                                    onClick={() => setShowFamilySuggestions(true)}
                                    className="p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors"
                                    title="Suggestions familiales"
                                >
                                    <Users className="w-5 h-5" />
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
                            user={user}
                            onBack={isMobile ? handleBackToList : undefined}
                        />
                    ) : (
                        <div className="flex-1 flex items-center justify-center bg-gray-50">
                            <div className="text-center">
                                <div className="w-24 h-24 bg-gradient-to-br from-orange-100 to-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <Search className="w-12 h-12 text-orange-500" />
                                </div>
                                <h3 className="text-lg font-medium text-gray-900 mb-2">
                                    Sélectionnez une conversation
                                </h3>
                                <p className="text-gray-500 max-w-sm">
                                    Choisissez une conversation dans la liste ou créez-en une nouvelle
                                    pour commencer à échanger avec votre famille.
                                </p>
                                <button
                                    onClick={() => setShowUserSearch(true)}
                                    className="mt-4 px-6 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 transition-all duration-200 transform hover:scale-105"
                                >
                                    Nouvelle conversation
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

                {showStats && (
                    <MessageStats
                        isOpen={showStats}
                        onClose={() => setShowStats(false)}
                    />
                )}

                {showFamilySuggestions && (
                    <FamilySuggestions
                        isOpen={showFamilySuggestions}
                        onClose={() => setShowFamilySuggestions(false)}
                        onConversationCreated={(conversationId) => {
                            setShowFamilySuggestions(false);
                            // Recharger les conversations
                            window.location.reload();
                        }}
                    />
                )}
            </div>
        </AuthenticatedLayout>
    );
}
