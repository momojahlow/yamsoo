
import { useState } from "react";
import { supabase } from "@/integrations/supabase/client";
import { useToast } from "@/hooks/use-toast";
import { safeProfileData } from "@/utils/profileUtils";

/**
 * Hook for fetching family relations from Supabase
 */
export function useFetchFamilyRelations() {
  const [loading, setLoading] = useState(false);
  const { toast } = useToast();

  const fetchRelations = async () => {
    try {
      setLoading(true);
      
      const { data: { user } } = await supabase.auth.getUser();
      if (!user) {
        toast({
          title: "Erreur",
          description: "Vous devez être connecté pour voir cette page",
          variant: "destructive",
        });
        return null;
      }

      console.log("Fetching family relations for user:", user.id);

      // Get all accepted relations for the current user
      const { data: relations, error } = await supabase
        .from('family_relations')
        .select(`
          *,
          related_profile:profiles!family_relations_related_user_id_fkey (
            id,
            first_name,
            last_name,
            email,
            avatar_url
          ),
          user_profile:profiles!family_relations_user_id_fkey (
            id,
            first_name,
            last_name,
            email,
            avatar_url
          )
        `)
        .or(`user_id.eq.${user.id},related_user_id.eq.${user.id}`)
        .eq('status', 'accepted');

      if (error) {
        throw error;
      }

      // Filter out relations with invalid profiles and ensure all profile data is safe
      const validRelations = relations?.filter(relation => {
        // Vérifier que les profils existent et ne sont pas des erreurs
        return relation && 
               relation.user_profile && typeof relation.user_profile !== 'string' && 
               relation.related_profile && typeof relation.related_profile !== 'string';
      }).map(relation => {
        // Utiliser safeProfileData pour s'assurer que les données de profil sont valides
        return {
          ...relation,
          user_profile: safeProfileData(relation.user_profile),
          related_profile: safeProfileData(relation.related_profile)
        };
      });

      return { relations: validRelations || [], userId: user.id };
    } catch (error) {
      console.error('Error fetching family relations:', error);
      toast({
        title: "Erreur",
        description: "Impossible de charger les relations familiales",
        variant: "destructive",
      });
      return null;
    } finally {
      setLoading(false);
    }
  };

  return { fetchRelations, loading };
}
