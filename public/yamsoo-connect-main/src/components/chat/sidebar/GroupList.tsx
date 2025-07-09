
import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Settings } from "lucide-react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { AddMemberDialog } from "./AddMemberDialog";
import type { ChatGroup } from "@/types/chat";

interface GroupListProps {
  groups: ChatGroup[];
  onSelectGroup: (group: ChatGroup | null) => void;
  selectedGroup: ChatGroup | null;
  onAddMember: (groupId: string, userId: string) => Promise<void>;
  currentUserId?: string;
  isCollapsed?: boolean;
}

export function GroupList({
  groups,
  onSelectGroup,
  selectedGroup,
  onAddMember,
  currentUserId,
  isCollapsed = false,
}: GroupListProps) {
  const [isAddMemberDialogOpen, setIsAddMemberDialogOpen] = useState(false);
  const [selectedGroupId, setSelectedGroupId] = useState<string | null>(null);
  const [selectedGroupName, setSelectedGroupName] = useState("");

  const openAddMemberDialog = (group: ChatGroup) => {
    setSelectedGroupId(group.id);
    setSelectedGroupName(group.name);
    setIsAddMemberDialogOpen(true);
  };

  return (
    <div className="space-y-2">
      {groups.map(group => (
        <div key={group.id}>
          <Button
            variant="ghost"
            className={`w-full justify-start rounded-md ${
              selectedGroup?.id === group.id ? "bg-secondary" : ""
            }`}
            onClick={() => onSelectGroup(group)}
          >
            {!isCollapsed ? (
              group.name
            ) : (
              group.name.charAt(0)
            )}
          </Button>
          {!isCollapsed && currentUserId === group.created_by && (
            <Button
              variant="ghost"
              size="sm"
              onClick={() => openAddMemberDialog(group)}
            >
              Ajouter un membre
            </Button>
          )}
        </div>
      ))}

      <AddMemberDialog
        isOpen={isAddMemberDialogOpen}
        onOpenChange={setIsAddMemberDialogOpen}
        groupId={selectedGroupId}
        groupName={selectedGroupName}
        onAddMember={onAddMember}
      />
    </div>
  );
}
