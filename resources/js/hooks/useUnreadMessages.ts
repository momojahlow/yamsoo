import { useState, useEffect } from 'react';
import axios from 'axios';

interface UnreadMessagesData {
  unreadCount: number;
  unreadConversations: number;
  loading: boolean;
  error: string | null;
}

export function useUnreadMessages(): UnreadMessagesData {
  const [unreadCount, setUnreadCount] = useState(0);
  const [unreadConversations, setUnreadConversations] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchUnreadMessages = async () => {
    try {
      setLoading(true);
      setError(null);
      
      // Appel à l'API pour récupérer les statistiques de messages
      const response = await axios.get('/api/messages/stats');
      
      if (response.data) {
        setUnreadCount(response.data.unread_messages || 0);
        setUnreadConversations(response.data.unread_conversations || 0);
      }
    } catch (err) {
      console.error('Erreur lors de la récupération des messages non lus:', err);
      setError('Erreur lors du chargement');
      // En cas d'erreur, on met des valeurs par défaut
      setUnreadCount(0);
      setUnreadConversations(0);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchUnreadMessages();
    
    // Actualiser toutes les 30 secondes
    const interval = setInterval(fetchUnreadMessages, 30000);
    
    return () => clearInterval(interval);
  }, []);

  return {
    unreadCount,
    unreadConversations,
    loading,
    error
  };
}
