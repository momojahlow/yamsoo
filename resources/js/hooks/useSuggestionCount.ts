
import { useState, useEffect } from "react";
import { supabase } from "@/integrations/supabase/client";
import { Profile } from "@/types/notifications";

export function useSuggestionCount(profile: Profile | null) {
  const [suggestionCount, setSuggestionCount] = useState(0);

  useEffect(() => {
    // Only fetch if we have a user profile with ID
    if (profile && profile.id) {
      fetchSuggestionCount();
      
      // Setup realtime subscription for suggestion updates
      const channel = supabase
        .channel('mobile-relation-suggestions')
        .on(
          'postgres_changes',
          {
            event: '*',
            schema: 'public',
            table: 'relation_suggestions',
            filter: `user_id=eq.${profile.id}`,
          },
          () => fetchSuggestionCount()
        )
        .subscribe();
        
      return () => {
        supabase.removeChannel(channel);
      };
    }
  }, [profile]);
  
  const fetchSuggestionCount = async () => {
    try {
      if (!profile || !profile.id) return;
      
      const { data, error } = await supabase
        .from('relation_suggestions')
        .select('id')
        .eq('user_id', profile.id)
        .eq('status', 'pending');
      
      if (error) {
        console.error('Error fetching suggestion count:', error);
        return;
      }
      
      setSuggestionCount(data?.length || 0);
    } catch (err) {
      console.error('Error in fetchSuggestionCount:', err);
    }
  };

  return { suggestionCount };
}
