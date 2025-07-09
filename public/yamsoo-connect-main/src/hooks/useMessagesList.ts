
import { useQuery } from "@tanstack/react-query";
import { supabase } from "@/integrations/supabase/client";
import { useNavigate } from "react-router-dom";
import { MessageWithSender } from "@/components/notifications/message/MessageItem";

export function useMessagesList() {
  const navigate = useNavigate();

  const { data: messages, isLoading, error } = useQuery({
    queryKey: ["messages"],
    queryFn: async () => {
      console.log("Starting messages fetch");
      
      const { data: sessionData } = await supabase.auth.getSession();
      const { data: userData } = await supabase.auth.getUser();
      
      if (!sessionData.session || !userData.user) {
        console.log("No session found, redirecting to login");
        navigate("/auth");
        return [];
      }

      // Récupérer tous les messages envoyés et reçus
      const { data: allMessages, error: messagesError } = await supabase
        .from('messages')
        .select('*')
        .or(`sender_id.eq.${userData.user.id},receiver_id.eq.${userData.user.id}`)
        .order('created_at', { ascending: true });

      if (messagesError) {
        console.error('Error fetching messages:', messagesError);
        throw messagesError;
      }

      // Si pas de messages, retourner un tableau vide
      if (!allMessages || allMessages.length === 0) {
        return [];
      }

      // Récupérer tous les IDs uniques des participants
      const participantIds = [...new Set(
        allMessages.flatMap(msg => [msg.sender_id, msg.receiver_id])
      )];

      // Récupérer les profils de tous les participants
      const { data: profilesData, error: profilesError } = await supabase
        .from('profiles')
        .select('*')
        .in('id', participantIds);

      if (profilesError) {
        console.error('Error fetching profiles:', profilesError);
        throw profilesError;
      }

      // Mapper les profils aux messages
      const messagesWithSenders = allMessages.map(message => ({
        ...message,
        sender: profilesData?.find(profile => profile.id === message.sender_id) || null
      }));

      return messagesWithSenders as MessageWithSender[];
    },
    meta: {
      errorMessage: "Impossible de charger les messages"
    }
  });

  return {
    messages,
    isLoading,
    error
  };
}
