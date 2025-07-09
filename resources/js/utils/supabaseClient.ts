
import { createClient } from '@supabase/supabase-js';
import { supabase } from '@/integrations/supabase/client';
import { FamilyRelationStatus } from '@/types/family';

// Fix TypeScript error by adding the correct function signature
export const getSupabaseClient = () => {
  return supabase;
};

// Create a type-safe RPC client for all database functions
export const supabaseRpc = {
  // Add the correct type for update_family_relation_status
  update_family_relation_status: async (params: { 
    relation_id: string; 
    new_status: FamilyRelationStatus 
  }) => {
    return supabase.rpc('update_family_relation_status', params);
  },
  
  // Add the correct type for find_all_family_paths
  find_all_family_paths: async (params: { 
    target_user_id: string 
  }) => {
    return supabase.rpc('find_all_family_paths', params);
  },
  
  // Add email existence check function
  check_email_exists: async (params: {
    email_to_check: string
  }) => {
    return supabase.rpc('check_email_exists', params);
  }
};

// Add the correct type for add_family_relation_by_text
export const addFamilyRelationByText = async (
  relationText: string,
  userId: string,
  profileId: string
) => {
  const { data, error } = await supabase.rpc(
    'add_family_relation',  // Using the correct function name that exists in the backend
    { 
      p_relation_type: relationText,
      p_user_id: userId,
      p_related_user_id: profileId,
      p_status: 'pending' as FamilyRelationStatus
    }
  );
  
  return { data, error };
};

// Add the correct type for has_role
export const hasRole = async (role: string) => {
  const { data, error } = await supabase.rpc(
    'has_role',
    { 
      requested_role: role as "user" | "admin" | "super_admin"
    }
  );
  
  return { data, error };
};
