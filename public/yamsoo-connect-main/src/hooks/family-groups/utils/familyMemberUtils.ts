
import { FamilyMember } from "../../types/familyGroups";
import { getRelationLabel } from "@/utils/relationUtils";
import { safeProfileData } from "@/utils/profileUtils";

/**
 * Transforms a profile into a family member object
 */
export const createFamilyMember = (
  profile: any,
  relationLabel: string
): FamilyMember => {
  if (!profile) return {} as FamilyMember;
  
  const safeProfile = safeProfileData(profile);
  
  return {
    id: safeProfile.id,
    userId: safeProfile.id,
    fullName: `${safeProfile.first_name} ${safeProfile.last_name}`,
    avatarUrl: safeProfile.avatar_url,
    relationLabel,
    email: safeProfile.email || ''
  };
};
