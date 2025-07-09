
import { useState, useEffect } from "react";
import { getSupabaseClient } from "@/utils/supabaseClient";
import { supabase } from "@/integrations/supabase/client";

export const useUnreadMessages = (userId: string | undefined) => {
  const [unreadCount, setUnreadCount] = useState(0);
  const supabaseClient = getSupabaseClient();
  
  useEffect(() => {
    if (!userId) return;
    
    const fetchUnreadCount = async () => {
      try {
        const { data, error } = await supabaseClient
          .from('messages')
          .select('*')  // Changed from select('id', { count: 'exact' }) to just select()
          .eq('receiver_id', userId)
          .is('read_at', null);
          
        if (error) {
          console.error('Error fetching unread messages:', error);
          return;
        }
        
        setUnreadCount(data?.length || 0);
      } catch (error) {
        console.error('Error in useUnreadMessages:', error);
      }
    };
    
    fetchUnreadCount();
    
    // Subscribe to new messages
    const channel = supabase.channel('message_changes');
    channel
      .on('postgres_changes', { 
        event: 'INSERT', 
        schema: 'public', 
        table: 'messages',
        filter: `receiver_id=eq.${userId}` 
      }, () => {
        fetchUnreadCount();
      })
      .subscribe();
      
    return () => {
      // Use supabase directly instead of supabaseClient for channel management
      channel.unsubscribe();
    };
  }, [userId]);
  
  return unreadCount;
};
