import React, { useState, useEffect } from 'react';
import { useMessenger } from '@/contexts/MessengerContext';
import { useNotificationSound } from '@/hooks/useNotificationSound';
import { MessageSquare } from 'lucide-react';
import MessengerDropdown from './MessengerDropdown';

interface User {
    id: number;
    name: string;
    avatar?: string;
}

interface MessengerIconProps {
    currentUser: User;
    className?: string;
    showDropdownOnHover?: boolean;
}

export default function MessengerIcon({
    currentUser,
    className = '',
    showDropdownOnHover = false
}: MessengerIconProps) {
    const [isDropdownOpen, setIsDropdownOpen] = useState(false);
    const [lastUnreadCount, setLastUnreadCount] = useState(0);

    // État global du messenger depuis le Context
    const {
        state: { conversations, totalUnreadCount, loading, error },
        fetchConversations,
        markAsRead
    } = useMessenger();

    // Notifications sonores pour les nouveaux messages
    const { playNotificationSound } = useNotificationSound({
        enabled: true,
        volume: 0.7,
        soundUrl: '/notifications/alert-sound.mp3'
    });

    // Jouer un son quand le nombre de messages non lus augmente
    useEffect(() => {
        if (!loading && totalUnreadCount > lastUnreadCount && lastUnreadCount > 0) {
            // Un nouveau message est arrivé, jouer le son seulement si le dropdown est fermé
            if (!isDropdownOpen) {
                console.log('🔊 Nouveau message détecté, son de notification');
                playNotificationSound();
            }
        }
        setLastUnreadCount(totalUnreadCount);
    }, [totalUnreadCount, lastUnreadCount, loading, isDropdownOpen, playNotificationSound]);

    const handleToggleDropdown = () => {
        setIsDropdownOpen(!isDropdownOpen);

        // Actualiser les conversations quand on ouvre le dropdown
        if (!isDropdownOpen) {
            fetchConversations();
        }
    };

    const handleCloseDropdown = () => {
        setIsDropdownOpen(false);
    };

    const handleConversationClick = (conversationId: number) => {
        // Marquer la conversation comme lue
        markAsRead(conversationId);
        handleCloseDropdown();
    };

    // Gestion du hover pour le dropdown (optionnel)
    const handleMouseEnter = () => {
        if (showDropdownOnHover) {
            setIsDropdownOpen(true);
        }
    };

    const handleMouseLeave = () => {
        if (showDropdownOnHover) {
            setTimeout(() => setIsDropdownOpen(false), 300);
        }
    };

    if (error) {
        console.error('❌ Erreur MessengerIcon:', error);
        // Fallback : afficher l'icône sans fonctionnalités avancées
        return (
            <div className={`relative ${className}`}>
                <button
                    onClick={() => window.location.href = '/messagerie'}
                    className="relative p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors"
                    title="Messages (mode dégradé)"
                >
                    <MessageSquare className="w-6 h-6" />
                    <span className="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                </button>
            </div>
        );
    }

    return (
        <div
            className={`relative ${className}`}
            onMouseEnter={handleMouseEnter}
            onMouseLeave={handleMouseLeave}
        >
            <MessengerDropdown
                conversations={conversations}
                totalUnreadCount={totalUnreadCount}
                currentUser={currentUser}
                isOpen={isDropdownOpen}
                onToggle={handleToggleDropdown}
                onClose={handleCloseDropdown}
                onConversationClick={handleConversationClick}
            />

            {/* Debug info (à supprimer en production) */}
            {process.env.NODE_ENV === 'development' && (
                <div className="absolute top-full left-0 mt-2 p-2 bg-black text-white text-xs rounded shadow-lg z-50 whitespace-nowrap">
                    <div>Conversations: {conversations.length}</div>
                    <div>Non lus: {totalUnreadCount}</div>
                    <div>Loading: {loading ? 'Oui' : 'Non'}</div>
                    <div>Dropdown: {isDropdownOpen ? 'Ouvert' : 'Fermé'}</div>
                </div>
            )}
        </div>
    );
}
