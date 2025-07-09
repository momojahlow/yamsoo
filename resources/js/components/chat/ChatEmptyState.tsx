
import { useEffect } from "react";
import { Button } from "@/components/ui/button";
import { MessageSquare } from "lucide-react";
import type { DatabaseProfile } from "@/types/chat";

interface ChatEmptyStateProps {
  conversations: Array<{ profile: DatabaseProfile, lastMessage: any, unreadCount: number }>;
  onSelectConversation: (profile: DatabaseProfile | null) => void;
}

export function ChatEmptyState({ conversations, onSelectConversation }: ChatEmptyStateProps) {
  const lastConversation = conversations && conversations.length > 0 ? conversations[0] : null;

  // Automatically select the last conversation when component mounts
  useEffect(() => {
    if (lastConversation) {
      onSelectConversation(lastConversation.profile);
    }
  }, [lastConversation, onSelectConversation]);

  // If auto-selection happens, this UI should not be displayed
  // But we keep it as a fallback in case there are no conversations
  // or if the automatic selection fails for some reason
  return (
    <div className="flex flex-col items-center justify-center h-full p-8 text-center space-y-4">
      {lastConversation ? (
        <div className="flex flex-col items-center space-y-4">
          <MessageSquare className="h-16 w-16 text-muted-foreground" />
          <h2 className="text-xl font-semibold">Sélectionnez une conversation pour commencer à discuter</h2>
          <p className="text-muted-foreground max-w-md">
            Vous avez des conversations existantes. Vous pouvez reprendre votre dernière conversation.
          </p>
          <Button 
            onClick={() => onSelectConversation(lastConversation.profile)}
            className="mt-4"
          >
            Reprendre avec {lastConversation.profile.first_name} {lastConversation.profile.last_name}
          </Button>
        </div>
      ) : (
        <div className="flex flex-col items-center space-y-4">
          <MessageSquare className="h-16 w-16 text-muted-foreground" />
          <h2 className="text-xl font-semibold">Sélectionnez une conversation pour commencer à discuter</h2>
          <p className="text-muted-foreground max-w-md">
            Vous n'avez pas encore de conversations. Sélectionnez un contact dans la liste pour commencer à discuter.
          </p>
        </div>
      )}
    </div>
  );
}
