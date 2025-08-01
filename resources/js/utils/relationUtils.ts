
import { FamilyRelationType } from "@/types/family";

/**
 * Returns a user-friendly label for a family relation type
 */
export const getRelationLabel = (relationType: string): string => {
  const relationLabels: Record<string, string> = {
    // Parents/Enfants
    'father': 'Père',
    'mother': 'Mère',
    'son': 'Fils',
    'daughter': 'Fille',
    'child': 'Enfant',
    'baby': 'Bébé',
    'boy': 'Garçon',

    // Conjoints
    'husband': 'Mari',
    'wife': 'Épouse',
    'spouse': 'Conjoint(e)',

    // Fratrie
    'brother': 'Frère',
    'sister': 'Sœur',
    'sibling': 'Frère/Sœur',
    'half_brother': 'Demi-frère',
    'half_brother_paternal': 'Demi-frère paternel',
    'half_brother_maternal': 'Demi-frère maternel',
    'half_sister_paternal': 'Demi-sœur paternelle',
    'half_sister_maternal': 'Demi-sœur maternelle',

    // Grands-parents/Petits-enfants
    'grandfather': 'Grand-père',
    'grandmother': 'Grand-mère',
    'grandson': 'Petit-fils',
    'granddaughter': 'Petite-fille',
    'grandparent': 'Grand-parent',
    'grandchild': 'Petit-enfant',

    // Oncles/Tantes/Neveux/Nièces
    'uncle': 'Oncle',
    'aunt': 'Tante',
    'nephew': 'Neveu',
    'niece': 'Nièce',

    // Relations par alliance
    'father_in_law': 'Beau-père',
    'mother_in_law': 'Belle-mère',
    'son_in_law': 'Gendre',
    'daughter_in_law': 'Belle-fille',
    'brother_in_law': 'Beau-frère',
    'sister_in_law': 'Belle-sœur',

    // Autres
    'cousin': 'Cousin(e)',
    'family_member': 'Membre de la famille',
    
    // Grands-parents/Petits-enfants
    'grandfather': 'Grand-père',
    'grandmother': 'Grand-mère',
    'grandson': 'Petit-fils',
    'granddaughter': 'Petite-fille',
    
    // Oncles/Tantes
    'uncle': 'Oncle',
    'uncle_paternal': 'Oncle paternel',
    'uncle_maternal': 'Oncle maternel',
    'aunt': 'Tante',
    'aunt_paternal': 'Tante paternelle',
    'aunt_maternal': 'Tante maternelle',
    
    // Neveux/Nièces
    'nephew': 'Neveu',
    'niece': 'Nièce',
    'nephew_brother': 'Neveu (frère)',
    'niece_brother': 'Nièce (frère)',
    'nephew_sister': 'Neveu (sœur)',
    'niece_sister': 'Nièce (sœur)',
    
    // Cousins
    'cousin': 'Cousin(e)',
    'cousin_paternal_m': 'Cousin paternel',
    'cousin_maternal_m': 'Cousin maternel',
    'cousin_paternal_f': 'Cousine paternelle',
    'cousin_maternal_f': 'Cousine maternelle',
    
    // Belle-famille
    'father_in_law': 'Beau-père',
    'mother_in_law': 'Belle-mère',
    'son_in_law': 'Gendre',
    'daughter_in_law': 'Belle-fille',
    'brother_in_law': 'Beau-frère',
    'sister_in_law': 'Belle-sœur',
    'stepfather': 'Beau-père',
    'stepmother': 'Belle-mère',
    'stepson': 'Beau-fils',
    'stepdaughter': 'Belle-fille',
    'stepbrother': 'Demi-frère',
    'stepsister': 'Demi-sœur',
    
    // Autres
    'friend_m': 'Ami',
    'friend_f': 'Amie',
    'colleague': 'Collègue',
    'other': 'Autre'
  };
  
  return relationLabels[relationType] || relationType;
};

/**
 * Returns the opposite relation type
 */
export const getOppositeRelation = (relationType: FamilyRelationType, targetGender?: string | null): FamilyRelationType => {
  const oppositeRelations: Record<string, Record<string, FamilyRelationType>> = {
    'father': { M: 'son', F: 'daughter', default: 'child' },
    'mother': { M: 'son', F: 'daughter', default: 'child' },
    'son': { M: 'father', F: 'mother', default: 'father' },
    'daughter': { M: 'father', F: 'mother', default: 'father' },
    'brother': { M: 'brother', F: 'sister', default: 'sibling' },
    'sister': { M: 'brother', F: 'sister', default: 'sibling' },
    'husband': { M: 'husband', F: 'wife', default: 'spouse' },
    'wife': { M: 'husband', F: 'wife', default: 'spouse' },
    'uncle': { M: 'nephew', F: 'niece', default: 'nephew' },
    'aunt': { M: 'nephew', F: 'niece', default: 'nephew' },
    'uncle_paternal': { M: 'nephew', F: 'niece', default: 'nephew' },
    'uncle_maternal': { M: 'nephew', F: 'niece', default: 'nephew' },
    'aunt_paternal': { M: 'nephew', F: 'niece', default: 'nephew' },
    'aunt_maternal': { M: 'nephew', F: 'niece', default: 'nephew' },
    'nephew': { M: 'uncle', F: 'aunt', default: 'uncle' },
    'niece': { M: 'uncle', F: 'aunt', default: 'uncle' },
    'cousin': { M: 'cousin', F: 'cousin', default: 'cousin' },
  };
  
  if (relationType in oppositeRelations) {
    const genderKey = targetGender || 'default';
    return oppositeRelations[relationType][genderKey] || oppositeRelations[relationType]['default'];
  }
  
  // Return the same relation type if no opposite is defined
  return relationType;
};

/**
 * Returns the inverse relation type (when viewed from the other person's perspective)
 * FIXED: Using proper relation types and improved logic
 */
export const getInverseRelation = (relationType: FamilyRelationType): FamilyRelationType => {
  const inverseRelations: Record<string, FamilyRelationType> = {
    // Parents/Enfants - using 'child' instead of 'parent' which doesn't exist in FamilyRelationType
    'father': 'child',
    'mother': 'child', 
    'son': 'father',
    'daughter': 'mother',
    'child': 'father', // Par défaut on retourne father, sera adapté par genre après
    
    // Conjoints
    'husband': 'wife',
    'wife': 'husband',
    'spouse': 'spouse',
    
    // Fratrie
    'brother': 'sibling',
    'sister': 'sibling', 
    'sibling': 'sibling',
    'half_brother': 'half_brother',
    'half_brother_paternal': 'half_brother_paternal',
    'half_brother_maternal': 'half_brother_maternal',
    'half_sister_paternal': 'half_sister_paternal',
    'half_sister_maternal': 'half_sister_maternal',
    
    // Grands-parents/Petits-enfants
    'grandfather': 'grandson',
    'grandmother': 'granddaughter',
    'grandson': 'grandfather',
    'granddaughter': 'grandmother',
    
    // Oncles/Tantes et Neveux/Nièces
    'uncle': 'nephew',
    'uncle_paternal': 'nephew',
    'uncle_maternal': 'nephew',
    'aunt': 'niece',
    'aunt_paternal': 'niece',
    'aunt_maternal': 'niece',
    'nephew': 'uncle',
    'niece': 'aunt',
    
    // Cousins
    'cousin': 'cousin',
    'cousin_paternal_m': 'cousin',
    'cousin_maternal_m': 'cousin',
    'cousin_paternal_f': 'cousin',
    'cousin_maternal_f': 'cousin',
    
    // Belle-famille
    'father_in_law': 'son_in_law',
    'mother_in_law': 'daughter_in_law',
    'son_in_law': 'father_in_law',
    'daughter_in_law': 'mother_in_law',
    'brother_in_law': 'brother_in_law',
    'sister_in_law': 'sister_in_law',
  };
  
  return inverseRelations[relationType] || relationType;
};

/**
 * Adapts a relation type to match the gender of the target person
 * IMPROVED: Better handling of gender-specific relations
 */
export const adaptRelationToGender = (relationType: FamilyRelationType, targetGender: string): FamilyRelationType => {
  if (!targetGender || (targetGender !== 'M' && targetGender !== 'F')) {
    return relationType;
  }
  
  const genderMap: Record<string, Record<string, FamilyRelationType>> = {
    'child': { M: 'son', F: 'daughter' },
    'sibling': { M: 'brother', F: 'sister' },
    'spouse': { M: 'husband', F: 'wife' },
    'cousin': { M: 'cousin_paternal_m', F: 'cousin_paternal_f' },
    'friend': { M: 'friend_m', F: 'friend_f' },
    // Additional mappings for better coverage
    'uncle': { M: 'uncle', F: 'aunt' },
    'aunt': { M: 'uncle', F: 'aunt' },
    'nephew': { M: 'nephew', F: 'niece' },
    'niece': { M: 'nephew', F: 'niece' },
  };
  
  if (relationType in genderMap) {
    return genderMap[relationType][targetGender] || relationType;
  }
  
  return relationType;
};

/**
 * Determines the gender from a relation type
 */
export const genderFromRelationType = (relationType: FamilyRelationType): string | null => {
  const maleTypes = [
    'father', 'son', 'brother', 'grandfather', 'grandson', 'uncle', 
    'nephew', 'husband', 'stepfather', 'stepson', 'stepbrother',
    'father_in_law', 'son_in_law', 'brother_in_law', 'half_brother',
    'half_brother_paternal', 'half_brother_maternal', 'uncle_paternal', 
    'uncle_maternal', 'cousin_paternal_m', 'cousin_maternal_m', 'friend_m'
  ];
  
  const femaleTypes = [
    'mother', 'daughter', 'sister', 'grandmother', 'granddaughter', 'aunt',
    'niece', 'wife', 'stepmother', 'stepdaughter', 'stepsister',
    'mother_in_law', 'daughter_in_law', 'sister_in_law', 'half_sister_paternal', 
    'half_sister_maternal', 'aunt_paternal', 'aunt_maternal', 'cousin_paternal_f', 
    'cousin_maternal_f', 'friend_f'
  ];
  
  if (maleTypes.includes(relationType)) {
    return 'M';
  } else if (femaleTypes.includes(relationType)) {
    return 'F';
  }
  
  return null;
};

// Export FamilyRelationType from this file for components that import it from here
export type { FamilyRelationType };
