
/**
 * Represents a member of a family group
 */
export interface FamilyMember {
  id: string;
  userId: string;
  fullName: string;
  avatarUrl: string | null;
  relationLabel: string;
  email: string;
}

/**
 * Represents a group of family members
 */
export interface FamilyGroup {
  id: string;
  name: string;
  members: FamilyMember[];
}
