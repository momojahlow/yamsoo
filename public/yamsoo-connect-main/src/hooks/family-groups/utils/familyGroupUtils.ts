
import { FamilyGroup, FamilyMember } from "../../types/familyGroups";
import { getRelationLabel } from "@/utils/relationUtils";

/**
 * Convert the family members map to an array of family groups
 */
export const mapToFamilyGroups = (familyMembersMap: Map<string, FamilyMember[]>): FamilyGroup[] => {
  const groups: FamilyGroup[] = [];
  
  for (const [fatherId, members] of familyMembersMap) {
    const fatherMember = members.find(m => m.userId === fatherId);
    const groupName = fatherId === 'autres' 
      ? 'Autres relations familiales' 
      : `Famille de ${fatherMember ? fatherMember.fullName : 'Unknown'}`;
    
    groups.push({
      id: fatherId,
      name: groupName,
      members
    });
  }
  
  console.log("Final family groups:", groups.length);
  return groups;
};

/**
 * Process relations that aren't connected to a father figure
 */
export const processOtherRelations = (
  relations: any[], 
  userId: string, 
  familyMembersMap: Map<string, FamilyMember[]>,
  createFamilyMember: (profile: any, relationLabel: string) => FamilyMember
) => {
  const otherMembersRelations = relations.filter(r => 
    r.relation_type !== 'father' && 
    r.relation_type !== 'son' &&
    r.status === 'accepted' &&
    ((r.user_id === userId && r.related_user_id !== userId) ||
     (r.related_user_id === userId && r.user_id !== userId))
  );
  
  if (otherMembersRelations && otherMembersRelations.length > 0) {
    const otherMembers: FamilyMember[] = [];
    
    for (const relation of otherMembersRelations) {
      const memberId = relation.user_id === userId 
        ? relation.related_user_id 
        : relation.user_id;
        
      // Skip self-references
      if (memberId === userId) {
        console.log("Skipping other relation with self");
        continue;
      }
        
      // Check if this member is already in another family
      let alreadyCounted = false;
      for (const [, members] of familyMembersMap) {
        if (members.some(m => m.userId === memberId)) {
          alreadyCounted = true;
          break;
        }
      }
      
      if (alreadyCounted) continue;
      
      const memberProfileRaw = relation.user_id === memberId 
        ? relation.user_profile 
        : relation.related_profile;
        
      // Avoid duplicates in the "other" category
      if (!otherMembers.some(m => m.userId === memberProfileRaw?.id)) {
        otherMembers.push(
          createFamilyMember(memberProfileRaw, getRelationLabel(relation.relation_type))
        );
      }
    }
    
    if (otherMembers.length > 0) {
      familyMembersMap.set('autres', otherMembers);
    }
  }
};
