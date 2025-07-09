
import { FamilyGroup, FamilyMember } from "../types/familyGroups";
import { getRelationLabel } from "@/utils/relationUtils";
import { createFamilyMember } from "./utils/familyMemberUtils";
import { mapToFamilyGroups, processOtherRelations } from "./utils/familyGroupUtils";

/**
 * Processes raw family relations data into structured family groups
 */
export function useProcessFamilyGroups() {
  /**
   * Groups family relations into logical family groups
   */
  const processFamilyGroups = (relations: any[], userId: string): FamilyGroup[] => {
    if (!relations || relations.length === 0) return [];
    
    console.log("Processing family relations into groups");
    
    // Filter out self-relationships
    const filteredRelations = relations.filter(relation => {
      return !(relation.user_id === relation.related_user_id);
    });
    
    // Map to organize family members
    const familyMembersMap = new Map<string, FamilyMember[]>();
    
    // First, collect all father figures as potential family heads
    const fathersRelations = filteredRelations.filter(r => 
      (r.relation_type === 'father' && r.user_id === userId) ||
      (r.relation_type === 'son' && r.related_user_id === userId)
    );
    
    // Process each father-based family
    for (const fatherRelation of fathersRelations || []) {
      const fatherId = fatherRelation.user_id === userId 
        ? fatherRelation.related_user_id 
        : fatherRelation.user_id;
      
      // Skip if this is a self-relation
      if (fatherId === userId) {
        console.log("Skipping father relation with self");
        continue;
      }
      
      // Get the profile data for the father
      const fatherProfileRaw = fatherRelation.user_id === userId 
        ? fatherRelation.related_profile 
        : fatherRelation.user_profile;
      
      if (!fatherProfileRaw) {
        console.log("No father profile found, skipping");
        continue;
      }
      
      // Create the family group with the father
      familyMembersMap.set(fatherId, [
        createFamilyMember(fatherProfileRaw, getRelationLabel('father'))
      ]);
      
      // Find other members related to this father
      const fatherFamilyMembers = filteredRelations.filter(r => 
        ((r.user_id === fatherId && r.related_user_id !== userId) || 
         (r.related_user_id === fatherId && r.user_id !== userId)) &&
        r.status === 'accepted'
      );
      
      // Add each family member related to this father
      for (const familyMember of fatherFamilyMembers || []) {
        if (familyMember.id === fatherRelation.id) continue;
        
        const memberId = familyMember.user_id === fatherId 
          ? familyMember.related_user_id 
          : familyMember.user_id;
          
        // Skip self-references
        if (memberId === userId) {
          console.log("Skipping member relation with self");
          continue;
        }
          
        const memberProfileRaw = familyMember.user_id === memberId 
          ? familyMember.user_profile 
          : familyMember.related_profile;
        
        if (!memberProfileRaw) {
          console.log("No member profile found, skipping");
          continue;
        }
        
        // Add the member to the father's family
        const members = familyMembersMap.get(fatherId) || [];
        
        // Avoid duplicates
        if (!members.some(m => m.userId === memberProfileRaw.id)) {
          members.push(
            createFamilyMember(memberProfileRaw, getRelationLabel(familyMember.relation_type))
          );
          
          familyMembersMap.set(fatherId, members);
        }
      }
    }
    
    // Process other relations not connected to fathers
    processOtherRelations(filteredRelations, userId, familyMembersMap, createFamilyMember);
    
    // Convert the map to an array of family groups
    return mapToFamilyGroups(familyMembersMap);
  };

  return { processFamilyGroups };
}
