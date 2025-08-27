import { useEffect, useCallback } from 'react';
import { useNotificationSound } from './useNotificationSound';

interface User {
    id: number;
    name: string;
    avatar?: string;
}

interface Message {
    id: number;
    content: string;
    type: 'text' | 'image' | 'file' | 'audio' | 'video';
    user: User;
    conversation_id: number;
    created_at: string;
}

interface Conversation {
    id: number;
    name: string;
    type: 'private' | 'group';
}

interface GlobalNotificationsOptions {
    currentUser: User;
    conversations: Conversation[];
    enabled?: boolean;
    activeConversationId?: number | null; // Conversation actuellement ouverte
}

declare global {
    interface Window {
        Echo: any;
    }
}

export function useGlobalNotifications({ currentUser, conversations, enabled = true, activeConversationId = null }: GlobalNotificationsOptions) {
    const { playNotificationSound } = useNotificationSound({
        enabled,
        volume: 0.7,
        soundUrl: '/notifications/alert-sound.mp3'
    });

    // Fonction pour vérifier les préférences de notification d'une conversation
    const checkNotificationSettings = useCallback(async (conversationId: number): Promise<boolean> => {
        try {
            const response = await fetch(`/api/conversations/${conversationId}/notification-settings`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                }
            });

            if (response.ok) {
                const data = await response.json();
                return data.notifications_enabled ?? true;
            }
        } catch (error) {
            console.error('❌ Erreur lors de la vérification des préférences de notification:', error);
        }

        return true; // Par défaut, notifications activées
    }, []);

    // Gérer les nouveaux messages reçus
    const handleGlobalMessage = useCallback(async (event: any) => {
        const message: Message = event.message;

        // Ne pas traiter ses propres messages
        if (message.user.id === currentUser.id) {
            console.log('🔇 Message de l\'utilisateur actuel, pas de notification');
            return;
        }

        // Ne pas jouer de notification pour la conversation actuellement active
        if (activeConversationId && message.conversation_id === activeConversationId) {
            console.log('🔇 Message de la conversation active, pas de notification sonore');
            return;
        }

        console.log('📨 Nouveau message global reçu:', message);

        // Vérifier les préférences de notification pour cette conversation
        const notificationsEnabled = await checkNotificationSettings(message.conversation_id);

        if (notificationsEnabled) {
            // Jouer le son de notification
            await playNotificationSound(message, currentUser.id, true);

            // Optionnel : Afficher une notification navigateur
            if ('Notification' in window && Notification.permission === 'granted') {
                const conversation = conversations.find(c => c.id === message.conversation_id);
                const title = conversation?.type === 'group'
                    ? `${message.user.name} dans ${conversation.name}`
                    : message.user.name;

                new Notification(title, {
                    body: message.content.length > 50
                        ? message.content.substring(0, 50) + '...'
                        : message.content,
                    icon: message.user.avatar || '/favicon.ico',
                    tag: `message-${message.id}`, // Éviter les doublons
                    silent: true // Le son est géré par notre système
                });
            }
        } else {
            console.log('🔇 Notifications désactivées pour cette conversation');
        }
    }, [currentUser.id, conversations, playNotificationSound, checkNotificationSettings, activeConversationId]);

    // Écouter les messages sur toutes les conversations
    useEffect(() => {
        if (!enabled || !window.Echo || conversations.length === 0) {
            return;
        }

        console.log('🔊 Initialisation des notifications globales pour', conversations.length, 'conversations');

        const channels: any[] = [];

        // S'abonner à chaque conversation
        conversations.forEach(conversation => {
            try {
                const channel = window.Echo.private(`conversation.${conversation.id}`)
                    .listen('.message.sent', handleGlobalMessage)
                    .error((error: any) => {
                        console.error(`❌ Erreur Echo pour conversation ${conversation.id}:`, error);
                    });

                channels.push({
                    id: conversation.id,
                    channel
                });

                console.log(`✅ Écoute globale activée pour conversation ${conversation.id}`);
            } catch (error) {
                console.error(`🚨 Erreur lors de l'abonnement à la conversation ${conversation.id}:`, error);
            }
        });

        // Nettoyage
        return () => {
            console.log('🔇 Nettoyage des notifications globales');
            channels.forEach(({ id }) => {
                try {
                    if (window.Echo && typeof window.Echo.leave === 'function') {
                        window.Echo.leave(`conversation.${id}`);
                    }
                } catch (error) {
                    console.error(`❌ Erreur lors du nettoyage de la conversation ${id}:`, error);
                }
            });
        };
    }, [enabled, conversations, handleGlobalMessage]);

    // Demander la permission pour les notifications navigateur
    useEffect(() => {
        if (enabled && 'Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                console.log('🔔 Permission notifications navigateur:', permission);
            });
        }
    }, [enabled]);

    return {
        checkNotificationSettings
    };
}
