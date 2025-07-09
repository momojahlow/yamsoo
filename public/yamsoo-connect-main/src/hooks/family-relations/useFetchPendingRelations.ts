
import { useState } from "react";
import { supabase } from "@/integrations/supabase/client";
import { useToast } from "@/hooks/use-toast";
import { FamilyRelation, UserProfile } from "@/types/family";

export function useFetchPendingRelations() {
  const [isLoading, setIsLoading] = useState(false);
  const { toast } = useToast();

  const fetchPendingRelations = async (): Promise<FamilyRelation[]> => {
    setIsLoading(true);
    
    try {
      const { data: { user }, error: authError } = await supabase.auth.getUser();
      
      if (authError || !user) {
        console.error('Erreur d\'authentification:', authError);
        toast({
          title: "Erreur d'authentification",
          description: "Votre session a expiré. Veuillez vous reconnecter.",
          variant: "destructive",
        });
        return [];
      }

      // Récupérer les demandes de relation où l'utilisateur est le destinataire
      const { data: pendingRelations, error } = await supabase
        .from('family_relations')
        .select(`
          id,
          user_id,
          related_user_id,
          relation_type,
          status,
          created_at,
          updated_at,
          profiles:user_id (
            id, 
            first_name, 
            last_name, 
            email, 
            avatar_url, 
            gender
          )
        `)
        .eq('related_user_id', user.id)
        .eq('status', 'pending');
      
      if (error) {
        console.error('Erreur lors de la récupération des relations en attente:', error);
        toast({
          title: "Erreur",
          description: "Impossible de récupérer les demandes de relation",
          variant: "destructive",
        });
        return [];
      }
      
      // Transformer les résultats pour faciliter l'utilisation
      const transformedRelations = pendingRelations.map(relation => {
        // S'assurer que le profil est correctement typé
        const userProfile = relation.profiles as unknown as UserProfile;
        
        return {
          id: relation.id,
          user_id: relation.user_id,
          related_user_id: relation.related_user_id,
          relation_type: relation.relation_type,
          status: relation.status,
          created_at: relation.created_at,
          updated_at: relation.updated_at,
          // Assigner le profil de l'utilisateur demandeur
          user_profile: userProfile || null,
          related_profile: null // On n'a pas chargé le profil de l'utilisateur courant
        } as FamilyRelation;
      });
      
      console.log('Demandes de relation en attente récupérées:', transformedRelations);
      return transformedRelations;
    } catch (error) {
      console.error('Erreur lors de la récupération des demandes de relation:', error);
      toast({
        title: "Erreur système",
        description: "Une erreur inattendue s'est produite",
        variant: "destructive",
      });
      return [];
    } finally {
      setIsLoading(false);
    }
  };

  return {
    fetchPendingRelations,
    isLoading
  };
}
