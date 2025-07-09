
import { getSupabaseClient } from "@/utils/supabaseClient";

export type DatabaseProfile = {
  id: string;
  avatar_url?: string | null;
  first_name?: string | null;
  last_name?: string | null;
  email: string;
  birth_date?: string | null;
  gender?: string | null;
  mobile?: string | null;
  created_at: string;
  updated_at: string;
};

export type MessageProfile = {
  id: string;
  avatar_url?: string | null;
  first_name?: string | null;
  last_name?: string | null;
  email: string;
  birth_date?: string | null;
  gender?: string | null;
  mobile?: string | null;
  created_at: string;
  updated_at: string;
};

export type Message = {
  id: string;
  content: string;
  sender_id: string;
  receiver_id: string;
  read_at?: string | null;
  is_typing?: boolean;
  attachment_url?: string | null;
  attachment_name?: string | null;
  audio_url?: string | null;
  audio_duration?: number | null;
  audio_transcription?: string | null;
  created_at: string;
  updated_at: string;
  sender_profile?: MessageProfile | null;
  reactions?: Record<string, string[]>;
};

export type VoiceMessage = {
  id: string;
  message_id: string;
  audio_url: string;
  duration: number;
  transcription?: string;
  created_at: string;
  updated_at: string;
};

export type Reaction = {
  emoji: string;
  users: string[];
};

export type ChatGroup = {
  id: string;
  name: string;
  description?: string;
  created_by: string;
  created_at: string;
  updated_at: string;
  members?: MessageProfile[];
};

export type ChatGroupMember = {
  group_id: string;
  user_id: string;
  role: 'admin' | 'member';
  joined_at: string;
};

// Helper to query tables through the typed supabase client
const supabaseClient = getSupabaseClient();
