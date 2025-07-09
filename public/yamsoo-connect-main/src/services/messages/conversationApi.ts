
import { supabase } from "@/integrations/supabase/client";

export async function getConversations(userId: string) {
  try {
    // Récupérer tous les messages uniques auxquels cet utilisateur a participé
    const { data: messagesData, error: messagesError } = await supabase
      .from("messages")
      .select("*")
      .or(`sender_id.eq.${userId},receiver_id.eq.${userId}`)
      .order('created_at', { ascending: false });

    if (messagesError) {
      console.error("Error fetching conversations:", messagesError);
      throw messagesError;
    }

    // Extraire les partenaires de conversation uniques
    const conversationPartners = new Set<string>();
    messagesData?.forEach(message => {
      const partnerId = message.sender_id === userId ? message.receiver_id : message.sender_id;
      conversationPartners.add(partnerId);
    });

    // Obtenir les détails du profil pour chaque partenaire de conversation
    const partnerIds = Array.from(conversationPartners);
    if (partnerIds.length === 0) return [];

    const { data: profilesData, error: profilesError } = await supabase
      .from("profiles")
      .select("*")
      .in("id", partnerIds);

    if (profilesError) {
      console.error("Error fetching conversation profiles:", profilesError);
      throw profilesError;
    }

    // Combiner les données de message et de profil pour créer des résumés de conversation
    const conversations = profilesData?.map(profile => {
      // Trouver le message le plus récent avec ce partenaire
      const partnerMessages = messagesData?.filter(
        msg => msg.sender_id === profile.id || msg.receiver_id === profile.id
      ).sort((a, b) => 
        new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
      );
      
      const lastMessage = partnerMessages?.[0];
      
      // Compter les messages non lus
      const unreadCount = messagesData?.filter(
        msg => msg.sender_id === profile.id && msg.receiver_id === userId && !msg.read_at
      ).length || 0;

      return {
        profile,
        lastMessage: lastMessage || null,
        unreadCount
      };
    }) || [];

    // Trier les conversations par message le plus récent
    return conversations.sort((a, b) => {
      if (!a.lastMessage) return 1;
      if (!b.lastMessage) return -1;
      return new Date(b.lastMessage.created_at).getTime() - new Date(a.lastMessage.created_at).getTime();
    });
  } catch (error) {
    console.error("Error in getConversations:", error);
    return [];
  }
}
