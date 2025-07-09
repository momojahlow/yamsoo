
import { supabase } from "@/integrations/supabase/client";
import type { Message } from "@/types/chat";

export async function getMessages(senderId: string, receiverId: string) {
  try {
    // Récupérer d'abord les messages
    const { data: messagesData, error: messagesError } = await supabase
      .from("messages")
      .select("*")
      .or(`and(sender_id.eq.${senderId},receiver_id.eq.${receiverId}),and(sender_id.eq.${receiverId},receiver_id.eq.${senderId})`)
      .order("created_at", { ascending: true });

    if (messagesError) {
      console.error("Error fetching messages:", messagesError);
      throw messagesError;
    }

    // Si pas de messages, retourner un tableau vide
    if (!messagesData || messagesData.length === 0) {
      return [];
    }

    // Récupérer les profils des expéditeurs
    const senderIds = [...new Set(messagesData.map(msg => msg.sender_id))];
    const { data: profilesData, error: profilesError } = await supabase
      .from("profiles")
      .select("*")
      .in("id", senderIds);

    if (profilesError) {
      console.error("Error fetching profiles:", profilesError);
      throw profilesError;
    }

    // Mapper les profils aux messages
    const messagesWithProfiles = messagesData.map(message => {
      const senderProfile = profilesData?.find(profile => profile.id === message.sender_id) || null;
      return {
        ...message,
        sender_profile: senderProfile
      };
    });

    console.log("Retrieved messages:", messagesWithProfiles.length || 0);
    return messagesWithProfiles || [];
  } catch (error) {
    console.error("Error in getMessages:", error);
    return [];
  }
}

export async function sendMessage(
  content: string, 
  senderId: string, 
  receiverId: string,
  attachment_url?: string | null,
  attachment_name?: string | null,
  audio_url?: string | null,
  audio_duration?: number | null
) {
  try {
    if (!content.trim() && !attachment_url && !audio_url) {
      throw new Error("Le contenu du message ne peut pas être vide");
    }
    
    const { data, error } = await supabase
      .from("messages")
      .insert([
        {
          content,
          sender_id: senderId,
          receiver_id: receiverId,
          attachment_url,
          attachment_name,
          audio_url,
          audio_duration
        }
      ])
      .select()
      .single();

    if (error) {
      console.error("Error sending message:", error);
      throw error;
    }

    console.log("Message sent successfully:", data);
    return data;
  } catch (error) {
    console.error("Error in sendMessage:", error);
    throw error;
  }
}

export { markMessageAsRead, markAllMessagesAsRead } from './messageReadApi';
export { getConversations } from './conversationApi';
