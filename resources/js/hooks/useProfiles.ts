
import { useState, useEffect } from "react";
import { supabase } from "@/integrations/supabase/client";
import { User } from "@supabase/supabase-js";
import { DatabaseProfile } from "@/types/chat";

export function useProfiles(currentUser: User | null) {
  const [profiles, setProfiles] = useState<DatabaseProfile[]>([]);

  useEffect(() => {
    if (!currentUser) return;

    const loadProfiles = async () => {
      const { data: profilesData, error: profilesError } = await supabase
        .from('profiles')
        .select('*')
        .neq('id', currentUser.id);

      if (profilesError) {
        console.error('Error loading profiles:', profilesError);
        return;
      }

      setProfiles(profilesData || []);
    };

    loadProfiles();
  }, [currentUser]);

  return profiles;
}
