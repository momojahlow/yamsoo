
import { useState } from "react";
import { useToast } from "@/hooks/use-toast";
import { supabaseRpc } from "@/utils/supabaseClient";

export function useNotificationActions(onSuccess?: () => void) {
  const [loading, setLoading] = useState(false);
  const { toast } = useToast();

  const handleAction = async (relationId: string | undefined, accept: boolean) => {
    if (!relationId) {
      console.error('No relation ID provided');
      toast({
        title: "Erreur",
        description: "ID de relation manquant",
        variant: "destructive",
      });
      return;
    }

    setLoading(true);
    
    try {
      // SECURITY IMPROVEMENT: Use the secure RPC function instead of direct table update
      const { data, error } = await supabaseRpc.update_family_relation_status({
        relation_id: relationId,
        new_status: accept ? 'accepted' : 'rejected'
      });

      if (error) {
        console.error('Error updating relation status:', error);
        toast({
          title: "Erreur",
          description: "Impossible de mettre à jour la relation",
          variant: "destructive",
        });
        return;
      }

      toast({
        title: "Succès",
        description: accept ? "Relation acceptée avec succès" : "Relation refusée avec succès",
      });

      // Call success callback if provided
      if (onSuccess) {
        onSuccess();
      }
    } catch (error) {
      console.error('Unexpected error:', error);
      toast({
        title: "Erreur système",
        description: "Une erreur inattendue s'est produite",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  return {
    handleAction,
    loading
  };
}
