
// Type definitions
export type FamilyRelationType = 
  | 'father' | 'mother'
  | 'son' | 'daughter'
  | 'husband' | 'wife'
  | 'brother' | 'sister'
  | 'grandfather' | 'grandmother'
  | 'grandson' | 'granddaughter'
  | 'uncle' | 'aunt'
  | 'nephew' | 'niece'
  | 'cousin_paternal_m' | 'cousin_maternal_m'
  | 'cousin_paternal_f' | 'cousin_maternal_f'
  | 'stepfather' | 'stepmother'
  | 'stepson' | 'stepdaughter'
  | 'father_in_law' | 'mother_in_law'
  | 'son_in_law' | 'daughter_in_law'
  | 'brother_in_law' | 'sister_in_law';

export type ProfileWithId = {
  id: string;
  first_name: string;
  last_name: string;
  gender?: string;
};

// Structure pour représenter une relation dans un format plus facile à traiter
export type ProcessedRelation = {
  userId: string;
  relatedUserId: string;
  relationType: FamilyRelationType;
  userProfile?: ProfileWithId;
  relatedProfile?: ProfileWithId;
};

// Types for suggestion results
export type RelationSuggestion = {
  user_id: string;
  suggested_user_id: string;
  suggested_relation_type: FamilyRelationType;
  reason: string;
  similarity_score: number;
};

/**
 * Generate relation suggestions based on existing family relationships
 */
export function generateSuggestions(
  processedRelations: ProcessedRelation[],
  profilesMap: Map<string, ProfileWithId>
): RelationSuggestion[] {
  const suggestions: RelationSuggestion[] = [];
  
  // Structure pour identifier les relations parent-enfant
  // Map: Parent ID -> Array of Children IDs
  const parentToChildrenMap = buildParentChildrenMap(processedRelations);
  
  console.log(`👨‍👩‍👧‍👦 Relations parent-enfant identifiées pour ${parentToChildrenMap.size} parents`);
  
  // Create a bidirectional map of existing relations for fast lookups
  const existingRelations = new Map<string, Set<string>>();
  for (const rel of processedRelations) {
    // Add both directions to the map
    if (!existingRelations.has(rel.userId)) {
      existingRelations.set(rel.userId, new Set<string>());
    }
    existingRelations.get(rel.userId)?.add(rel.relatedUserId);
    
    if (!existingRelations.has(rel.relatedUserId)) {
      existingRelations.set(rel.relatedUserId, new Set<string>());
    }
    existingRelations.get(rel.relatedUserId)?.add(rel.userId);
  }
  
  // Générer des suggestions de fratrie basées sur les parents communs
  const siblingsSuggestions = generateSiblingSuggestions(
    parentToChildrenMap, 
    existingRelations, 
    profilesMap
  );
  
  suggestions.push(...siblingsSuggestions);
  
  return suggestions;
}

/**
 * Build a map of parent IDs to their children IDs
 */
function buildParentChildrenMap(processedRelations: ProcessedRelation[]): Map<string, string[]> {
  const parentToChildrenMap = new Map<string, string[]>();
  
  for (const rel of processedRelations) {
    if (rel.relationType === 'father' || rel.relationType === 'mother') {
      // Relation parent vers enfant
      if (!parentToChildrenMap.has(rel.userId)) {
        parentToChildrenMap.set(rel.userId, []);
      }
      parentToChildrenMap.get(rel.userId)?.push(rel.relatedUserId);
    } else if (rel.relationType === 'son' || rel.relationType === 'daughter') {
      // Relation enfant vers parent
      if (!parentToChildrenMap.has(rel.relatedUserId)) {
        parentToChildrenMap.set(rel.relatedUserId, []);
      }
      parentToChildrenMap.get(rel.relatedUserId)?.push(rel.userId);
    }
  }
  
  return parentToChildrenMap;
}

/**
 * Generate suggestions for sibling relationships based on common parents
 */
function generateSiblingSuggestions(
  parentToChildrenMap: Map<string, string[]>,
  existingRelations: Map<string, Set<string>>,
  profilesMap: Map<string, ProfileWithId>
): RelationSuggestion[] {
  const suggestions: RelationSuggestion[] = [];
  
  for (const [parentId, childrenIds] of parentToChildrenMap.entries()) {
    if (childrenIds.length > 1) {
      console.log(`🔍 Parent ${parentId} a ${childrenIds.length} enfants - création de suggestions de fratrie`);
      
      // Pour chaque paire d'enfants
      for (let i = 0; i < childrenIds.length; i++) {
        for (let j = i + 1; j < childrenIds.length; j++) {
          const child1Id = childrenIds[i];
          const child2Id = childrenIds[j];
          
          // Check if they already have a relation in either direction
          const hasRelation = 
            (existingRelations.get(child1Id)?.has(child2Id)) || 
            (existingRelations.get(child2Id)?.has(child1Id));
            
          if (!hasRelation) {
            // Obtenir les profils pour déterminer le genre
            const child1Profile = profilesMap.get(child1Id);
            const child2Profile = profilesMap.get(child2Id);
            const parentProfile = profilesMap.get(parentId);
            
            if (child1Profile && child2Profile && parentProfile) {
              console.log(`🔄 Suggestion: ${child1Profile.first_name} et ${child2Profile.first_name} pourraient être frère/sœur`);
              
              // Déterminer le genre du parent pour la raison de la suggestion
              // CORRECTION: Utiliser le genre du parent suggéré, pas du parent existant
              const parentGender = parentProfile.gender || '';
              const parentRelation = parentGender === 'F' ? 'mère' : 'père';
              
              // Suggestion pour child1 -> child2
              // CORRECTION: Déterminer le type de relation basé sur le genre du parent suggéré
              const relationTypeForChild1 = parentGender === 'F' ? 'mother' : 'father';
              const relationTypeForChild2 = parentGender === 'F' ? 'mother' : 'father';
              
              suggestions.push({
                user_id: child1Id,
                suggested_user_id: parentId,
                suggested_relation_type: relationTypeForChild1,
                reason: `Vous partagez la même ${parentRelation} avec ${child2Profile.first_name} ${child2Profile.last_name}`,
                similarity_score: 0.95,
              });
              
              // Suggestion pour child2 -> child1
              suggestions.push({
                user_id: child2Id,
                suggested_user_id: parentId,
                suggested_relation_type: relationTypeForChild2,
                reason: `Vous partagez la même ${parentRelation} avec ${child1Profile.first_name} ${child1Profile.last_name}`,
                similarity_score: 0.95,
              });
            }
          }
        }
      }
    }
  }
  
  return suggestions;
}
