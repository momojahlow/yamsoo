
// Types de relations acceptés par la base de données Supabase
export type DbFamilyRelationType =
  | 'father'
  | 'mother'
  | 'son'
  | 'daughter'
  | 'husband'
  | 'wife'
  | 'brother'
  | 'sister'
  | 'grandfather'
  | 'grandmother'
  | 'grandson'
  | 'granddaughter'
  | 'uncle'
  | 'aunt'
  | 'nephew'
  | 'niece'
  | 'cousin'
  | 'spouse'
  | 'stepfather'
  | 'stepmother'
  | 'stepson'
  | 'stepdaughter'
  | 'father_in_law'
  | 'mother_in_law'
  | 'son_in_law'
  | 'daughter_in_law'
  | 'brother_in_law'
  | 'sister_in_law'
  | 'half_brother_maternal'
  | 'half_brother_paternal'
  | 'half_sister_maternal'
  | 'half_sister_paternal'
  | 'nephew_brother'
  | 'niece_brother'
  | 'nephew_sister'
  | 'niece_sister'
  | 'cousin_paternal_m'
  | 'cousin_maternal_m'
  | 'cousin_paternal_f'
  | 'cousin_maternal_f'
  | 'uncle_paternal'
  | 'uncle_maternal'
  | 'aunt_paternal'
  | 'aunt_maternal';

// Types de relations étendus pour l'interface utilisateur
export type FamilyRelationType =
  | DbFamilyRelationType
  | 'boy'
  | 'baby'
  | 'child'
  | 'friend_m'
  | 'friend_f'
  | 'colleague'
  | 'sibling'
  | 'half_brother';

export type FamilyRelationStatus = 'pending' | 'accepted' | 'rejected';

export interface UserProfile {
  id: string;
  first_name: string;
  last_name: string;
  email: string;
  avatar_url?: string | null;
  gender?: string | null;
  birth_date?: string | null;
  mobile?: string | null;
}

export interface FamilyRelation {
  id: string;
  user_id: string;
  related_user_id: string;
  relation_type: DbFamilyRelationType;
  status: FamilyRelationStatus;
  created_at?: string;
  updated_at?: string;
  user_profile?: UserProfile;
  related_profile?: UserProfile;
  // Pour la compatibilité avec le code existant
  profiles?: UserProfile;
}
