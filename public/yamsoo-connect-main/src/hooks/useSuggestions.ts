import { useState, useEffect } from "react";
import { supabase } from "@/integrations/supabase/client";
import { useToast } from "@/hooks/use-toast";
import { Profile } from "@/types/notifications";
import { Suggestion } from "@/components/suggestions/types";
import { useAddRelation } from "@/hooks/family-relations/useAddRelation";
import { FamilyRelationType } from "@/types/family";
import { useFetchRelations } from "@/hooks/family-relations/useFetchRelations";

export function useSuggestions(currentProfile: Profile | null) {
  const [suggestions, setSuggestions] = useState<Suggestion[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const { toast } = useToast();
  const { addRelation } = useAddRelation();
  const { fetchRelations } = useFetchRelations();

  // Fetch all available suggestions for the current user
  const fetchSuggestions = async () => {
    if (!currentProfile || !currentProfile.id) {
      console.log("No profile or profile ID available, skipping suggestion fetch");
      setLoading(false);
      setSuggestions([]);
      return;
    }

    setLoading(true);
    try {
      console.log("Fetching suggestions for user:", currentProfile.id);
      
      // Fetch existing family relations first to filter out suggestions
      const existingRelations = await fetchRelations();
      const existingRelationUserIds = new Set<string>();
      
      if (existingRelations && existingRelations.length > 0) {
        existingRelations.forEach(relation => {
          // For each relation, add the related user ID to the set
          if (relation.related_profile) {
            existingRelationUserIds.add(relation.related_profile.id);
          }
        });
      }
      
      console.log(`Found ${existingRelationUserIds.size} existing relations to filter out`);

      // Fetch relation suggestions from the backend
      const { data: suggestionData, error: suggestionError } = await supabase
        .from("relation_suggestions")
        .select("*")
        .eq("user_id", currentProfile.id)
        .eq("status", "pending");

      if (suggestionError) {
        console.error("Error fetching suggestions:", suggestionError);
        throw suggestionError;
      }

      console.log(`Fetched ${suggestionData?.length || 0} initial suggestions`);

      // Fetch profiles for all the suggested users
      if (suggestionData && suggestionData.length > 0) {
        const suggestedUserIds = suggestionData.map((s) => s.suggested_user_id);
        
        const { data: profilesData, error: profilesError } = await supabase
          .from("profiles")
          .select("*")
          .in("id", suggestedUserIds);
          
        if (profilesError) {
          console.error("Error fetching profiles for suggestions:", profilesError);
          throw profilesError;
        }
        
        // Filter out suggestions for users who already have a family relation
        const filteredSuggestionData = suggestionData.filter(suggestion => 
          !existingRelationUserIds.has(suggestion.suggested_user_id)
        );
        
        console.log(`Filtered to ${filteredSuggestionData.length} suggestions after removing existing relations`);
        
        // Map profiles to suggestions - ensuring all required fields from Suggestion type are present
        const suggestionsWithProfiles: Suggestion[] = filteredSuggestionData.map((suggestion) => {
          const profile = profilesData?.find(p => p.id === suggestion.suggested_user_id);
          
          // Ensure all required fields are included in the returned object
          return {
            id: suggestion.id,
            created_at: suggestion.created_at,
            user_id: suggestion.user_id,
            suggested_relation_type: suggestion.suggested_relation_type,
            target_id: suggestion.suggested_user_id, // Map suggested_user_id to target_id
            suggested_user_id: suggestion.suggested_user_id,
            status: suggestion.status,
            target_name: profile?.first_name && profile?.last_name ? 
              `${profile.first_name} ${profile.last_name}`.trim() : undefined,
            target_avatar_url: profile?.avatar_url || undefined,
            reason: suggestion.reason,
            profiles: profile ? {
              first_name: profile.first_name,
              last_name: profile.last_name,
              avatar_url: profile.avatar_url,
              gender: profile.gender,
              email: profile.email
            } : undefined
          };
        });
        
        setSuggestions(suggestionsWithProfiles);
      } else {
        setSuggestions([]);
      }
    } catch (error) {
      console.error("Error fetching suggestions:", error);
      toast({
        title: "Erreur",
        description: "Impossible de charger les suggestions",
        variant: "destructive",
      });
      setSuggestions([]);
    } finally {
      setLoading(false);
    }
  };

  // Handle accepting a suggestion
  const handleAcceptSuggestion = async (suggestionId: string, selectedRelationType?: string) => {
    try {
      // Find the suggestion
      const suggestion = suggestions.find((s) => s.id === suggestionId);
      if (!suggestion) {
        toast({
          title: "Erreur",
          description: "Suggestion non trouvée",
          variant: "destructive",
        });
        return;
      }
      
      // Use the selected relation type if provided, otherwise use the suggested one
      const relationType = selectedRelationType || suggestion.suggested_relation_type;
      
      if (!relationType) {
        toast({
          title: "Erreur",
          description: "Veuillez sélectionner un type de relation",
          variant: "destructive",
        });
        return;
      }
      
      // Add the relation
      const success = await addRelation(
        suggestion.suggested_user_id, 
        relationType as FamilyRelationType
      );
      
      if (success) {
        // Update the suggestion status in the database
        const { error: updateError } = await supabase
          .from("relation_suggestions")
          .update({ status: "accepted" })
          .eq("id", suggestionId);
          
        if (updateError) {
          console.error("Error updating suggestion status:", updateError);
          throw updateError;
        }
        
        // Remove the suggestion from local state
        setSuggestions((prev) => prev.filter((s) => s.id !== suggestionId));
        
        toast({
          title: "Succès",
          description: "Relation ajoutée avec succès",
        });
      }
    } catch (error) {
      console.error("Error accepting suggestion:", error);
      toast({
        title: "Erreur",
        description: "Impossible d'accepter la suggestion",
        variant: "destructive",
      });
    }
  };

  // Handle rejecting a suggestion
  const handleRejectSuggestion = async (suggestionId: string) => {
    try {
      // Update the suggestion status in the database
      const { error } = await supabase
        .from("relation_suggestions")
        .update({ status: "rejected" })
        .eq("id", suggestionId);
        
      if (error) {
        console.error("Error rejecting suggestion:", error);
        throw error;
      }
      
      // Remove the suggestion from local state
      setSuggestions((prev) => prev.filter((s) => s.id !== suggestionId));
      
      toast({
        title: "Suggérence rejetée",
        description: "La suggestion a été rejetée avec succès",
      });
    } catch (error) {
      console.error("Error rejecting suggestion:", error);
      toast({
        title: "Erreur",
        description: "Impossible de rejeter la suggestion",
        variant: "destructive",
      });
    }
  };

  // Fetch suggestions only when we have a valid currentProfile with ID
  useEffect(() => {
    if (currentProfile && currentProfile.id) {
      console.log("Profile available, fetching suggestions");
      fetchSuggestions();
    } else {
      console.log("No valid profile available, skipping suggestion fetch");
      setLoading(false);
    }
  }, [currentProfile?.id]); // Only run when currentProfile.id changes

  return {
    suggestions,
    loading,
    handleAcceptSuggestion,
    handleRejectSuggestion,
    refetchSuggestions: fetchSuggestions,
  };
}
