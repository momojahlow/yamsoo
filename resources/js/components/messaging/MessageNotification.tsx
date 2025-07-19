import React, { useEffect, useState } from 'react';
import { X, MessageCircle } from 'lucide-react';

interface MessageNotificationProps {
    message: {
        id: number;
        content: string;
        user: {
            name: string;
            avatar?: string;
        };
        conversation: {
            name: string;
        };
    };
    onClose: () => void;
    onClick: () => void;
}

export default function MessageNotification({ message, onClose, onClick }: MessageNotificationProps) {
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        // Animation d'entrée
        const timer = setTimeout(() => setIsVisible(true), 100);
        
        // Auto-fermeture après 5 secondes
        const autoClose = setTimeout(() => {
            setIsVisible(false);
            setTimeout(onClose, 300);
        }, 5000);

        return () => {
            clearTimeout(timer);
            clearTimeout(autoClose);
        };
    }, [onClose]);

    const handleClose = () => {
        setIsVisible(false);
        setTimeout(onClose, 300);
    };

    const handleClick = () => {
        onClick();
        handleClose();
    };

    const getInitials = (name: string) => {
        return name
            .split(' ')
            .map(word => word.charAt(0))
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };

    return (
        <div
            className={`
                fixed top-4 right-4 z-50 max-w-sm w-full bg-white rounded-lg shadow-lg border border-gray-200 
                transform transition-all duration-300 cursor-pointer
                ${isVisible ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'}
            `}
            onClick={handleClick}
        >
            <div className="p-4">
                <div className="flex items-start">
                    {/* Avatar */}
                    <div className="flex-shrink-0 mr-3">
                        {message.user.avatar ? (
                            <img
                                src={message.user.avatar}
                                alt={message.user.name}
                                className="w-10 h-10 rounded-full object-cover"
                            />
                        ) : (
                            <div className="w-10 h-10 rounded-full bg-gradient-to-br from-orange-100 to-red-100 flex items-center justify-center text-orange-600 font-medium text-sm">
                                {getInitials(message.user.name)}
                            </div>
                        )}
                    </div>

                    {/* Contenu */}
                    <div className="flex-1 min-w-0">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center">
                                <MessageCircle className="w-4 h-4 text-orange-500 mr-1" />
                                <p className="text-sm font-medium text-gray-900 truncate">
                                    {message.user.name}
                                </p>
                            </div>
                            <button
                                onClick={(e) => {
                                    e.stopPropagation();
                                    handleClose();
                                }}
                                className="ml-2 text-gray-400 hover:text-gray-600 transition-colors"
                            >
                                <X className="w-4 h-4" />
                            </button>
                        </div>
                        
                        <p className="text-xs text-gray-500 mb-1">
                            {message.conversation.name}
                        </p>
                        
                        <p className="text-sm text-gray-700 line-clamp-2">
                            {message.content}
                        </p>
                    </div>
                </div>
            </div>

            {/* Barre de progression */}
            <div className="h-1 bg-gray-100 rounded-b-lg overflow-hidden">
                <div 
                    className="h-full bg-gradient-to-r from-orange-500 to-red-500 rounded-b-lg animate-progress"
                    style={{
                        animation: 'progress 5s linear forwards'
                    }}
                />
            </div>

            <style jsx>{`
                @keyframes progress {
                    from { width: 100%; }
                    to { width: 0%; }
                }
                .animate-progress {
                    animation: progress 5s linear forwards;
                }
                .line-clamp-2 {
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                }
            `}</style>
        </div>
    );
}
