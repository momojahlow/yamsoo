
import { useState } from "react";
import { supabase } from "@/integrations/supabase/client";
import { supabaseRpc } from "@/utils/supabaseClient";
import { useToast } from "@/hooks/use-toast";
import { FamilyRelationStatus } from "@/types/family";

export function useRelationResponse() {
  const [loading, setLoading] = useState(false);
  const { toast } = useToast();

  const handleResponse = async (relationId: string, accept: boolean) => {
    setLoading(true);
    try {
      // 1. Vérification de l'authentification
      const { data: { user }, error: authError } = await supabase.auth.getUser();
      if (authError || !user) {
        console.error('Erreur d\'authentification:', authError);
        toast({
          title: "Erreur d'authentification",
          description: "Votre session a expiré. Veuillez vous reconnecter.",
          variant: "destructive",
        });
        return false;
      }
      
      console.log("Tentative de mise à jour de la relation:", relationId, accept ? "acceptée" : "rejetée");
      console.log("ID utilisateur authentifié:", user.id);
      
      // Utiliser la fonction RPC update_family_relation_status via notre helper sécurisé
      const newStatus: FamilyRelationStatus = accept ? 'accepted' : 'rejected';
      
      console.log("Tentative de mise à jour de la relation via RPC:", relationId, newStatus);
      
      const { data, error } = await supabaseRpc.update_family_relation_status({
        relation_id: relationId,
        new_status: newStatus
      });
        
      if (error) {
        console.error('Erreur lors de la mise à jour de la relation:', error);
        toast({
          title: "Erreur",
          description: "Impossible de mettre à jour la relation: " + error.message,
          variant: "destructive",
        });
        return false;
      }
        
      console.log("Mise à jour réussie:", data);

      // 3. Notification de succès
      toast({
        title: "Succès",
        description: accept 
          ? "Vous avez accepté la demande de relation familiale" 
          : "Vous avez refusé la demande de relation familiale",
      });

      return true;
    } catch (error) {
      console.error('Erreur inattendue:', error);
      toast({
        title: "Erreur système",
        description: "Une erreur inattendue s'est produite",
        variant: "destructive",
      });
      return false;
    } finally {
      setLoading(false);
    }
  };

  return {
    handleResponse,
    loading
  };
}
