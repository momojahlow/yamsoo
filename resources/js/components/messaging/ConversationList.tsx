import React from 'react';
import { formatDistanceToNow } from 'date-fns';
import { fr } from 'date-fns/locale';

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

interface ConversationListProps {
    conversations: Conversation[];
    selectedConversation: Conversation | null;
    onConversationSelect: (conversation: Conversation) => void;
}

export default function ConversationList({ 
    conversations, 
    selectedConversation, 
    onConversationSelect 
}: ConversationListProps) {
    const getInitials = (name: string) => {
        return name
            .split(' ')
            .map(word => word.charAt(0))
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };

    const formatTime = (dateString: string) => {
        const date = new Date(dateString);
        const now = new Date();
        const diffInHours = (now.getTime() - date.getTime()) / (1000 * 60 * 60);

        if (diffInHours < 24) {
            return date.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        } else {
            return formatDistanceToNow(date, { 
                addSuffix: false, 
                locale: fr 
            });
        }
    };

    const truncateMessage = (content: string, maxLength: number = 50) => {
        if (content.length <= maxLength) return content;
        return content.substring(0, maxLength) + '...';
    };

    return (
        <div className="flex-1 overflow-y-auto">
            {conversations.length === 0 ? (
                <div className="p-4 text-center text-gray-500">
                    <p>Aucune conversation</p>
                    <p className="text-sm mt-1">Commencez une nouvelle conversation</p>
                </div>
            ) : (
                <div className="space-y-1">
                    {conversations.map((conversation) => (
                        <div
                            key={conversation.id}
                            onClick={() => onConversationSelect(conversation)}
                            className={`
                                relative flex items-center p-3 mx-2 rounded-lg cursor-pointer transition-all duration-200
                                ${selectedConversation?.id === conversation.id
                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg'
                                    : 'hover:bg-gray-50 text-gray-900'
                                }
                            `}
                        >
                            {/* Avatar */}
                            <div className="relative flex-shrink-0">
                                {conversation.avatar ? (
                                    <img
                                        src={conversation.avatar}
                                        alt={conversation.name}
                                        className="w-12 h-12 rounded-full object-cover"
                                    />
                                ) : (
                                    <div className={`
                                        w-12 h-12 rounded-full flex items-center justify-center font-medium text-sm
                                        ${selectedConversation?.id === conversation.id
                                            ? 'bg-white/20 text-white'
                                            : 'bg-gradient-to-br from-orange-100 to-red-100 text-orange-600'
                                        }
                                    `}>
                                        {getInitials(conversation.name)}
                                    </div>
                                )}
                                
                                {/* Indicateur en ligne */}
                                {conversation.is_online && (
                                    <div className="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-green-500 border-2 border-white rounded-full"></div>
                                )}
                            </div>

                            {/* Contenu de la conversation */}
                            <div className="flex-1 min-w-0 ml-3">
                                <div className="flex items-center justify-between">
                                    <h3 className={`
                                        font-medium truncate
                                        ${selectedConversation?.id === conversation.id
                                            ? 'text-white'
                                            : 'text-gray-900'
                                        }
                                    `}>
                                        {conversation.name}
                                    </h3>
                                    
                                    {conversation.last_message && (
                                        <span className={`
                                            text-xs flex-shrink-0 ml-2
                                            ${selectedConversation?.id === conversation.id
                                                ? 'text-white/80'
                                                : 'text-gray-500'
                                            }
                                        `}>
                                            {formatTime(conversation.last_message.created_at)}
                                        </span>
                                    )}
                                </div>

                                <div className="flex items-center justify-between mt-1">
                                    {conversation.last_message ? (
                                        <p className={`
                                            text-sm truncate
                                            ${selectedConversation?.id === conversation.id
                                                ? 'text-white/80'
                                                : 'text-gray-600'
                                            }
                                        `}>
                                            {conversation.last_message.is_own && (
                                                <span className="mr-1">Vous:</span>
                                            )}
                                            {truncateMessage(conversation.last_message.content)}
                                        </p>
                                    ) : (
                                        <p className={`
                                            text-sm italic
                                            ${selectedConversation?.id === conversation.id
                                                ? 'text-white/60'
                                                : 'text-gray-400'
                                            }
                                        `}>
                                            Aucun message
                                        </p>
                                    )}

                                    {/* Badge de messages non lus */}
                                    {conversation.unread_count > 0 && (
                                        <div className={`
                                            flex-shrink-0 ml-2 px-2 py-1 rounded-full text-xs font-medium
                                            ${selectedConversation?.id === conversation.id
                                                ? 'bg-white/20 text-white'
                                                : 'bg-orange-500 text-white'
                                            }
                                        `}>
                                            {conversation.unread_count > 99 ? '99+' : conversation.unread_count}
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
