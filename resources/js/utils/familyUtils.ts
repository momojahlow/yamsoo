
import { FamilyRelation } from "@/types/family";

/**
 * Returns a user-friendly label for a family relation type
 */
export const getRelationLabel = (relationType: string): string => {
  const relationLabels: Record<string, string> = {
    'father': 'Père',
    'mother': 'Mère',
    'son': 'Fils',
    'daughter': 'Fille',
    'husband': 'Mari',
    'wife': 'Épouse',
    'brother': 'Frère',
    'sister': 'Sœur',
    'grandfather': 'Grand-père',
    'grandmother': 'Grand-mère',
    'grandson': 'Petit-fils',
    'granddaughter': 'Petite-fille',
    'uncle': 'Oncle',
    'aunt': 'Tante',
    'nephew': 'Neveu',
    'niece': 'Nièce',
    'cousin': 'Cousin(e)',
    'spouse': 'Conjoint(e)',
  };
  
  return relationLabels[relationType] || relationType;
};

/**
 * Returns a user-friendly label for relation status
 */
export const getStatusLabel = (status: string): string => {
  const statusLabels: Record<string, string> = {
    'pending': 'En attente',
    'accepted': 'Acceptée',
    'rejected': 'Rejetée'
  };
  
  return statusLabels[status] || status;
};

/**
 * Format family relations for display in tree view
 */
export const formatFamilyRelationsForTree = (relations: FamilyRelation[]) => {
  return relations.map(relation => ({
    ...relation,
    formattedRelation: getRelationLabel(relation.relation_type)
  }));
};
