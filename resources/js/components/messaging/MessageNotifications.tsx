import React, { useEffect, useState } from 'react';
import { toast } from 'sonner';
import { MessageSquare, User } from 'lucide-react';

interface MessageNotification {
  id: string;
  sender_name: string;
  content: string;
  conversation_id: number;
  created_at: string;
}

interface MessageNotificationsProps {
  userId: number;
  onNewMessage?: (notification: MessageNotification) => void;
}

export function MessageNotifications({ userId, onNewMessage }: MessageNotificationsProps) {
  const [lastCheck, setLastCheck] = useState<Date>(new Date());

  const checkForNewMessages = async () => {
    try {
      // Vérifier s'il y a de nouveaux messages depuis la dernière vérification
      const response = await fetch(`/api/messages/notifications?since=${lastCheck.toISOString()}`);
      
      if (response.ok) {
        const data = await response.json();
        
        if (data.new_messages && data.new_messages.length > 0) {
          data.new_messages.forEach((message: MessageNotification) => {
            // Afficher une notification toast
            toast(
              <div className="flex items-center space-x-3">
                <div className="flex-shrink-0">
                  <MessageSquare className="h-5 w-5 text-blue-500" />
                </div>
                <div className="flex-1">
                  <p className="text-sm font-medium text-gray-900">
                    {message.sender_name}
                  </p>
                  <p className="text-sm text-gray-500 truncate">
                    {message.content.length > 50 
                      ? message.content.substring(0, 50) + '...' 
                      : message.content
                    }
                  </p>
                </div>
              </div>,
              {
                duration: 5000,
                action: {
                  label: 'Voir',
                  onClick: () => {
                    window.location.href = `/messages?conversation=${message.conversation_id}`;
                  }
                }
              }
            );

            // Callback pour le parent
            if (onNewMessage) {
              onNewMessage(message);
            }
          });

          setLastCheck(new Date());
        }
      }
    } catch (error) {
      console.error('Erreur lors de la vérification des nouveaux messages:', error);
    }
  };

  useEffect(() => {
    // Vérifier les nouveaux messages toutes les 10 secondes
    const interval = setInterval(checkForNewMessages, 10000);

    // Vérification initiale après 2 secondes
    const initialTimeout = setTimeout(checkForNewMessages, 2000);

    return () => {
      clearInterval(interval);
      clearTimeout(initialTimeout);
    };
  }, [lastCheck]);

  // Écouter les événements de focus de la fenêtre pour vérifier immédiatement
  useEffect(() => {
    const handleFocus = () => {
      checkForNewMessages();
    };

    window.addEventListener('focus', handleFocus);
    return () => window.removeEventListener('focus', handleFocus);
  }, []);

  // Ce composant ne rend rien visuellement
  return null;
}

// Hook pour utiliser les notifications de messages
export function useMessageNotifications(userId: number) {
  const [newMessageCount, setNewMessageCount] = useState(0);

  const handleNewMessage = (notification: MessageNotification) => {
    setNewMessageCount(prev => prev + 1);
  };

  const resetCount = () => {
    setNewMessageCount(0);
  };

  return {
    newMessageCount,
    resetCount,
    handleNewMessage
  };
}
