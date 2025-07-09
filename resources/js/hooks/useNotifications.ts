import { useState, useCallback } from "react";
import { supabase } from "@/integrations/supabase/client";
import { useToast } from "@/hooks/use-toast";
import { getInverseRelation, getRelationLabel, adaptRelationToGender } from "@/utils/relationUtils";
import type { Notification, Profile } from "@/types/notifications";
import { FamilyRelationType } from "@/types/family";
import { safeProfileData } from "@/utils/profileUtils";

export function useNotifications() {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [loading, setLoading] = useState(true);
  const { toast } = useToast();

  const fetchNotifications = useCallback(async (userId: string) => {
    try {
      console.log("Fetching notifications for user:", userId);
      
      // Fetch pending relations where the user is the recipient
      const { data: pendingRelations, error: relationsError } = await supabase
        .from('family_relations')
        .select(`
          id,
          user_id,
          related_user_id,
          relation_type,
          status,
          created_at
        `)
        .eq('related_user_id', userId)
        .eq('status', 'pending')
        .neq('user_id', userId); // Ensure user is not both sender and recipient

      if (relationsError) {
        console.error("Error fetching relations:", relationsError);
        throw relationsError;
      }
      
      console.log("Found pending relations:", pendingRelations?.length || 0);

      // Fetch sender profiles for each relation in a separate query
      const relationNotifications: Notification[] = [];
      
      if (pendingRelations && pendingRelations.length > 0) {
        // Get all sender user IDs
        const senderIds = pendingRelations.map(relation => relation.user_id);
        
        // Fetch all sender profiles in a single query
        const { data: senderProfiles, error: profilesError } = await supabase
          .from('profiles')
          .select('*')
          .in('id', senderIds);
          
        if (profilesError) {
          console.error("Error fetching sender profiles:", profilesError);
          throw profilesError;
        }
        
        // Create a map of user IDs to profiles for quick lookups
        const profileMap = new Map();
        if (senderProfiles) {
          senderProfiles.forEach(profile => {
            profileMap.set(profile.id, profile);
          });
        }
        
        // Map relations to notifications with their sender profiles
        pendingRelations.forEach(relation => {
          const senderProfile = profileMap.get(relation.user_id);
          const safeProfile = senderProfile ? safeProfileData(senderProfile) as Profile : null;
          
          // Get inverse relation type to display correctly
          let inverseRelationType = getInverseRelation(relation.relation_type as FamilyRelationType);
          
          // FIXED: Adapt relation type based on sender's gender more intelligently
          if (safeProfile?.gender) {
            // For parent-child relations, adapt based on sender's gender
            if (inverseRelationType === 'child') {
              inverseRelationType = safeProfile.gender === 'F' ? 'daughter' : 'son';
            } else if (inverseRelationType === 'father') {
              // Si c'est father mais que le demandeur est féminin, adapter vers mother
              inverseRelationType = safeProfile.gender === 'F' ? 'mother' : 'father';
            } else if (inverseRelationType === 'sibling') {
              inverseRelationType = safeProfile.gender === 'F' ? 'sister' : 'brother';
            } else if (inverseRelationType === 'spouse') {
              inverseRelationType = safeProfile.gender === 'F' ? 'wife' : 'husband';
            } else {
              // For other relations, use the existing adaptRelationToGender function
              inverseRelationType = adaptRelationToGender(
                inverseRelationType as FamilyRelationType, 
                safeProfile.gender
              );
            }
          }
          
          console.log(`Original relation: ${relation.relation_type}, Inverse: ${inverseRelationType}, Sender gender: ${safeProfile?.gender}`);
          
          relationNotifications.push({
            id: `relation-${relation.id}`,
            created_at: relation.created_at || '',
            type: 'relation',
            message: `Demande pour être votre ${getRelationLabel(inverseRelationType)}`,
            read: false,
            sender: safeProfile,
            relation_type: relation.relation_type,
            originalId: relation.id,
            isFromCurrentUser: false
          });
        });
      }
      
      // Fetch relation suggestions using a similar approach
      const { data: suggestions, error: suggestionsError } = await supabase
        .from('relation_suggestions')
        .select(`
          id,
          user_id,
          suggested_user_id,
          suggested_relation_type,
          reason,
          status,
          created_at
        `)
        .eq('user_id', userId)
        .eq('status', 'pending')
        .neq('suggested_user_id', userId); // Exclude self-suggestions

      if (suggestionsError) {
        console.error("Error fetching suggestions:", suggestionsError);
        throw suggestionsError;
      }
      
      console.log("Found pending suggestions:", suggestions?.length || 0);

      // Fetch suggested user profiles
      const suggestionNotifications: Notification[] = [];
      
      if (suggestions && suggestions.length > 0) {
        const suggestedUserIds = suggestions.map(suggestion => suggestion.suggested_user_id);
        
        const { data: suggestedProfiles, error: suggestedProfilesError } = await supabase
          .from('profiles')
          .select('*')
          .in('id', suggestedUserIds);
          
        if (suggestedProfilesError) {
          console.error("Error fetching suggested profiles:", suggestedProfilesError);
          throw suggestedProfilesError;
        }
        
        // Create a map of user IDs to profiles for quick lookups
        const profileMap = new Map();
        if (suggestedProfiles) {
          suggestedProfiles.forEach(profile => {
            profileMap.set(profile.id, profile);
          });
        }
        
        // Map suggestions to notifications with their profiles
        suggestions.forEach(suggestion => {
          const suggestedProfile = profileMap.get(suggestion.suggested_user_id);
          const safeProfile = suggestedProfile ? safeProfileData(suggestedProfile) as Profile : null;
          
          // Adapt relation type based on gender if needed
          const adaptedRelationType = safeProfile?.gender 
            ? adaptRelationToGender(suggestion.suggested_relation_type as FamilyRelationType, safeProfile.gender)
            : suggestion.suggested_relation_type;
            
          const relationLabel = getRelationLabel(adaptedRelationType);
          
          suggestionNotifications.push({
            id: `suggestion-${suggestion.id}`,
            created_at: suggestion.created_at || '',
            type: 'suggestion',
            message: `Suggestion: ${safeProfile?.first_name || ''} pourrait être votre ${relationLabel}`,
            read: false,
            sender: safeProfile,
            relation_type: suggestion.suggested_relation_type,
            originalId: suggestion.id,
            reason: suggestion.reason,
            isFromCurrentUser: false
          });
        });
      }

      // Combine and sort all notifications
      const allNotifications = [...relationNotifications, ...suggestionNotifications]
        .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());

      console.log("Total notifications after processing:", allNotifications.length);
      setNotifications(allNotifications);
      setLoading(false);
    } catch (error) {
      console.error('Error fetching notifications:', error);
      toast({
        title: "Erreur",
        description: "Impossible de charger les notifications",
        variant: "destructive",
      });
      setLoading(false);
      throw error;
    }
  }, [toast]);

  const setupRealtimeSubscription = useCallback((userId: string) => {
    console.log("Setting up realtime subscription for user:", userId);
    
    // Subscribe to changes in relations and suggestions
    const channel = supabase
      .channel('db-changes')
      .on(
        'postgres_changes',
        {
          event: '*',
          schema: 'public',
          table: 'family_relations',
          filter: `related_user_id=eq.${userId}`
        },
        (payload) => {
          console.log("Family relation change detected:", payload);
          fetchNotifications(userId).catch(console.error);
        }
      )
      .on(
        'postgres_changes',
        {
          event: '*',
          schema: 'public',
          table: 'relation_suggestions',
          filter: `user_id=eq.${userId}`
        },
        (payload) => {
          console.log("Relation suggestion change detected:", payload);
          fetchNotifications(userId).catch(console.error);
        }
      )
      .subscribe();

    return () => {
      console.log("Unsubscribing from realtime events");
      channel.unsubscribe();
    };
  }, [fetchNotifications]);

  return {
    notifications,
    loading,
    fetchNotifications,
    setupRealtimeSubscription,
    setLoading
  };
}
