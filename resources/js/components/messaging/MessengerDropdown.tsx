import React, { useState, useEffect, useRef } from 'react';
import { Link } from '@inertiajs/react';
import { MessageSquare, Users, Clock, ChevronRight } from 'lucide-react';
import { Badge } from '@/components/ui/badge';

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

interface MessengerDropdownProps {
    conversations: ConversationSummary[];
    totalUnreadCount: number;
    currentUser: User;
    isOpen: boolean;
    onToggle: () => void;
    onClose: () => void;
    onConversationClick?: (conversationId: number) => void;
}

export default function MessengerDropdown({
    conversations,
    totalUnreadCount,
    currentUser,
    isOpen,
    onToggle,
    onClose,
    onConversationClick
}: MessengerDropdownProps) {
    const dropdownRef = useRef<HTMLDivElement>(null);

    // Fermer le dropdown quand on clique à l'extérieur
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
                onClose();
            }
        };

        if (isOpen) {
            document.addEventListener('mousedown', handleClickOutside);
        }

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, [isOpen, onClose]);

    const getInitials = (name: string) => {
        return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
    };

    const formatTime = (dateString: string) => {
        const date = new Date(dateString);
        const now = new Date();
        const diffInHours = (now.getTime() - date.getTime()) / (1000 * 60 * 60);

        if (diffInHours < 1) {
            const minutes = Math.floor(diffInHours * 60);
            return minutes <= 1 ? 'À l\'instant' : `${minutes}m`;
        } else if (diffInHours < 24) {
            return `${Math.floor(diffInHours)}h`;
        } else {
            const days = Math.floor(diffInHours / 24);
            return `${days}j`;
        }
    };

    const truncateMessage = (content: string, maxLength: number = 50) => {
        if (content.length <= maxLength) return content;
        return content.substring(0, maxLength) + '...';
    };

    const getConversationUrl = (conversation: ConversationSummary) => {
        if (conversation.type === 'group') {
            return `/messagerie?selectedGroupId=${conversation.id}`;
        } else {
            return `/messagerie?selectedContactId=${conversation.other_participant?.id || conversation.id}`;
        }
    };

    return (
        <div className="relative" ref={dropdownRef}>
            {/* Icône de messagerie avec badge */}
            <button
                onClick={onToggle}
                className="relative p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors"
                title="Messages"
            >
                <MessageSquare className="w-6 h-6" />
                {totalUnreadCount > 0 && (
                    <Badge
                        variant="destructive"
                        className="absolute -top-1 -right-1 h-5 w-5 text-xs p-0 flex items-center justify-center animate-pulse"
                    >
                        {totalUnreadCount > 99 ? '99+' : totalUnreadCount}
                    </Badge>
                )}
            </button>

            {/* Dropdown */}
            {isOpen && (
                <div className="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50 max-h-96 overflow-hidden">
                    {/* Header */}
                    <div className="p-4 border-b border-gray-200">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">Messages</h3>
                            <Link
                                href="/messagerie"
                                className="text-sm text-orange-600 hover:text-orange-700 font-medium"
                                onClick={onClose}
                            >
                                Voir tout
                            </Link>
                        </div>
                        {totalUnreadCount > 0 && (
                            <p className="text-sm text-gray-500 mt-1">
                                {totalUnreadCount} message{totalUnreadCount > 1 ? 's' : ''} non lu{totalUnreadCount > 1 ? 's' : ''}
                            </p>
                        )}
                    </div>

                    {/* Liste des conversations */}
                    <div className="max-h-80 overflow-y-auto">
                        {conversations.length === 0 ? (
                            <div className="p-6 text-center">
                                <MessageSquare className="w-12 h-12 text-gray-300 mx-auto mb-3" />
                                <p className="text-gray-500 text-sm">Aucune conversation</p>
                                <Link
                                    href="/messagerie"
                                    className="text-orange-600 hover:text-orange-700 text-sm font-medium mt-2 inline-block"
                                    onClick={onClose}
                                >
                                    Commencer une conversation
                                </Link>
                            </div>
                        ) : (
                            conversations.slice(0, 8).map((conversation) => (
                                <Link
                                    key={conversation.id}
                                    href={getConversationUrl(conversation)}
                                    onClick={() => {
                                        onConversationClick?.(conversation.id);
                                        onClose();
                                    }}
                                    className="block p-3 hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-b-0"
                                >
                                    <div className="flex items-center space-x-3">
                                        {/* Avatar */}
                                        <div className="relative flex-shrink-0">
                                            {conversation.avatar ? (
                                                <img
                                                    src={conversation.avatar}
                                                    alt={conversation.name}
                                                    className="w-12 h-12 rounded-full object-cover"
                                                />
                                            ) : (
                                                <div className={`w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold ${
                                                    conversation.type === 'group'
                                                        ? 'bg-gradient-to-br from-blue-400 to-blue-600'
                                                        : 'bg-gradient-to-br from-orange-400 to-red-500'
                                                }`}>
                                                    {conversation.type === 'group' ? (
                                                        <Users className="w-6 h-6" />
                                                    ) : (
                                                        getInitials(conversation.name)
                                                    )}
                                                </div>
                                            )}

                                            {/* Indicateur en ligne pour les conversations privées */}
                                            {conversation.type === 'private' && conversation.is_online && (
                                                <div className="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></div>
                                            )}
                                        </div>

                                        {/* Contenu */}
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center justify-between">
                                                <h4 className={`font-medium truncate ${
                                                    conversation.unread_count > 0 ? 'text-gray-900' : 'text-gray-700'
                                                }`}>
                                                    {conversation.name}
                                                    {conversation.type === 'group' && conversation.participants_count && (
                                                        <span className="text-xs text-gray-500 ml-1">
                                                            ({conversation.participants_count})
                                                        </span>
                                                    )}
                                                </h4>

                                                <div className="flex items-center space-x-2 flex-shrink-0">
                                                    {conversation.last_message && (
                                                        <span className="text-xs text-gray-500">
                                                            {formatTime(conversation.last_message.created_at)}
                                                        </span>
                                                    )}

                                                    {conversation.unread_count > 0 && (
                                                        <Badge
                                                            variant="destructive"
                                                            className="h-5 w-5 text-xs p-0 flex items-center justify-center"
                                                        >
                                                            {conversation.unread_count > 99 ? '99+' : conversation.unread_count}
                                                        </Badge>
                                                    )}
                                                </div>
                                            </div>

                                            {conversation.last_message && (
                                                <p className={`text-sm mt-1 truncate ${
                                                    conversation.unread_count > 0 ? 'text-gray-900 font-medium' : 'text-gray-500'
                                                }`}>
                                                    {conversation.last_message.is_own ? 'Vous: ' : `${conversation.last_message.user_name}: `}
                                                    {truncateMessage(conversation.last_message.content)}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </Link>
                            ))
                        )}
                    </div>

                    {/* Footer */}
                    {conversations.length > 8 && (
                        <div className="p-3 border-t border-gray-200 bg-gray-50">
                            <Link
                                href="/messagerie"
                                className="flex items-center justify-center text-sm text-orange-600 hover:text-orange-700 font-medium"
                                onClick={onClose}
                            >
                                Voir toutes les conversations
                                <ChevronRight className="w-4 h-4 ml-1" />
                            </Link>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
