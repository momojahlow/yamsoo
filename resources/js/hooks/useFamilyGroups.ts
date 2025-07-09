
import { useState, useEffect } from "react";
import { useToast } from "@/hooks/use-toast";
import { FamilyGroup } from "./types/familyGroups";
import { useFetchFamilyRelations } from "./family-groups/useFetchFamilyRelations";
import { useProcessFamilyGroups } from "./family-groups/useProcessFamilyGroups";

/**
 * Hook for fetching and organizing family groups
 */
export function useFamilyGroups() {
  const [familyGroups, setFamilyGroups] = useState<FamilyGroup[]>([]);
  const { fetchRelations, loading: fetchLoading } = useFetchFamilyRelations();
  const { processFamilyGroups } = useProcessFamilyGroups();
  const [loading, setLoading] = useState(true);
  const { toast } = useToast();

  useEffect(() => {
    const loadFamilyGroups = async () => {
      try {
        setLoading(true);
        
        // Fetch the family relations data
        const result = await fetchRelations();
        
        if (!result) {
          setFamilyGroups([]);
          return;
        }
        
        const { relations, userId } = result;
        
        // Process the relations into family groups
        const groups = processFamilyGroups(relations, userId);
        
        setFamilyGroups(groups);
      } catch (error) {
        console.error('Error loading family groups:', error);
        toast({
          title: "Erreur",
          description: "Impossible de charger les groupes familiaux",
          variant: "destructive",
        });
        setFamilyGroups([]);
      } finally {
        setLoading(false);
      }
    };

    loadFamilyGroups();
  }, [toast, fetchRelations, processFamilyGroups]);

  return { familyGroups, loading: loading || fetchLoading };
}
