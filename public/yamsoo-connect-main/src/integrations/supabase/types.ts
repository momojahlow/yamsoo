export type Json =
  | string
  | number
  | boolean
  | null
  | { [key: string]: Json | undefined }
  | Json[]

export type Database = {
  public: {
    Tables: {
      chat_group_members: {
        Row: {
          group_id: string
          id: string
          joined_at: string | null
          role: string | null
          user_id: string
        }
        Insert: {
          group_id: string
          id?: string
          joined_at?: string | null
          role?: string | null
          user_id: string
        }
        Update: {
          group_id?: string
          id?: string
          joined_at?: string | null
          role?: string | null
          user_id?: string
        }
        Relationships: [
          {
            foreignKeyName: "chat_group_members_group_id_fkey"
            columns: ["group_id"]
            isOneToOne: false
            referencedRelation: "chat_groups"
            referencedColumns: ["id"]
          },
        ]
      }
      chat_groups: {
        Row: {
          created_at: string | null
          created_by: string | null
          description: string | null
          id: string
          name: string
          updated_at: string | null
        }
        Insert: {
          created_at?: string | null
          created_by?: string | null
          description?: string | null
          id?: string
          name: string
          updated_at?: string | null
        }
        Update: {
          created_at?: string | null
          created_by?: string | null
          description?: string | null
          id?: string
          name?: string
          updated_at?: string | null
        }
        Relationships: []
      }
      family_relation_suggestions: {
        Row: {
          confidence_score: number | null
          created_at: string | null
          id: string
          reason: string | null
          status: string | null
          suggested_relation_type: string
          suggested_user_id: string
          updated_at: string | null
          user_id: string
        }
        Insert: {
          confidence_score?: number | null
          created_at?: string | null
          id?: string
          reason?: string | null
          status?: string | null
          suggested_relation_type: string
          suggested_user_id: string
          updated_at?: string | null
          user_id: string
        }
        Update: {
          confidence_score?: number | null
          created_at?: string | null
          id?: string
          reason?: string | null
          status?: string | null
          suggested_relation_type?: string
          suggested_user_id?: string
          updated_at?: string | null
          user_id?: string
        }
        Relationships: [
          {
            foreignKeyName: "family_relation_suggestions_suggested_user_id_fkey"
            columns: ["suggested_user_id"]
            isOneToOne: false
            referencedRelation: "profiles"
            referencedColumns: ["id"]
          },
          {
            foreignKeyName: "family_relation_suggestions_user_id_fkey"
            columns: ["user_id"]
            isOneToOne: false
            referencedRelation: "profiles"
            referencedColumns: ["id"]
          },
        ]
      }
      family_relations: {
        Row: {
          created_at: string | null
          id: string
          related_user_id: string
          relation_type: Database["public"]["Enums"]["family_relation_type"]
          status: Database["public"]["Enums"]["family_relation_status"] | null
          updated_at: string | null
          user_id: string
        }
        Insert: {
          created_at?: string | null
          id?: string
          related_user_id: string
          relation_type: Database["public"]["Enums"]["family_relation_type"]
          status?: Database["public"]["Enums"]["family_relation_status"] | null
          updated_at?: string | null
          user_id: string
        }
        Update: {
          created_at?: string | null
          id?: string
          related_user_id?: string
          relation_type?: Database["public"]["Enums"]["family_relation_type"]
          status?: Database["public"]["Enums"]["family_relation_status"] | null
          updated_at?: string | null
          user_id?: string
        }
        Relationships: [
          {
            foreignKeyName: "family_relations_related_user_id_fkey1"
            columns: ["related_user_id"]
            isOneToOne: false
            referencedRelation: "profiles"
            referencedColumns: ["id"]
          },
          {
            foreignKeyName: "family_relations_user_id_fkey1"
            columns: ["user_id"]
            isOneToOne: false
            referencedRelation: "profiles"
            referencedColumns: ["id"]
          },
        ]
      }
      manual_family_members: {
        Row: {
          avatar_url: string | null
          birth_date: string | null
          created_at: string
          first_name: string
          gender: string | null
          id: string
          last_name: string
          relation_type: string
          updated_at: string
          user_id: string
        }
        Insert: {
          avatar_url?: string | null
          birth_date?: string | null
          created_at?: string
          first_name: string
          gender?: string | null
          id?: string
          last_name: string
          relation_type: string
          updated_at?: string
          user_id: string
        }
        Update: {
          avatar_url?: string | null
          birth_date?: string | null
          created_at?: string
          first_name?: string
          gender?: string | null
          id?: string
          last_name?: string
          relation_type?: string
          updated_at?: string
          user_id?: string
        }
        Relationships: []
      }
      messages: {
        Row: {
          attachment_name: string | null
          attachment_url: string | null
          audio_duration: number | null
          audio_transcription: string | null
          audio_url: string | null
          content: string
          created_at: string | null
          id: string
          is_typing: boolean | null
          reactions: Json | null
          read_at: string | null
          receiver_id: string
          sender_id: string
          sender_profile_id: string | null
          updated_at: string | null
        }
        Insert: {
          attachment_name?: string | null
          attachment_url?: string | null
          audio_duration?: number | null
          audio_transcription?: string | null
          audio_url?: string | null
          content: string
          created_at?: string | null
          id?: string
          is_typing?: boolean | null
          reactions?: Json | null
          read_at?: string | null
          receiver_id: string
          sender_id: string
          sender_profile_id?: string | null
          updated_at?: string | null
        }
        Update: {
          attachment_name?: string | null
          attachment_url?: string | null
          audio_duration?: number | null
          audio_transcription?: string | null
          audio_url?: string | null
          content?: string
          created_at?: string | null
          id?: string
          is_typing?: boolean | null
          reactions?: Json | null
          read_at?: string | null
          receiver_id?: string
          sender_id?: string
          sender_profile_id?: string | null
          updated_at?: string | null
        }
        Relationships: [
          {
            foreignKeyName: "messages_sender_profile_id_fkey"
            columns: ["sender_profile_id"]
            isOneToOne: false
            referencedRelation: "profiles"
            referencedColumns: ["id"]
          },
        ]
      }
      profiles: {
        Row: {
          avatar_url: string | null
          birth_date: string | null
          created_at: string | null
          email: string | null
          first_name: string | null
          gender: string | null
          id: string
          last_name: string | null
          mobile: string | null
          updated_at: string | null
        }
        Insert: {
          avatar_url?: string | null
          birth_date?: string | null
          created_at?: string | null
          email?: string | null
          first_name?: string | null
          gender?: string | null
          id: string
          last_name?: string | null
          mobile?: string | null
          updated_at?: string | null
        }
        Update: {
          avatar_url?: string | null
          birth_date?: string | null
          created_at?: string | null
          email?: string | null
          first_name?: string | null
          gender?: string | null
          id?: string
          last_name?: string | null
          mobile?: string | null
          updated_at?: string | null
        }
        Relationships: []
      }
      relation_suggestions: {
        Row: {
          created_at: string | null
          id: string
          reason: string | null
          similarity_score: number | null
          status:
            | Database["public"]["Enums"]["relation_suggestion_status"]
            | null
          suggested_relation_type: Database["public"]["Enums"]["family_relation_type"]
          suggested_user_id: string
          updated_at: string | null
          user_id: string
        }
        Insert: {
          created_at?: string | null
          id?: string
          reason?: string | null
          similarity_score?: number | null
          status?:
            | Database["public"]["Enums"]["relation_suggestion_status"]
            | null
          suggested_relation_type: Database["public"]["Enums"]["family_relation_type"]
          suggested_user_id: string
          updated_at?: string | null
          user_id: string
        }
        Update: {
          created_at?: string | null
          id?: string
          reason?: string | null
          similarity_score?: number | null
          status?:
            | Database["public"]["Enums"]["relation_suggestion_status"]
            | null
          suggested_relation_type?: Database["public"]["Enums"]["family_relation_type"]
          suggested_user_id?: string
          updated_at?: string | null
          user_id?: string
        }
        Relationships: [
          {
            foreignKeyName: "relation_suggestions_suggested_user_id_fkey1"
            columns: ["suggested_user_id"]
            isOneToOne: false
            referencedRelation: "profiles"
            referencedColumns: ["id"]
          },
          {
            foreignKeyName: "relation_suggestions_user_id_fkey1"
            columns: ["user_id"]
            isOneToOne: false
            referencedRelation: "profiles"
            referencedColumns: ["id"]
          },
        ]
      }
      stories: {
        Row: {
          content: string
          created_at: string
          expires_at: string
          id: string
          is_active: boolean
          media_url: string | null
          reactions: Json | null
          user_id: string
        }
        Insert: {
          content: string
          created_at?: string
          expires_at?: string
          id?: string
          is_active?: boolean
          media_url?: string | null
          reactions?: Json | null
          user_id: string
        }
        Update: {
          content?: string
          created_at?: string
          expires_at?: string
          id?: string
          is_active?: boolean
          media_url?: string | null
          reactions?: Json | null
          user_id?: string
        }
        Relationships: []
      }
      story_comments: {
        Row: {
          content: string
          created_at: string | null
          id: string
          story_id: string
          user_id: string
        }
        Insert: {
          content: string
          created_at?: string | null
          id?: string
          story_id: string
          user_id: string
        }
        Update: {
          content?: string
          created_at?: string | null
          id?: string
          story_id?: string
          user_id?: string
        }
        Relationships: [
          {
            foreignKeyName: "fk_story_comments_story"
            columns: ["story_id"]
            isOneToOne: false
            referencedRelation: "stories"
            referencedColumns: ["id"]
          },
          {
            foreignKeyName: "story_comments_story_id_fkey"
            columns: ["story_id"]
            isOneToOne: false
            referencedRelation: "stories"
            referencedColumns: ["id"]
          },
        ]
      }
      user_roles: {
        Row: {
          created_at: string | null
          id: string
          role: Database["public"]["Enums"]["user_role"]
          updated_at: string | null
          user_id: string
        }
        Insert: {
          created_at?: string | null
          id?: string
          role: Database["public"]["Enums"]["user_role"]
          updated_at?: string | null
          user_id: string
        }
        Update: {
          created_at?: string | null
          id?: string
          role?: Database["public"]["Enums"]["user_role"]
          updated_at?: string | null
          user_id?: string
        }
        Relationships: []
      }
      user_status: {
        Row: {
          created_at: string | null
          id: string
          is_online: boolean | null
          last_activity: string | null
          updated_at: string | null
          user_id: string
        }
        Insert: {
          created_at?: string | null
          id?: string
          is_online?: boolean | null
          last_activity?: string | null
          updated_at?: string | null
          user_id: string
        }
        Update: {
          created_at?: string | null
          id?: string
          is_online?: boolean | null
          last_activity?: string | null
          updated_at?: string | null
          user_id?: string
        }
        Relationships: []
      }
      voice_messages: {
        Row: {
          audio_url: string
          created_at: string | null
          duration: number
          id: string
          message_id: string
          transcription: string | null
          updated_at: string | null
        }
        Insert: {
          audio_url: string
          created_at?: string | null
          duration: number
          id?: string
          message_id: string
          transcription?: string | null
          updated_at?: string | null
        }
        Update: {
          audio_url?: string
          created_at?: string | null
          duration?: number
          id?: string
          message_id?: string
          transcription?: string | null
          updated_at?: string | null
        }
        Relationships: [
          {
            foreignKeyName: "voice_messages_message_id_fkey"
            columns: ["message_id"]
            isOneToOne: false
            referencedRelation: "messages"
            referencedColumns: ["id"]
          },
        ]
      }
    }
    Views: {
      [_ in never]: never
    }
    Functions: {
      accept_or_reject_relation: {
        Args: { relation_id: string; new_status: string }
        Returns: Json
      }
      add_family_relation: {
        Args:
          | {
              p_user_id: string
              p_related_user_id: string
              p_relation_type: Database["public"]["Enums"]["family_relation_type"]
              p_status: Database["public"]["Enums"]["family_relation_status"]
            }
          | {
              p_user_id: string
              p_related_user_id: string
              p_relation_type: string
              p_status: Database["public"]["Enums"]["family_relation_status"]
            }
        Returns: undefined
      }
      check_email_exists: {
        Args: { email_to_check: string }
        Returns: boolean
      }
      delete_user_with_data: {
        Args: { user_id: string }
        Returns: undefined
      }
      find_all_family_paths: {
        Args: { target_user_id: string }
        Returns: {
          path_members: string[]
          path_relations: string[]
          total_depth: number
        }[]
      }
      find_closest_family_relation: {
        Args: { target_user_id: string }
        Returns: {
          relation_type: string
          confidence: number
          reason: string
        }[]
      }
      find_direct_family_relation: {
        Args: { target_user_id: string }
        Returns: {
          relation_type: string
          is_inverse: boolean
          path_depth: number
          intermediate_user_id: string
        }[]
      }
      generate_family_relation_suggestions: {
        Args: Record<PropertyKey, never>
        Returns: undefined
      }
      has_role: {
        Args: { requested_role: Database["public"]["Enums"]["user_role"] }
        Returns: boolean
      }
      update_family_relation_status: {
        Args: { relation_id: string; new_status: string }
        Returns: Json
      }
      update_relation_suggestion_status: {
        Args: { suggestion_id: string; new_status: string }
        Returns: Json
      }
    }
    Enums: {
      family_relation_status: "pending" | "accepted" | "rejected" | "manual"
      family_relation_type:
        | "father"
        | "mother"
        | "son"
        | "daughter"
        | "husband"
        | "wife"
        | "brother"
        | "sister"
        | "grandfather"
        | "grandmother"
        | "grandson"
        | "granddaughter"
        | "uncle"
        | "aunt"
        | "nephew"
        | "niece"
        | "cousin"
        | "spouse"
        | "stepfather"
        | "stepmother"
        | "stepson"
        | "stepdaughter"
        | "half_brother_maternal"
        | "half_brother_paternal"
        | "half_sister_maternal"
        | "half_sister_paternal"
        | "nephew_brother"
        | "niece_brother"
        | "nephew_sister"
        | "niece_sister"
        | "cousin_paternal_m"
        | "cousin_maternal_m"
        | "cousin_paternal_f"
        | "cousin_maternal_f"
      relation_suggestion_status:
        | "pending"
        | "accepted"
        | "rejected"
        | "ignored"
      user_role: "user" | "admin" | "super_admin"
    }
    CompositeTypes: {
      [_ in never]: never
    }
  }
}

type DefaultSchema = Database[Extract<keyof Database, "public">]

export type Tables<
  DefaultSchemaTableNameOrOptions extends
    | keyof (DefaultSchema["Tables"] & DefaultSchema["Views"])
    | { schema: keyof Database },
  TableName extends DefaultSchemaTableNameOrOptions extends {
    schema: keyof Database
  }
    ? keyof (Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"] &
        Database[DefaultSchemaTableNameOrOptions["schema"]]["Views"])
    : never = never,
> = DefaultSchemaTableNameOrOptions extends { schema: keyof Database }
  ? (Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"] &
      Database[DefaultSchemaTableNameOrOptions["schema"]]["Views"])[TableName] extends {
      Row: infer R
    }
    ? R
    : never
  : DefaultSchemaTableNameOrOptions extends keyof (DefaultSchema["Tables"] &
        DefaultSchema["Views"])
    ? (DefaultSchema["Tables"] &
        DefaultSchema["Views"])[DefaultSchemaTableNameOrOptions] extends {
        Row: infer R
      }
      ? R
      : never
    : never

export type TablesInsert<
  DefaultSchemaTableNameOrOptions extends
    | keyof DefaultSchema["Tables"]
    | { schema: keyof Database },
  TableName extends DefaultSchemaTableNameOrOptions extends {
    schema: keyof Database
  }
    ? keyof Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"]
    : never = never,
> = DefaultSchemaTableNameOrOptions extends { schema: keyof Database }
  ? Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"][TableName] extends {
      Insert: infer I
    }
    ? I
    : never
  : DefaultSchemaTableNameOrOptions extends keyof DefaultSchema["Tables"]
    ? DefaultSchema["Tables"][DefaultSchemaTableNameOrOptions] extends {
        Insert: infer I
      }
      ? I
      : never
    : never

export type TablesUpdate<
  DefaultSchemaTableNameOrOptions extends
    | keyof DefaultSchema["Tables"]
    | { schema: keyof Database },
  TableName extends DefaultSchemaTableNameOrOptions extends {
    schema: keyof Database
  }
    ? keyof Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"]
    : never = never,
> = DefaultSchemaTableNameOrOptions extends { schema: keyof Database }
  ? Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"][TableName] extends {
      Update: infer U
    }
    ? U
    : never
  : DefaultSchemaTableNameOrOptions extends keyof DefaultSchema["Tables"]
    ? DefaultSchema["Tables"][DefaultSchemaTableNameOrOptions] extends {
        Update: infer U
      }
      ? U
      : never
    : never

export type Enums<
  DefaultSchemaEnumNameOrOptions extends
    | keyof DefaultSchema["Enums"]
    | { schema: keyof Database },
  EnumName extends DefaultSchemaEnumNameOrOptions extends {
    schema: keyof Database
  }
    ? keyof Database[DefaultSchemaEnumNameOrOptions["schema"]]["Enums"]
    : never = never,
> = DefaultSchemaEnumNameOrOptions extends { schema: keyof Database }
  ? Database[DefaultSchemaEnumNameOrOptions["schema"]]["Enums"][EnumName]
  : DefaultSchemaEnumNameOrOptions extends keyof DefaultSchema["Enums"]
    ? DefaultSchema["Enums"][DefaultSchemaEnumNameOrOptions]
    : never

export type CompositeTypes<
  PublicCompositeTypeNameOrOptions extends
    | keyof DefaultSchema["CompositeTypes"]
    | { schema: keyof Database },
  CompositeTypeName extends PublicCompositeTypeNameOrOptions extends {
    schema: keyof Database
  }
    ? keyof Database[PublicCompositeTypeNameOrOptions["schema"]]["CompositeTypes"]
    : never = never,
> = PublicCompositeTypeNameOrOptions extends { schema: keyof Database }
  ? Database[PublicCompositeTypeNameOrOptions["schema"]]["CompositeTypes"][CompositeTypeName]
  : PublicCompositeTypeNameOrOptions extends keyof DefaultSchema["CompositeTypes"]
    ? DefaultSchema["CompositeTypes"][PublicCompositeTypeNameOrOptions]
    : never

export const Constants = {
  public: {
    Enums: {
      family_relation_status: ["pending", "accepted", "rejected", "manual"],
      family_relation_type: [
        "father",
        "mother",
        "son",
        "daughter",
        "husband",
        "wife",
        "brother",
        "sister",
        "grandfather",
        "grandmother",
        "grandson",
        "granddaughter",
        "uncle",
        "aunt",
        "nephew",
        "niece",
        "cousin",
        "spouse",
        "stepfather",
        "stepmother",
        "stepson",
        "stepdaughter",
        "half_brother_maternal",
        "half_brother_paternal",
        "half_sister_maternal",
        "half_sister_paternal",
        "nephew_brother",
        "niece_brother",
        "nephew_sister",
        "niece_sister",
        "cousin_paternal_m",
        "cousin_maternal_m",
        "cousin_paternal_f",
        "cousin_maternal_f",
      ],
      relation_suggestion_status: [
        "pending",
        "accepted",
        "rejected",
        "ignored",
      ],
      user_role: ["user", "admin", "super_admin"],
    },
  },
} as const
