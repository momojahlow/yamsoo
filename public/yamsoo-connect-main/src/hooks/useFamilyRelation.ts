
import { useState } from "react";
import { FamilyRelationType } from "@/types/family";
import { useAddRelation } from "./family-relations/useAddRelation";
import { useFetchRelations } from "./family-relations/useFetchRelations";
import { useDeleteRelation } from "./family-relations/useDeleteRelation";
import { useFetchPendingRelations } from "./family-relations/useFetchPendingRelations";

/**
 * Hook for managing family relations
 * Provides functions for adding, fetching, fetching pending, and deleting family relations
 */
export function useFamilyRelation() {
  const { addRelation, isLoading: isAddingRelation } = useAddRelation();
  const { fetchRelations, isLoading: isFetchingRelations } = useFetchRelations();
  const { fetchPendingRelations, isLoading: isFetchingPendingRelations } = useFetchPendingRelations();
  const { deleteRelation, isLoading: isDeletingRelation } = useDeleteRelation();
  
  // Combining all loading states
  const isLoading = isAddingRelation || isFetchingRelations || isDeletingRelation || isFetchingPendingRelations;

  return {
    addRelation,
    fetchRelations,
    fetchPendingRelations,
    deleteRelation,
    isLoading
  };
}
