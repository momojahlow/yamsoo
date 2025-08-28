import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { MessageSquare } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import MessengerDropdown from './MessengerDropdown';

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

interface SimpleMessengerIconProps {
    currentUser: User;
    conversations: ConversationSummary[];
    totalUnreadCount: number;
    className?: string;
}

export default function SimpleMessengerIcon({ 
    currentUser, 
    conversations = [],
    totalUnreadCount = 0,
    className = ''
}: SimpleMessengerIconProps) {
    const [isDropdownOpen, setIsDropdownOpen] = useState(false);
    const [localConversations, setLocalConversations] = useState(conversations);
    const [localUnreadCount, setLocalUnreadCount] = useState(totalUnreadCount);

    // Mettre à jour les données locales quand les props changent
    useEffect(() => {
        setLocalConversations(conversations);
        setLocalUnreadCount(totalUnreadCount);
    }, [conversations, totalUnreadCount]);

    // Écouter les nouveaux messages via Echo
    useEffect(() => {
        if (window.Echo && localConversations.length > 0) {
            console.log('🔊 Abonnement Echo pour', localConversations.length, 'conversations');

            const channels: any[] = [];

            // S'abonner au canal privé de l'utilisateur
            try {
                const userChannel = window.Echo.private(`App.Models.User.${currentUser.id}`)
                    .notification((notification: any) => {
                        console.log('🔔 Notification reçue:', notification);
                        
                        if (notification.type === 'App\\Notifications\\NewMessageNotification') {
                            handleNewMessage(notification);
                        }
                    });

                channels.push(userChannel);
            } catch (error) {
                console.error('❌ Erreur abonnement utilisateur:', error);
            }

            // S'abonner aux conversations individuelles
            localConversations.forEach(conversation => {
                try {
                    const channel = window.Echo.private(`conversation.${conversation.id}`)
                        .listen('.message.sent', (event: any) => {
                            console.log('📨 Message Echo reçu pour conversation', conversation.id);
                            handleNewMessage(event);
                        });

                    channels.push(channel);
                } catch (error) {
                    console.error(`❌ Erreur abonnement conversation ${conversation.id}:`, error);
                }
            });

            return () => {
                console.log('🔇 Nettoyage abonnements Echo');
                channels.forEach((_, index) => {
                    try {
                        if (index === 0) {
                            window.Echo.leave(`App.Models.User.${currentUser.id}`);
                        } else {
                            const conv = localConversations[index - 1];
                            if (conv) {
                                window.Echo.leave(`conversation.${conv.id}`);
                            }
                        }
                    } catch (error) {
                        console.error('❌ Erreur nettoyage Echo:', error);
                    }
                });
            };
        }
    }, [localConversations, currentUser.id]);

    const handleNewMessage = (event: any) => {
        const message = event.message || event;
        
        // Ne pas traiter ses propres messages
        if (message.user?.id === currentUser.id) {
            return;
        }

        console.log('📨 Nouveau message reçu:', message);

        // Mettre à jour les conversations locales
        setLocalConversations(prevConversations => {
            return prevConversations.map(conv => {
                if (conv.id === message.conversation_id) {
                    return {
                        ...conv,
                        last_message: {
                            content: message.content,
                            created_at: message.created_at,
                            user_name: message.user?.name || 'Utilisateur',
                            is_own: false
                        },
                        unread_count: conv.unread_count + 1
                    };
                }
                return conv;
            });
        });

        // Mettre à jour le compteur total
        setLocalUnreadCount(prev => prev + 1);

        // Jouer un son si le dropdown est fermé
        if (!isDropdownOpen) {
            playNotificationSound();
        }
    };

    const playNotificationSound = () => {
        try {
            // Son simple avec Web Audio API
            const audioContext = new (window.AudioContext || (window as any).webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
            
            console.log('🔊 Son de notification joué');
        } catch (error) {
            console.error('❌ Erreur son de notification:', error);
        }
    };

    const handleToggleDropdown = () => {
        setIsDropdownOpen(!isDropdownOpen);
        
        // Actualiser les données quand on ouvre le dropdown
        if (!isDropdownOpen) {
            refreshConversations();
        }
    };

    const handleCloseDropdown = () => {
        setIsDropdownOpen(false);
    };

    const handleConversationClick = (conversationId: number) => {
        // Marquer la conversation comme lue localement
        setLocalConversations(prevConversations => {
            return prevConversations.map(conv => {
                if (conv.id === conversationId) {
                    const unreadBefore = conv.unread_count;
                    setLocalUnreadCount(prev => Math.max(0, prev - unreadBefore));
                    return { ...conv, unread_count: 0 };
                }
                return conv;
            });
        });

        handleCloseDropdown();
    };

    const refreshConversations = () => {
        // Utiliser Inertia pour actualiser les données
        router.reload({
            only: ['messengerData'],
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                console.log('🔄 Données Messenger actualisées');
            },
            onError: (errors) => {
                console.error('❌ Erreur actualisation:', errors);
            }
        });
    };

    return (
        <div className={`relative ${className}`}>
            <MessengerDropdown
                conversations={localConversations}
                totalUnreadCount={localUnreadCount}
                currentUser={currentUser}
                isOpen={isDropdownOpen}
                onToggle={handleToggleDropdown}
                onClose={handleCloseDropdown}
                onConversationClick={handleConversationClick}
            />
            
            {/* Debug info en développement */}
            {process.env.NODE_ENV === 'development' && (
                <div className="absolute top-full left-0 mt-2 p-2 bg-black text-white text-xs rounded shadow-lg z-50 whitespace-nowrap">
                    <div>Conversations: {localConversations.length}</div>
                    <div>Non lus: {localUnreadCount}</div>
                    <div>Dropdown: {isDropdownOpen ? 'Ouvert' : 'Fermé'}</div>
                    <div>Echo: {window.Echo ? 'Connecté' : 'Déconnecté'}</div>
                </div>
            )}
        </div>
    );
}
