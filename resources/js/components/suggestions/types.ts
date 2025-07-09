
export interface Suggestion {
  id: string;
  created_at: string;
  user_id: string;
  suggested_relation_type: string; // Keep as string for database compatibility
  target_id: string;
  suggested_user_id: string;
  status: string;
  target_name?: string;
  target_avatar_url?: string;
  reason?: string;
  profiles?: {
    first_name: string | null;
    last_name: string | null;
    avatar_url: string | null;
    gender?: string | null;
  };
}
