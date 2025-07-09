
import { useState } from "react";
import { supabase } from "@/integrations/supabase/client";
import { useToast } from "@/hooks/use-toast";

export function useDeleteRelation() {
  const [isLoading, setIsLoading] = useState(false);
  const { toast } = useToast();

  const deleteRelation = async (relationId: string) => {
    setIsLoading(true);
    
    try {
      const { error } = await supabase
        .from('family_relations')
        .delete()
        .eq('id', relationId);
      
      if (error) {
        console.error('Error deleting relation:', error);
        toast({
          title: "Erreur",
          description: "Impossible de supprimer la relation",
          variant: "destructive",
        });
        return false;
      }
      
      toast({
        title: "Succès",
        description: "Relation supprimée avec succès",
      });
      
      return true;
    } catch (error) {
      console.error('Error during relation deletion:', error);
      toast({
        title: "Erreur système",
        description: "Une erreur inattendue s'est produite",
        variant: "destructive",
      });
      return false;
    } finally {
      setIsLoading(false);
    }
  };

  return {
    deleteRelation,
    isLoading
  };
}
