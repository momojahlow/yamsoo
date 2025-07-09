
import { FamilyRelationType, DbFamilyRelationType } from "@/types/family";

// Liste des types de relations valides dans la base de données Supabase
export const VALID_DB_RELATION_TYPES: DbFamilyRelationType[] = [
  'father', 'mother', 'son', 'daughter', 'husband', 'wife', 
  'brother', 'sister', 'grandfather', 'grandmother', 'grandson', 
  'granddaughter', 'uncle', 'aunt', 'nephew', 'niece',
  'cousin', 'spouse', 'stepfather', 'stepmother', 'stepson', 'stepdaughter',
  'father_in_law', 'mother_in_law', 'son_in_law', 'daughter_in_law', 
  'brother_in_law', 'sister_in_law', 'half_brother_maternal', 'half_brother_paternal',
  'half_sister_maternal', 'half_sister_paternal', 'nephew_brother', 'niece_brother',
  'nephew_sister', 'niece_sister', 'cousin_paternal_m', 'cousin_maternal_m',
  'cousin_paternal_f', 'cousin_maternal_f', 'uncle_paternal', 'uncle_maternal',
  'aunt_paternal', 'aunt_maternal'
];

// Fonction pour vérifier si un type de relation est valide selon l'interface utilisateur
export const isValidRelationType = (type: string): boolean => {
  // Liste des types de relations valides dans l'interface utilisateur
  const validTypes = [
    ...VALID_DB_RELATION_TYPES,
    'boy', 'baby', 'child', 'friend_m', 'friend_f', 
    'colleague', 'sibling', 'half_brother'
  ];
  
  return validTypes.includes(type);
};

// Fonction pour vérifier si un type de relation est valide dans la DB
export const isValidDbRelationType = (type: string): type is DbFamilyRelationType => {
  return VALID_DB_RELATION_TYPES.includes(type as DbFamilyRelationType);
};

// Fonction pour convertir un type de relation utilisateur en type valide pour la DB
export const getValidDbRelationType = (type: FamilyRelationType): string => {
  // Si le type est déjà valide pour la DB, le retourner
  if (isValidDbRelationType(type)) {
    return type;
  }
  
  // Pour les types UI spécifiques qui n'existent pas en DB, faire une conversion
  const typeMapping: Record<string, DbFamilyRelationType> = {
    'boy': 'son',
    'baby': 'son',
    'child': 'son',
    'friend_m': 'brother',
    'friend_f': 'sister',
    'colleague': 'brother',
    'sibling': 'brother',
    'half_brother': 'half_brother_paternal' // Par défaut on considère paternel
  };
  
  // Vérification dans la table de correspondance
  const mappedType = typeMapping[type];
  if (mappedType) {
    return mappedType;
  }
  
  // En dernier recours, type par défaut
  console.warn(`Type de relation non reconnu: ${type}, conversion par défaut vers 'brother'`);
  return 'brother';
};
