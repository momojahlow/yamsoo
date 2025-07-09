
import { Button } from "@/components/ui/button";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import type { DatabaseProfile } from "@/types/chat";

interface ChatListProps {
  conversations: any[];
  selectedConversation: DatabaseProfile | null;
  onSelectConversation: (profile: DatabaseProfile | null) => void;
  currentUserId?: string;
  isCollapsed?: boolean;
}

export function ChatList({
  conversations,
  selectedConversation,
  onSelectConversation,
  currentUserId,
  isCollapsed = false,
}: ChatListProps) {
  return (
    <div className="space-y-2">
      {conversations.map(conversation => {
        // Create initials from profile name
        const firstInitial = conversation.profile.first_name?.[0] || '';
        const lastInitial = conversation.profile.last_name?.[0] || '';
        const initials = `${firstInitial}${lastInitial}`.toUpperCase();
        
        return (
          <Button
            key={conversation.profile.id}
            variant="ghost"
            className={`w-full justify-start rounded-md ${
              selectedConversation?.id === conversation.profile.id ? "bg-secondary" : ""
            }`}
            onClick={() => onSelectConversation(conversation.profile)}
          >
            <div className="flex items-center space-x-2 w-full">
              <Avatar className="h-6 w-6">
                {conversation.profile.avatar_url ? (
                  <AvatarImage
                    src={conversation.profile.avatar_url}
                    alt={initials}
                    className="object-cover"
                  />
                ) : (
                  <AvatarFallback className="bg-slate-100 text-slate-500">
                    {initials}
                  </AvatarFallback>
                )}
              </Avatar>
              {!isCollapsed && (
                <div className="flex-1 truncate">
                  {conversation.profile.first_name} {conversation.profile.last_name}
                </div>
              )}
              {conversation.unreadCount > 0 && conversation.profile.id !== currentUserId && (
                <div className="rounded-full bg-primary text-primary-foreground px-2 py-0.5 text-xs font-bold">
                  {conversation.unreadCount}
                </div>
              )}
            </div>
          </Button>
        );
      })}
    </div>
  );
}
