
import { useState, useEffect, useCallback } from "react";
import { supabase } from "@/integrations/supabase/client";
import { useToast } from "@/hooks/use-toast";
import { User } from "@supabase/supabase-js";
import { Message, MessageProfile } from "@/types/chat";
import { safeProfileData } from "@/utils/profileUtils";
import { getConversations, getMessages } from "@/services/messages";

export function useMessages(currentUser: User | null) {
  const [messages, setMessages] = useState<Message[]>([]);
  const [conversations, setConversations] = useState<any[]>([]);
  const [isLoadingConversations, setIsLoadingConversations] = useState(false);
  const { toast } = useToast();

  const loadMessages = useCallback(async () => {
    if (!currentUser) return;

    try {
      // Récupérer tous les messages envoyés et reçus par l'utilisateur courant
      const { data: messagesData, error: messagesError } = await supabase
        .from('messages')
        .select("*")
        .or(`receiver_id.eq.${currentUser.id},sender_id.eq.${currentUser.id}`)
        .order('created_at', { ascending: true });

      if (messagesError) {
        console.error('Error loading messages:', messagesError);
        return;
      }

      console.log('Messages loaded:', messagesData?.length || 0);

      if (!messagesData || messagesData.length === 0) {
        setMessages([]);
        return;
      }

      // Récupérer les profils des utilisateurs impliqués
      const userIds = [...new Set([
        ...messagesData.map(msg => msg.sender_id),
        ...messagesData.map(msg => msg.receiver_id)
      ])];

      const { data: profilesData, error: profilesError } = await supabase
        .from('profiles')
        .select("*")
        .in('id', userIds);

      if (profilesError) {
        console.error('Error loading profiles:', profilesError);
        return;
      }

      // Mapper les profils aux messages
      const validatedMessages = messagesData.map(msg => {
        // Trouver le profil de l'expéditeur
        const senderProfile = profilesData?.find(p => p.id === msg.sender_id);
        
        // Assurer que les réactions sont correctement typées
        let validReactions: Record<string, string[]> = {};
        if (msg.reactions && typeof msg.reactions === 'object') {
          Object.entries(msg.reactions as Record<string, unknown>).forEach(([emoji, users]) => {
            if (Array.isArray(users)) {
              validReactions[emoji] = users.filter((user): user is string => typeof user === 'string');
            }
          });
        }

        // Créer l'objet Message validé
        const message: Message = {
          id: msg.id,
          content: msg.content || '',
          created_at: msg.created_at,
          sender_id: msg.sender_id,
          receiver_id: msg.receiver_id,
          read_at: msg.read_at,
          is_typing: msg.is_typing || false,
          attachment_url: msg.attachment_url || '',
          attachment_name: msg.attachment_name || '',
          updated_at: msg.updated_at,
          audio_url: msg.audio_url || '',
          audio_duration: msg.audio_duration || 0,
          audio_transcription: msg.audio_transcription || '',
          reactions: validReactions,
          sender_profile: senderProfile ? safeProfileData(senderProfile) as MessageProfile : null
        };
        return message;
      });

      setMessages(validatedMessages);
    } catch (error) {
      console.error('Error in loadMessages:', error);
    }
  }, [currentUser]);

  const loadConversations = useCallback(async () => {
    if (!currentUser) return;
    
    setIsLoadingConversations(true);
    try {
      const conversationsData = await getConversations(currentUser.id);
      setConversations(conversationsData);
    } catch (error) {
      console.error('Error loading conversations:', error);
      toast({
        title: "Erreur",
        description: "Impossible de charger les conversations",
        variant: "destructive",
      });
    } finally {
      setIsLoadingConversations(false);
    }
  }, [currentUser, toast]);

  useEffect(() => {
    if (!currentUser) return;

    loadMessages();
    loadConversations();

    const channel = supabase
      .channel('chat_updates')
      .on('postgres_changes', 
        { 
          event: '*', 
          schema: 'public', 
          table: 'messages',
          filter: `receiver_id=eq.${currentUser.id}`
        }, 
        async (payload) => {
          console.log('Realtime update:', payload);
          
          if (payload.eventType === 'INSERT') {
            await loadMessages();
            await loadConversations();
            
            toast({
              title: "Nouveau message",
              description: "Vous avez reçu un nouveau message",
            });
          } else if (payload.eventType === 'UPDATE') {
            await loadMessages();
            await loadConversations();
          }
        }
      )
      .subscribe();

    return () => {
      supabase.removeChannel(channel);
    };
  }, [currentUser, loadMessages, loadConversations, toast]);

  return { 
    messages, 
    conversations, 
    isLoadingConversations,
    loadMessages,
    loadConversations
  };
}
