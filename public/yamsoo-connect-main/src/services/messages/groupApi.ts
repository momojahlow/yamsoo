import { supabase } from "@/integrations/supabase/client";
import { safeProfileData } from "@/utils/profileUtils";

export type ChatGroupMember = {
  id: string;
  avatar_url: string | null;
  first_name: string;
  last_name: string;
  email: string;
  birth_date: string | null;
  gender: string | null;
  mobile: string | null;
  created_at: string;
  updated_at: string;
  role: string | null;
  joined_at: string;
};

export type ChatGroup = {
  id: string;
  name: string;
  description: string | null;
  created_at: string;
  created_by: string | null;
  updated_at: string;
  members?: ChatGroupMember[];
};

export const getGroupsByUserId = async (userId: string): Promise<ChatGroup[]> => {
  try {
    const { data, error } = await supabase
      .from("chat_group_members")
      .select(`
        group_id,
        role,
        joined_at,
        groups:chat_groups!chat_group_members_group_id_fkey (
          id,
          name,
          description,
          created_at,
          created_by,
          updated_at
        )
      `)
      .eq("user_id", userId);

    if (error) throw error;

    if (!data || data.length === 0) return [];

    const groups = data.map((item) => ({
      id: item.groups?.id || '',
      name: item.groups?.name || '',
      description: item.groups?.description,
      created_at: item.groups?.created_at || '',
      created_by: item.groups?.created_by,
      updated_at: item.groups?.updated_at || '',
    }));

    // Deduplicate groups in case user belongs to same group with different roles
    return groups.filter(
      (group, index, self) =>
        index === self.findIndex((g) => g.id === group.id)
    );
  } catch (error) {
    console.error("Error fetching groups:", error);
    throw error;
  }
};

export const getGroupById = async (
  groupId: string,
  includeMembers = false
): Promise<ChatGroup | null> => {
  try {
    const { data, error } = await supabase
      .from("chat_groups")
      .select("*")
      .eq("id", groupId)
      .single();

    if (error) throw error;
    if (!data) return null;

    const group: ChatGroup = {
      id: data.id,
      name: data.name,
      description: data.description,
      created_at: data.created_at,
      created_by: data.created_by,
      updated_at: data.updated_at,
    };

    if (includeMembers) {
      const { data: membersData, error: membersError } = await supabase
        .from("chat_group_members")
        .select(`
          role,
          joined_at,
          user_id,
          profiles:profiles!chat_group_members_user_id_fkey (
            id,
            avatar_url,
            first_name,
            last_name,
            email,
            birth_date,
            gender,
            mobile,
            created_at,
            updated_at
          )
        `)
        .eq("group_id", groupId);

      if (membersError) throw membersError;

      // Process members data with safe profile handling
      group.members = (membersData || []).map((member) => {
        const safeProfile = safeProfileData(member.profiles);
        return {
          id: safeProfile.id || member.user_id,
          avatar_url: safeProfile.avatar_url,
          first_name: safeProfile.first_name || '',
          last_name: safeProfile.last_name || '',
          email: safeProfile.email || '',
          birth_date: safeProfile.birth_date,
          gender: safeProfile.gender,
          mobile: safeProfile.mobile,
          created_at: safeProfile.created_at || '',
          updated_at: safeProfile.updated_at || '',
          role: member.role,
          joined_at: member.joined_at,
        };
      });
    }

    return group;
  } catch (error) {
    console.error("Error fetching group:", error);
    throw error;
  }
};

export const createGroup = async (
  name: string,
  description: string | null,
  members: string[],
  createdBy: string
): Promise<{ id: string } | null> => {
  try {
    // Create group
    const { data: groupData, error: groupError } = await supabase
      .from("chat_groups")
      .insert({
        name,
        description,
        created_by: createdBy,
      })
      .select("id")
      .single();

    if (groupError) throw groupError;

    // Add members to group
    const membersToAdd = [
      // Include the creator
      {
        group_id: groupData.id,
        user_id: createdBy,
        role: "admin",
      },
      // Include other members
      ...members
        .filter((memberId) => memberId !== createdBy)
        .map((memberId) => ({
          group_id: groupData.id,
          user_id: memberId,
          role: "member",
        })),
    ];

    const { error: membersError } = await supabase
      .from("chat_group_members")
      .insert(membersToAdd);

    if (membersError) throw membersError;

    return { id: groupData.id };
  } catch (error) {
    console.error("Error creating group:", error);
    throw error;
  }
};

export const addMemberToGroup = async (
  groupId: string,
  userId: string,
  role = "member"
): Promise<boolean> => {
  try {
    const { error } = await supabase.from("chat_group_members").insert({
      group_id: groupId,
      user_id: userId,
      role,
    });

    if (error) throw error;
    return true;
  } catch (error) {
    console.error("Error adding member to group:", error);
    return false;
  }
};

export const removeMemberFromGroup = async (
  groupId: string,
  userId: string
): Promise<boolean> => {
  try {
    const { error } = await supabase
      .from("chat_group_members")
      .delete()
      .eq("group_id", groupId)
      .eq("user_id", userId);

    if (error) throw error;
    return true;
  } catch (error) {
    console.error("Error removing member from group:", error);
    return false;
  }
};

export const updateGroup = async (
  groupId: string,
  updates: {
    name?: string;
    description?: string | null;
  }
): Promise<boolean> => {
  try {
    const { error } = await supabase
      .from("chat_groups")
      .update(updates)
      .eq("id", groupId);

    if (error) throw error;
    return true;
  } catch (error) {
    console.error("Error updating group:", error);
    return false;
  }
};

export const deleteGroup = async (groupId: string): Promise<boolean> => {
  try {
    // First, delete all members
    const { error: membersError } = await supabase
      .from("chat_group_members")
      .delete()
      .eq("group_id", groupId);

    if (membersError) throw membersError;

    // Then, delete the group
    const { error: groupError } = await supabase
      .from("chat_groups")
      .delete()
      .eq("id", groupId);

    if (groupError) throw groupError;

    return true;
  } catch (error) {
    console.error("Error deleting group:", error);
    return false;
  }
};
