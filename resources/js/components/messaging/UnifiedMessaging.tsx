import React, { useState, useEffect, useRef } from 'react';
import axios from 'axios';
import { Users, Send, Paperclip, Smile, Phone, Video, MoreVertical, ArrowLeft } from 'lucide-react';

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
    currentUser: User;
    initialConversations?: Conversation[];
}

export default function UnifiedMessaging({ currentUser, initialConversations = [] }: UnifiedMessagingProps) {
    const [conversations, setConversations] = useState<Conversation[]>(initialConversations);
    const [selectedConversation, setSelectedConversation] = useState<Conversation | null>(null);
    const [messages, setMessages] = useState<Message[]>([]);
    const [newMessage, setNewMessage] = useState('');
    const [loading, setLoading] = useState(false);
    const [sending, setSending] = useState(false);
    const [showMobileChat, setShowMobileChat] = useState(false);

    const messagesEndRef = useRef<HTMLDivElement>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);

    // Charger les conversations
    useEffect(() => {
        loadConversations();
    }, []);

    // Scroll automatique vers le bas
    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    const loadConversations = async () => {
        try {
            const response = await fetch('/messenger/conversations', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (response.ok) {
                const data = await response.json();
                setConversations(data.conversations);
            }
        } catch (error) {
            console.error('Erreur lors du chargement des conversations:', error);
        }
    };

    const loadMessages = async (conversation: Conversation) => {
        setLoading(true);
        try {
            const response = await fetch(`/messenger/conversations/${conversation.id}/messages`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (response.ok) {
                const data = await response.json();
                setMessages(data.messages.reverse()); // Inverser pour affichage chronologique
            }
        } catch (error) {
            console.error('Erreur lors du chargement des messages:', error);
        } finally {
            setLoading(false);
        }
    };

    const sendMessage = async (e: React.FormEvent) => {
        e.preventDefault();

        if (!newMessage.trim() || !selectedConversation || sending) return;

        setSending(true);
        try {
            const response = await fetch(`/messenger/conversations/${selectedConversation.id}/messages`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    content: newMessage
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            const newMsg = data.message;
            setMessages(prev => [...prev, newMsg]);
            setNewMessage('');

            // Mettre à jour la conversation dans la liste
            setConversations(prev => prev.map(conv =>
                conv.id === selectedConversation.id
                    ? { ...conv, last_message: {
                        id: newMsg.id,
                        content: newMsg.content,
                        type: newMsg.type,
                        user_name: newMsg.user.name,
                        created_at: newMsg.created_at,
                        is_own: true
                    }, last_message_at: newMsg.created_at }
                    : conv
            ));

        } catch (error) {
            console.error('Erreur lors de l\'envoi du message:', error);
        } finally {
            setSending(false);
        }
    };

    const selectConversation = (conversation: Conversation) => {
        setSelectedConversation(conversation);
        setShowMobileChat(true);
        loadMessages(conversation);
    };

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
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

        // Avatar par défaut pour les groupes
        if (conversation.is_group) {
            return '/images/group-avatar.png';
        }

        return '/images/default-avatar.png';
    };

    return (
        <div className="flex h-screen bg-gray-100">
            {/* Liste des conversations */}
            <div className={`${showMobileChat ? 'hidden md:flex' : 'flex'} w-full md:w-80 bg-white border-r border-gray-200 flex-col`}>
                {/* Header */}
                <div className="p-4 border-b border-gray-200">
                    <h1 className="text-xl font-semibold text-gray-900">Messages</h1>
                </div>

                {/* Liste */}
                <div className="flex-1 overflow-y-auto">
                    {conversations.map(conversation => (
                        <div
                            key={conversation.id}
                            onClick={() => selectConversation(conversation)}
                            className={`p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50 ${
                                selectedConversation?.id === conversation.id ? 'bg-blue-50' : ''
                            }`}
                        >
                            <div className="flex items-center space-x-3">
                                <div className="relative">
                                    <img
                                        src={getConversationAvatar(conversation)}
                                        alt={conversation.name}
                                        className="w-12 h-12 rounded-full object-cover"
                                    />
                                    {conversation.is_group && (
                                        <div className="absolute -bottom-1 -right-1 bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
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
                                                        <span className="font-medium">
                                                            {conversation.last_message.user_name}:{' '}
                                                        </span>
                                                    )}
                                                    {conversation.last_message.content}
                                                </>
                                            )}
                                        </p>

                                        {conversation.unread_count > 0 && (
                                            <span className="bg-blue-500 text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center">
                                                {conversation.unread_count}
                                            </span>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
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
                                        className="md:hidden p-2 text-gray-500 hover:text-gray-700"
                                    >
                                        <ArrowLeft className="w-5 h-5" />
                                    </button>

                                    <img
                                        src={getConversationAvatar(selectedConversation)}
                                        alt={selectedConversation.name}
                                        className="w-10 h-10 rounded-full object-cover"
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
                                    <button className="p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100">
                                        <Phone className="w-5 h-5" />
                                    </button>
                                    <button className="p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100">
                                        <Video className="w-5 h-5" />
                                    </button>
                                    <button className="p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100">
                                        <MoreVertical className="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        {/* Messages */}
                        <div className="flex-1 overflow-y-auto p-4 space-y-4">
                            {loading ? (
                                <div className="flex justify-center">
                                    <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                                </div>
                            ) : (
                                messages.map(message => (
                                    <div
                                        key={message.id}
                                        className={`flex ${message.is_own ? 'justify-end' : 'justify-start'}`}
                                    >
                                        <div className={`max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
                                            message.is_own
                                                ? 'bg-blue-500 text-white'
                                                : 'bg-white text-gray-900 border border-gray-200'
                                        }`}>
                                            {selectedConversation.is_group && !message.is_own && (
                                                <p className="text-xs font-medium mb-1 opacity-75">
                                                    {message.user.name}
                                                </p>
                                            )}

                                            <p className="text-sm">{message.content}</p>

                                            <p className={`text-xs mt-1 ${
                                                message.is_own ? 'text-blue-100' : 'text-gray-500'
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
                            <form onSubmit={sendMessage} className="flex items-center space-x-2">
                                <button
                                    type="button"
                                    onClick={() => fileInputRef.current?.click()}
                                    className="p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100"
                                >
                                    <Paperclip className="w-5 h-5" />
                                </button>

                                <input
                                    type="file"
                                    ref={fileInputRef}
                                    className="hidden"
                                    accept="image/*,video/*,audio/*,.pdf,.doc,.docx"
                                />

                                <div className="flex-1 relative">
                                    <input
                                        type="text"
                                        value={newMessage}
                                        onChange={(e) => setNewMessage(e.target.value)}
                                        placeholder="Écrivez votre message..."
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        disabled={sending}
                                    />
                                </div>

                                <button
                                    type="button"
                                    className="p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100"
                                >
                                    <Smile className="w-5 h-5" />
                                </button>

                                <button
                                    type="submit"
                                    disabled={!newMessage.trim() || sending}
                                    className="p-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <Send className="w-5 h-5" />
                                </button>
                            </form>
                        </div>
                    </>
                ) : (
                    <div className="flex-1 flex items-center justify-center bg-gray-50">
                        <div className="text-center">
                            <div className="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                <Users className="w-8 h-8 text-gray-400" />
                            </div>
                            <h3 className="text-lg font-medium text-gray-900 mb-2">
                                Sélectionnez une conversation
                            </h3>
                            <p className="text-gray-500">
                                Choisissez une conversation pour commencer à discuter
                            </p>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
