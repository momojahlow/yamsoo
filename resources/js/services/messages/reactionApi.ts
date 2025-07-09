
import { supabase } from "@/integrations/supabase/client";

export async function addReaction(messageId: string, emoji: string, userId: string): Promise<void> {
  try {
    const { data: message } = await supabase
      .from("messages")
      .select("reactions")
      .eq("id", messageId)
      .single();

    const reactions = message?.reactions || {};
    const users = reactions[emoji] || [];

    const userIndex = users.indexOf(userId);
    if (userIndex === -1) {
      users.push(userId);
    } else {
      users.splice(userIndex, 1);
    }

    if (users.length > 0) {
      reactions[emoji] = users;
    } else {
      delete reactions[emoji];
    }

    const { error } = await supabase
      .from("messages")
      .update({ reactions })
      .eq("id", messageId);

    if (error) throw error;
  } catch (error) {
    console.error("Error adding reaction:", error);
    throw error;
  }
}
