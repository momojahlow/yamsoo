
import { useEffect, useState } from "react";
import { supabase } from "@/integrations/supabase/client";
import { Profile } from "@/types/notifications";

interface NotificationsBadgeProps {
  profile: Profile | null;
  className?: string;
  isCollapsed?: boolean;
  hideWhenCollapsed?: boolean; // New prop to control visibility in collapsed state
}

export function NotificationsBadge({ 
  profile, 
  className, 
  isCollapsed = false,
  hideWhenCollapsed = false // Default to showing badges even when collapsed
}: NotificationsBadgeProps) {
  const [suggestionCount, setSuggestionCount] = useState(0);
  
  useEffect(() => {
    // Only fetch if we have a user profile with ID
    if (profile && profile.id) {
      fetchSuggestionCount();
      
      // Setup realtime subscription for suggestion updates
      const channel = supabase
        .channel('relation-suggestions-changes')
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

  // Return null in any of these cases:
  // 1. No suggestions
  // 2. When sidebar is collapsed AND hideWhenCollapsed is true
  if (suggestionCount <= 0 || (isCollapsed && hideWhenCollapsed)) return null;
  
  return (
    <span className={`absolute min-w-5 h-5 flex items-center justify-center rounded-full bg-red-500 text-[11px] font-medium text-white ${
      isCollapsed ? '-top-1.5 -right-1.5' : 'top-1 right-2'
    } ${className || ''}`}>
      {suggestionCount}
    </span>
  );
}
