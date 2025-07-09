
import { useState } from "react";
import { getSupabaseClient } from "@/utils/supabaseClient";
import { useToast } from "@/hooks/use-toast";

export function useRelationDelete() {
  const [selectedRelation, setSelectedRelation] = useState<{ id: string; name: string } | null>(null);
  const supabaseClient = getSupabaseClient();
  const { toast } = useToast();

  const handleDeleteRelation = async () => {
    if (!selectedRelation) return;

    try {
      const { error } = await supabaseClient
        .from('family_relations')
        .delete()
        .eq('id', selectedRelation.id);

      if (error) throw error;

      toast({
        title: "Succès",
        description: `La relation avec ${selectedRelation.name} a été supprimée`,
      });

      window.location.reload();
    } catch (error) {
      toast({
        title: "Erreur",
        description: "Impossible de supprimer la relation",
        variant: "destructive",
      });
    } finally {
      setSelectedRelation(null);
    }
  };

  return {
    selectedRelation,
    setSelectedRelation,
    handleDeleteRelation
  };
}
