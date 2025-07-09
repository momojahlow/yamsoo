
import { useState } from "react";
import { Separator } from "@/components/ui/separator";
import { ScrollArea } from "@/components/ui/scroll-area";
import { SearchInput } from "./sidebar/SearchInput";
import { ChatList } from "./sidebar/ChatList";
import { GroupList } from "./sidebar/GroupList";
import { CreateGroupDialog } from "./sidebar/CreateGroupDialog";
import type { DatabaseProfile, ChatGroup } from "@/types/chat";

interface ChatSidebarProps {
  profiles: DatabaseProfile[];
  conversations: any[];
  isLoadingConversations: boolean;
  selectedConversation: DatabaseProfile | null;
  onSelectConversation: (profile: DatabaseProfile | null) => void;
  groups: ChatGroup[];
  currentUserId?: string;
  onCreateGroup: (name: string, description: string, memberIds: string[]) => Promise<void>;
  onAddGroupMember: (groupId: string, userId: string) => Promise<void>;
  onSelectGroup: (group: ChatGroup | null) => void;
  selectedGroup: ChatGroup | null;
  isCollapsed?: boolean;
}

export function ChatSidebar({
  profiles,
  conversations,
  isLoadingConversations,
  selectedConversation,
  onSelectConversation,
  groups,
  currentUserId,
  onCreateGroup,
  onAddGroupMember,
  onSelectGroup,
  selectedGroup,
  isCollapsed = false,
}: ChatSidebarProps) {
  const [searchQuery, setSearchQuery] = useState("");

  return (
    <aside className={`border-r flex flex-col h-full transition-all duration-300 ${isCollapsed ? 'w-20' : 'w-full'}`}>
      <div className="p-3">
        <SearchInput 
          value={searchQuery}
          onChange={setSearchQuery}
        />
      </div>
      
      <Separator />
      
      <ScrollArea className="flex-1">
        <div className="p-3 space-y-4">
          <div className="text-sm font-bold text-muted-foreground">
            {!isCollapsed && "Conversations"}
          </div>
          
          {isLoadingConversations ? (
            <div>{!isCollapsed && "Chargement des conversations..."}</div>
          ) : (
            <ChatList
              conversations={conversations}
              selectedConversation={selectedConversation}
              onSelectConversation={onSelectConversation}
              currentUserId={currentUserId}
              isCollapsed={isCollapsed}
            />
          )}
          
          <Separator />
          
          <div className="flex items-center justify-between">
            <div className="text-sm font-bold text-muted-foreground">
              {!isCollapsed && "Groupes"}
            </div>
            {!isCollapsed && (
              <CreateGroupDialog
                profiles={profiles}
                searchQuery={searchQuery}
                onCreateGroup={onCreateGroup}
              />
            )}
          </div>
          
          <GroupList
            groups={groups}
            onSelectGroup={onSelectGroup}
            selectedGroup={selectedGroup}
            onAddMember={onAddGroupMember}
            currentUserId={currentUserId}
            isCollapsed={isCollapsed}
          />
        </div>
      </ScrollArea>
    </aside>
  );
}
