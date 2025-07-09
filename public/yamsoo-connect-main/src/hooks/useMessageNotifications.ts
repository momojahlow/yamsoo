
import { useState, useEffect } from "react";
import { getSupabaseClient } from "@/utils/supabaseClient";
import { useToast } from "@/hooks/use-toast";
import { formatDate } from "@/lib/utils";

export interface MessageNotification {
  id: string;
  content: string;
  sender_id: string;
  sender_name: string;
  sender_avatar?: string;
  created_at: string;
  formatted_date: string;
}

export function useMessageNotifications() {
  const [messages, setMessages] = useState<MessageNotification[]>([]);
  const [loading, setLoading] = useState(true);
  const { toast } = useToast();
  const supabaseClient = getSupabaseClient();

  const fetchMessages = async (userId: string) => {
    try {
      setLoading(true);
      console.log("Fetching messages for user:", userId);
      
      // Modifié pour ne pas utiliser la relation explicite mais faire une jointure manuelle
      const { data: messagesData, error } = await supabaseClient
        .from('messages')
        .select(`
          id,
          content,
          sender_id,
          created_at
        `)
        .eq('receiver_id', userId)
        .order('created_at', { ascending: false })
        .limit(10);

      if (error) {
        console.error("Error fetching messages:", error);
        throw error;
      }

      // Si pas de messages, retourner un tableau vide
      if (!messagesData || messagesData.length === 0) {
        setMessages([]);
        setLoading(false);
        return;
      }

      // Récupérer les profils des expéditeurs
      const senderIds = [...new Set(messagesData.map(msg => msg.sender_id))];
      const { data: profilesData, error: profilesError } = await supabaseClient
        .from('profiles')
        .select('*')
        .in('id', senderIds);

      if (profilesError) {
        console.error("Error fetching profiles:", profilesError);
        throw profilesError;
      }

      // Mapper les profils aux messages
      const formattedMessages = messagesData.map(message => {
        const senderProfile = profilesData?.find(profile => profile.id === message.sender_id);
        return {
          id: message.id,
          content: message.content,
          sender_id: message.sender_id,
          sender_name: senderProfile ? 
            `${senderProfile.first_name || ''} ${senderProfile.last_name || ''}`.trim() : 
            'Utilisateur inconnu',
          sender_avatar: senderProfile?.avatar_url,
          created_at: message.created_at,
          formatted_date: formatDate(message.created_at)
        };
      });
      
      setMessages(formattedMessages);
    } catch (error) {
      console.error('Error in useMessageNotifications:', error);
      toast({
        title: "Erreur",
        description: "Impossible de charger les messages",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  return {
    messages,
    loading,
    fetchMessages
  };
}
