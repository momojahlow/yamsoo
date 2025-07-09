
import { FamilyRelation } from "@/types/family";
import { TreeNode } from "../types";
import { getRelationLabel } from "@/utils/familyUtils";

/**
 * Creates a basic tree node from a relation and profile
 */
export function createTreeNode(
  nodeId: string, 
  profile: any, 
  relationType: string, 
  relationId: string
): TreeNode {
  return {
    id: nodeId,
    name: `${profile.first_name} ${profile.last_name}`,
    avatarUrl: profile.avatar_url,
    relation: getRelationLabel(relationType),
    relationId: relationId,
    children: [],
    level: 0, // Will be adjusted later
    generation: 0, // Will be adjusted later
    birthDate: profile.birth_date || undefined // Extraire la date de naissance du profil
  };
}

/**
 * Creates a tree node for the current user
 */
export function createCurrentUserNode(userId: string, userProfile: any): TreeNode {
  return {
    id: userId,
    name: `${userProfile.first_name} ${userProfile.last_name}`,
    avatarUrl: userProfile.avatar_url,
    relation: "Moi",
    relationId: "",
    children: [],
    level: 2, // Reference level for current user
    generation: 0,
    birthDate: userProfile.birth_date || undefined // Extraire la date de naissance du profil utilisateur
  };
}

/**
 * Extract profile data from a relation based on whether it's a sent or received relation
 */
export function extractProfileFromRelation(relation: FamilyRelation): any {
  return relation.related_profile || relation.profiles;
}
