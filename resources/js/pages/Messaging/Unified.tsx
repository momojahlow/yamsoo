import React, { useState, useEffect, useRef } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import KwdDashboardLayout from '@/layouts/KwdDashboardLayout';
import { Users, Send, Paperclip, Phone, Video, MoreVertical, ArrowLeft, Plus } from 'lucide-react';

interface User {
    id: number;
    name: string;
    avatar?: string;
}

interface Message {
    id: number;
    content: string;
    type: 'text' | 'image' | 'file' | 'audio' | 'video';
    file_url?: string;
    file_name?: string;
    file_size?: number;
    is_own: boolean;
    user: User;
    reply_to?: {
        id: number;
        content: string;
        user_name: string;
    };
    is_edited: boolean;
    edited_at?: string;
    created_at: string;
}

interface Conversation {
    id: number;
    type: 'private' | 'group';
    name: string;
    description?: string;
    avatar?: string;
    is_group: boolean;
    participants_count?: number;
    is_admin?: boolean;
    other_participant_id?: number;
    last_message?: {
        id: number;
        content: string;
        type: string;
        user_name: string;
        created_at: string;
        is_own: boolean;
    };
    unread_count: number;
    last_message_at: string;
    created_at: string;
}

interface UnifiedMessagingProps {
    conversations: Conversation[];
    selectedConversation?: Conversation;
    messages: Message[];
    user: User;
}

export default function UnifiedMessaging({ conversations, selectedConversation, messages, user }: UnifiedMessagingProps) {
    const [showMobileChat, setShowMobileChat] = useState(!!selectedConversation);
    const messagesEndRef = useRef<HTMLDivElement>(null);
    
    const { data, setData, post, processing, reset } = useForm({
        conversation_id: selectedConversation?.id || '',
        content: '',
        file: null as File | null,
        reply_to_id: null as number | null,
    });

    // Scroll automatique vers le bas
    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    // Mettre à jour l'ID de conversation quand elle change
    useEffect(() => {
        if (selectedConversation) {
            setData('conversation_id', selectedConversation.id);
        }
    }, [selectedConversation]);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    const handleSendMessage = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (!data.content.trim() || processing || !selectedConversation) return;

        post('/messagerie/send', {
            onSuccess: () => {
                reset('content');
            },
            onError: (errors) => {
                console.error('Erreur envoi message:', errors);
            }
        });
    };

    const selectConversation = (conversation: Conversation) => {
        const params: any = {};
        
        if (conversation.is_group) {
            params.selectedGroupId = conversation.id;
        } else {
            params.selectedContactId = conversation.other_participant_id;
        }

        router.get('/messagerie', params, {
            preserveState: false,
            preserveScroll: false,
        });
    };

    const formatTime = (dateString: string) => {
        return new Date(dateString).toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const getConversationAvatar = (conversation: Conversation) => {
        if (conversation.avatar) {
            return conversation.avatar;
        }
        
        if (conversation.is_group) {
            return '/images/group-avatar.png';
        }
        
        return '/images/default-avatar.png';
    };

    return (
        <KwdDashboardLayout>
            <Head title="Messages" />
            
            <div className="flex h-[calc(100vh-120px)] bg-gray-100 rounded-lg overflow-hidden">
                {/* Liste des conversations */}
                <div className={`${showMobileChat ? 'hidden md:flex' : 'flex'} w-full md:w-80 bg-white border-r border-gray-200 flex-col`}>
                    {/* Header */}
                    <div className="p-4 border-b border-gray-200 bg-gradient-to-r from-orange-500 to-red-500">
                        <div className="flex items-center justify-between">
                            <h1 className="text-xl font-semibold text-white">Messages</h1>
                            <button
                                onClick={() => router.get('/groups/create')}
                                className="p-2 text-white hover:bg-white/20 rounded-lg transition-colors"
                                title="Créer un groupe"
                            >
                                <Plus className="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    {/* Liste */}
                    <div className="flex-1 overflow-y-auto">
                        {conversations.length === 0 ? (
                            <div className="p-8 text-center text-gray-500">
                                <Users className="w-12 h-12 mx-auto mb-4 text-gray-400" />
                                <p className="font-medium">Aucune conversation</p>
                                <p className="text-sm">Commencez une nouvelle conversation</p>
                            </div>
                        ) : (
                            conversations.map(conversation => (
                                <div
                                    key={conversation.id}
                                    onClick={() => selectConversation(conversation)}
                                    className={`p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors ${
                                        selectedConversation?.id === conversation.id ? 'bg-orange-50 border-l-4 border-l-orange-500' : ''
                                    }`}
                                >
                                    <div className="flex items-center space-x-3">
                                        <div className="relative">
                                            <img
                                                src={getConversationAvatar(conversation)}
                                                alt={conversation.name}
                                                className="w-12 h-12 rounded-full object-cover"
                                                onError={(e) => {
                                                    e.currentTarget.src = '/images/default-avatar.png';
                                                }}
                                            />
                                            {conversation.is_group && (
                                                <div className="absolute -bottom-1 -right-1 bg-orange-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                                    <Users className="w-3 h-3" />
                                                </div>
                                            )}
                                        </div>
                                        
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center justify-between">
                                                <h3 className="text-sm font-medium text-gray-900 truncate">
                                                    {conversation.name}
                                                    {conversation.is_group && conversation.participants_count && (
                                                        <span className="text-gray-500 text-xs ml-1">
                                                            ({conversation.participants_count})
                                                        </span>
                                                    )}
                                                </h3>
                                                <span className="text-xs text-gray-500">
                                                    {formatTime(conversation.last_message_at)}
                                                </span>
                                            </div>
                                            
                                            <div className="flex items-center justify-between mt-1">
                                                <p className="text-sm text-gray-600 truncate">
                                                    {conversation.last_message && (
                                                        <>
                                                            {conversation.is_group && !conversation.last_message.is_own && (
                                                                <span className="font-medium text-orange-600">
                                                                    {conversation.last_message.user_name}:{' '}
                                                                </span>
                                                            )}
                                                            {conversation.last_message.content}
                                                        </>
                                                    )}
                                                </p>
                                                
                                                {conversation.unread_count > 0 && (
                                                    <span className="bg-orange-500 text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center">
                                                        {conversation.unread_count}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                </div>

                {/* Zone de chat */}
                <div className={`${showMobileChat ? 'flex' : 'hidden md:flex'} flex-1 flex-col`}>
                    {selectedConversation ? (
                        <>
                            {/* Header du chat */}
                            <div className="bg-white border-b border-gray-200 p-4">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center space-x-3">
                                        <button
                                            onClick={() => setShowMobileChat(false)}
                                            className="md:hidden p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100"
                                        >
                                            <ArrowLeft className="w-5 h-5" />
                                        </button>
                                        
                                        <img
                                            src={getConversationAvatar(selectedConversation)}
                                            alt={selectedConversation.name}
                                            className="w-10 h-10 rounded-full object-cover"
                                            onError={(e) => {
                                                e.currentTarget.src = '/images/default-avatar.png';
                                            }}
                                        />
                                        
                                        <div>
                                            <h2 className="text-lg font-semibold text-gray-900">
                                                {selectedConversation.name}
                                            </h2>
                                            {selectedConversation.is_group && (
                                                <p className="text-sm text-gray-500">
                                                    {selectedConversation.participants_count} participants
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                    
                                    <div className="flex items-center space-x-2">
                                        <button className="p-2 text-gray-500 hover:text-orange-600 rounded-lg hover:bg-orange-50 transition-colors">
                                            <Phone className="w-5 h-5" />
                                        </button>
                                        <button className="p-2 text-gray-500 hover:text-orange-600 rounded-lg hover:bg-orange-50 transition-colors">
                                            <Video className="w-5 h-5" />
                                        </button>
                                        <button className="p-2 text-gray-500 hover:text-orange-600 rounded-lg hover:bg-orange-50 transition-colors">
                                            <MoreVertical className="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {/* Messages */}
                            <div className="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
                                {messages.length === 0 ? (
                                    <div className="flex items-center justify-center h-full">
                                        <div className="text-center text-gray-500">
                                            <p className="text-lg font-medium">Aucun message</p>
                                            <p className="text-sm">Commencez la conversation !</p>
                                        </div>
                                    </div>
                                ) : (
                                    messages.map(message => (
                                        <div
                                            key={message.id}
                                            className={`flex ${message.is_own ? 'justify-end' : 'justify-start'}`}
                                        >
                                            <div className={`max-w-xs lg:max-w-md px-4 py-2 rounded-lg shadow-sm ${
                                                message.is_own
                                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white'
                                                    : 'bg-white text-gray-900 border border-gray-200'
                                            }`}>
                                                {selectedConversation.is_group && !message.is_own && (
                                                    <p className="text-xs font-medium mb-1 opacity-75">
                                                        {message.user.name}
                                                    </p>
                                                )}
                                                
                                                <p className="text-sm">{message.content}</p>
                                                
                                                <p className={`text-xs mt-1 ${
                                                    message.is_own ? 'text-orange-100' : 'text-gray-500'
                                                }`}>
                                                    {formatTime(message.created_at)}
                                                </p>
                                            </div>
                                        </div>
                                    ))
                                )}
                                <div ref={messagesEndRef} />
                            </div>

                            {/* Input de message */}
                            <div className="bg-white border-t border-gray-200 p-4">
                                <form onSubmit={handleSendMessage} className="flex items-center space-x-2">
                                    <button
                                        type="button"
                                        className="p-2 text-gray-500 hover:text-orange-600 rounded-lg hover:bg-orange-50 transition-colors"
                                    >
                                        <Paperclip className="w-5 h-5" />
                                    </button>
                                    
                                    <div className="flex-1 relative">
                                        <input
                                            type="text"
                                            value={data.content}
                                            onChange={(e) => setData('content', e.target.value)}
                                            placeholder="Écrivez votre message..."
                                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                            disabled={processing}
                                        />
                                    </div>
                                    
                                    <button
                                        type="submit"
                                        disabled={!data.content.trim() || processing}
                                        className="p-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                                    >
                                        <Send className="w-5 h-5" />
                                    </button>
                                </form>
                            </div>
                        </>
                    ) : (
                        <div className="flex-1 flex items-center justify-center bg-gray-50">
                            <div className="text-center">
                                <div className="w-16 h-16 bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <Users className="w-8 h-8 text-white" />
                                </div>
                                <h3 className="text-lg font-medium text-gray-900 mb-2">
                                    Sélectionnez une conversation
                                </h3>
                                <p className="text-gray-500 mb-4">
                                    Choisissez une conversation pour commencer à discuter
                                </p>
                                <button
                                    onClick={() => router.get('/groups/create')}
                                    className="px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 transition-all"
                                >
                                    Créer un groupe
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </KwdDashboardLayout>
    );
}
