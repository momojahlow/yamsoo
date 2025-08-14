
export interface Suggestion {
  id: string;
  created_at: string;
  user_id: string;
  suggested_relation_code: string; // Code de la relation (ex: 'grandfather')
  suggested_relation_name: string; // Nom français de la relation (ex: 'Grand-père')
  suggested_relation_type?: string; // Deprecated - pour compatibilité
  target_id: string;
  suggested_user_id: string;
  status: string;
  target_name?: string;
  target_avatar_url?: string;
  reason?: string;
  has_pending_request?: boolean; // Indique si une demande de relation est en cours
  profiles?: {
    first_name: string | null;
    last_name: string | null;
    avatar_url: string | null;
    gender?: string | null;
  };
}
