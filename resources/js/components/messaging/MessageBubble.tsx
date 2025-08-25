import React, { useState } from 'react';
import { formatDistanceToNow } from 'date-fns';
import { fr } from 'date-fns/locale';
import { Reply, MoreVertical, Download, Eye } from 'lucide-react';

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
    file_size?: string;
    created_at: string;
    is_edited: boolean;
    edited_at?: string;
    user: User;
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

interface MessageBubbleProps {
    message: Message;
    isOwn: boolean;
    isGroup?: boolean;
    onReply: () => void;
}

export default function MessageBubble({ message, isOwn, isGroup = false, onReply }: MessageBubbleProps) {
    const [showActions, setShowActions] = useState(false);
    const [showImageModal, setShowImageModal] = useState(false);

    const formatTime = (dateString: string) => {
        const date = new Date(dateString);
        return date.toLocaleTimeString('fr-FR', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    };

    const getInitials = (name: string) => {
        return name
            .split(' ')
            .map(word => word.charAt(0))
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };

    const renderFileContent = () => {
        switch (message.type) {
            case 'image':
                return (
                    <div className="relative">
                        <img
                            src={message.file_url}
                            alt={message.file_name}
                            className="max-w-sm rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                            onClick={() => setShowImageModal(true)}
                        />
                        <button
                            onClick={() => setShowImageModal(true)}
                            className="absolute top-2 right-2 p-1 bg-black/50 text-white rounded-full hover:bg-black/70 transition-colors"
                        >
                            <Eye className="w-4 h-4" />
                        </button>
                    </div>
                );

            case 'video':
                return (
                    <video
                        src={message.file_url}
                        controls
                        className="max-w-sm rounded-lg"
                    >
                        Votre navigateur ne supporte pas la lecture vidéo.
                    </video>
                );

            case 'audio':
                return (
                    <audio
                        src={message.file_url}
                        controls
                        className="max-w-sm"
                    >
                        Votre navigateur ne supporte pas la lecture audio.
                    </audio>
                );

            case 'file':
                return (
                    <div className="flex items-center p-3 bg-gray-100 rounded-lg max-w-sm">
                        <div className="flex-1">
                            <p className="font-medium text-gray-900 truncate">
                                {message.file_name}
                            </p>
                            {message.file_size && (
                                <p className="text-sm text-gray-500">{message.file_size}</p>
                            )}
                        </div>
                        <a
                            href={message.file_url}
                            download={message.file_name}
                            className="ml-3 p-2 text-gray-500 hover:text-orange-600 hover:bg-white rounded-lg transition-colors"
                        >
                            <Download className="w-5 h-5" />
                        </a>
                    </div>
                );

            default:
                return null;
        }
    };

    return (
        <>
            <div
                className={`flex ${isOwn ? 'justify-end' : 'justify-start'} group`}
                onMouseEnter={() => setShowActions(true)}
                onMouseLeave={() => setShowActions(false)}
            >
                <div className={`flex max-w-xs lg:max-w-md ${isOwn ? 'flex-row-reverse' : 'flex-row'}`}>
                    {/* Avatar (seulement pour les groupes) */}
                    {!isOwn && isGroup && (
                        <div className="flex-shrink-0 mr-3">
                            {message.user.avatar ? (
                                <img
                                    src={message.user.avatar}
                                    alt={message.user.name}
                                    className="w-8 h-8 rounded-full object-cover"
                                />
                            ) : (
                                <div className="w-8 h-8 rounded-full bg-gradient-to-br from-orange-100 to-red-100 flex items-center justify-center text-orange-600 font-medium text-xs">
                                    {getInitials(message.user.name)}
                                </div>
                            )}
                        </div>
                    )}

                    {/* Bulle de message */}
                    <div className={`relative ${isOwn ? 'mr-3' : ''}`}>
                        {/* Message de réponse */}
                        {message.reply_to && (
                            <div className={`
                                mb-2 p-2 rounded-lg border-l-4 text-sm
                                ${isOwn 
                                    ? 'bg-orange-50 border-orange-300 text-orange-800' 
                                    : 'bg-gray-100 border-gray-300 text-gray-700'
                                }
                            `}>
                                <p className="font-medium text-xs mb-1">
                                    {message.reply_to.user_name}
                                </p>
                                <p className="truncate">{message.reply_to.content}</p>
                            </div>
                        )}

                        {/* Contenu principal */}
                        <div
                            className={`
                                px-4 py-2 rounded-2xl shadow-sm
                                ${isOwn
                                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white'
                                    : 'bg-white text-gray-900 border border-gray-200'
                                }
                            `}
                        >
                            {/* Nom de l'utilisateur (pour les groupes seulement) */}
                            {!isOwn && isGroup && (
                                <p className="text-xs font-medium text-orange-600 mb-1">
                                    {message.user.name}
                                </p>
                            )}

                            {/* Contenu du fichier */}
                            {message.type !== 'text' && (
                                <div className="mb-2">
                                    {renderFileContent()}
                                </div>
                            )}

                            {/* Contenu texte */}
                            {message.content && (
                                <div className="break-words">
                                    <p dangerouslySetInnerHTML={{ __html: message.content }} />
                                </div>
                            )}

                            {/* Réactions */}
                            {message.reactions && message.reactions.length > 0 && (
                                <div className="flex flex-wrap gap-1 mt-2">
                                    {message.reactions.map((reaction, index) => (
                                        <div
                                            key={index}
                                            className={`
                                                inline-flex items-center px-2 py-1 rounded-full text-xs
                                                ${isOwn 
                                                    ? 'bg-white/20 text-white' 
                                                    : 'bg-gray-100 text-gray-700'
                                                }
                                            `}
                                            title={reaction.users.join(', ')}
                                        >
                                            <span className="mr-1">{reaction.emoji}</span>
                                            <span>{reaction.count}</span>
                                        </div>
                                    ))}
                                </div>
                            )}

                            {/* Heure et statut */}
                            <div className={`
                                flex items-center justify-end mt-1 text-xs
                                ${isOwn ? 'text-white/70' : 'text-gray-500'}
                            `}>
                                {message.is_edited && (
                                    <span className="mr-2 italic">modifié</span>
                                )}
                                <span>{formatTime(message.created_at)}</span>
                            </div>
                        </div>

                        {/* Actions rapides */}
                        {showActions && (
                            <div className={`
                                absolute top-0 flex items-center space-x-1
                                ${isOwn ? '-left-20' : '-right-20'}
                            `}>
                                <button
                                    onClick={onReply}
                                    className="p-1 bg-white border border-gray-200 rounded-full shadow-sm hover:bg-gray-50 transition-colors"
                                    title="Répondre"
                                >
                                    <Reply className="w-4 h-4 text-gray-500" />
                                </button>
                                <button
                                    className="p-1 bg-white border border-gray-200 rounded-full shadow-sm hover:bg-gray-50 transition-colors"
                                    title="Plus d'actions"
                                >
                                    <MoreVertical className="w-4 h-4 text-gray-500" />
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Modal d'image */}
            {showImageModal && message.type === 'image' && (
                <div
                    className="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4"
                    onClick={() => setShowImageModal(false)}
                >
                    <div className="relative max-w-4xl max-h-full">
                        <img
                            src={message.file_url}
                            alt={message.file_name}
                            className="max-w-full max-h-full object-contain rounded-lg"
                        />
                        <button
                            onClick={() => setShowImageModal(false)}
                            className="absolute top-4 right-4 p-2 bg-black/50 text-white rounded-full hover:bg-black/70 transition-colors"
                        >
                            ×
                        </button>
                    </div>
                </div>
            )}
        </>
    );
}
