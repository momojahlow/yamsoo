
import { useRef } from "react";
import { supabase } from "@/integrations/supabase/client";
import { User } from "@supabase/supabase-js";
import { DatabaseProfile } from "@/types/chat";

export function useTypingStatus(currentUser: User | null, selectedConversation: DatabaseProfile | null) {
  const typingTimeoutRef = useRef<NodeJS.Timeout>();

  const handleTyping = async (isTyping: boolean) => {
    if (!currentUser || !selectedConversation) return;

    if (typingTimeoutRef.current) {
      clearTimeout(typingTimeoutRef.current);
    }

    try {
      await supabase
        .from('messages')
        .update({ is_typing: isTyping })
        .eq('sender_id', currentUser.id)
        .eq('receiver_id', selectedConversation.id);

      if (isTyping) {
        typingTimeoutRef.current = setTimeout(() => {
          handleTyping(false);
        }, 5000);
      }
    } catch (error) {
      console.error('Error updating typing status:', error);
    }
  };

  return handleTyping;
}
