
/**
 * Safely extracts profile data from potentially erroneous Supabase responses
 */
export const safeProfileData = (profileData: any) => {
  // If profileData is null, undefined, or has an error property, return defaults
  if (!profileData || profileData.error) {
    return {
      id: null,
      first_name: null,
      last_name: null,
      avatar_url: null,
      gender: null,
      email: null,
      birth_date: null,
      mobile: null,
      created_at: '',
      updated_at: ''
    };
  }
  
  // Otherwise return the valid profile data
  return {
    id: profileData.id || null,
    first_name: profileData.first_name || null,
    last_name: profileData.last_name || null,
    avatar_url: profileData.avatar_url || null,
    gender: profileData.gender || null,
    email: profileData.email || null,
    birth_date: profileData.birth_date || null,
    mobile: profileData.mobile || null,
    created_at: profileData.created_at || '',
    updated_at: profileData.updated_at || ''
  };
};
