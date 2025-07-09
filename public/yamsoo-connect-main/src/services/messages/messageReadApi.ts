
import { supabase } from "@/integrations/supabase/client";

export async function markMessageAsRead(messageId: string) {
  try {
    const { error } = await supabase
      .from("messages")
      .update({ read_at: new Date().toISOString() })
      .eq("id", messageId);

    if (error) throw error;
  } catch (error) {
    console.error("Error marking message as read:", error);
    throw error;
  }
}

export async function markAllMessagesAsRead(senderId: string, receiverId: string) {
  try {
    const { error } = await supabase
      .from("messages")
      .update({ read_at: new Date().toISOString() })
      .eq("sender_id", senderId)
      .eq("receiver_id", receiverId)
      .is("read_at", null);

    if (error) throw error;
    
    return { success: true };
  } catch (error) {
    console.error("Error marking all messages as read:", error);
    throw error;
  }
}
