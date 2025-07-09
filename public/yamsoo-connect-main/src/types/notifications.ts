
// Type définissant la structure d'un profil utilisateur
export type Profile = {
  id: string;
  first_name: string | null;
  last_name: string | null;
  avatar_url?: string | null;
  gender?: string | null;
  email?: string | null;
};

export type Notification = {
  id: string;
  created_at: string;
  type: 'relation' | 'message' | 'suggestion';
  message: string;
  read: boolean;
  sender?: Profile | null;
  relation_type?: string;
  originalId?: string;
  reason?: string;
  isFromCurrentUser?: boolean;
};
