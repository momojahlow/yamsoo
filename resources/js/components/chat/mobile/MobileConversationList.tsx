
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { ScrollArea } from "@/components/ui/scroll-area";
import { formatDistanceToNow } from "date-fns";
import { fr } from "date-fns/locale";
import type { DatabaseProfile } from "@/types/chat";
import { Badge } from "@/components/ui/badge";
import { Search } from "lucide-react";
import { Input } from "@/components/ui/input";
import { useState } from "react";

interface MobileConversationListProps {
  conversations: Array<{
    profile: DatabaseProfile;
    lastMessage: any;
    unreadCount: number;
  }>;
  isLoading: boolean;
  onSelectConversation: (profile: DatabaseProfile) => void;
  currentUserId?: string;
}

export function MobileConversationList({
  conversations,
  isLoading,
  onSelectConversation,
  currentUserId,
}: MobileConversationListProps) {
  const [searchQuery, setSearchQuery] = useState("");
  
  const formatMessageDate = (date: string) => {
    try {
      return formatDistanceToNow(new Date(date), { addSuffix: true, locale: fr });
    } catch (e) {
      return "";
    }
  };

  const truncateMessage = (message: string, limit = 30) => {
    if (!message) return "";
    if (message.length <= limit) return message;
    return message.substring(0, limit) + "...";
  };
  
  const filteredConversations = searchQuery 
    ? conversations.filter(convo => 
        `${convo.profile.first_name} ${convo.profile.last_name}`
          .toLowerCase()
          .includes(searchQuery.toLowerCase())
      )
    : conversations;

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-full">
        <p className="text-muted-foreground">Chargement des conversations...</p>
      </div>
    );
  }

  return (
    <div className="flex flex-col h-full bg-background">
      <div className="p-3 sticky top-0 bg-background z-10 border-b pt-14"> {/* Added pt-14 for menu button space */}
        <h1 className="text-xl font-bold mb-2">Messages</h1>
        <div className="relative">
          <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            type="text"
            placeholder="Rechercher une conversation..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="pl-9 bg-muted/50"
          />
        </div>
      </div>

      {filteredConversations.length === 0 && !isLoading ? (
        <div className="flex flex-col items-center justify-center flex-1 p-4 text-center">
          {searchQuery ? (
            <>
              <h3 className="text-xl font-semibold mb-2">Aucun résultat</h3>
              <p className="text-muted-foreground">
                Aucune conversation ne correspond à votre recherche
              </p>
            </>
          ) : (
            <>
              <h3 className="text-xl font-semibold mb-2">Aucune conversation</h3>
              <p className="text-muted-foreground">
                Commencez une nouvelle conversation en allant dans le réseau
              </p>
            </>
          )}
        </div>
      ) : (
        <ScrollArea className="flex-1">
          <div>
            {filteredConversations.map((convo) => (
              <div
                key={convo.profile.id}
                className="px-4 py-3 flex items-center gap-3 cursor-pointer hover:bg-muted/50 transition-colors border-b border-muted/30"
                onClick={() => onSelectConversation(convo.profile)}
              >
                <div className="relative">
                  <Avatar className="h-12 w-12 border border-muted/20">
                    <AvatarImage
                      src={convo.profile.avatar_url || ""}
                      alt={`${convo.profile.first_name} ${convo.profile.last_name}`}
                    />
                    <AvatarFallback>
                      {convo.profile.first_name?.[0]}
                      {convo.profile.last_name?.[0]}
                    </AvatarFallback>
                  </Avatar>
                  {convo.unreadCount > 0 && convo.profile.id !== currentUserId && (
                    <Badge 
                      variant="destructive" 
                      className="h-5 min-w-5 flex items-center justify-center rounded-full p-0 text-xs absolute -top-1 -right-1"
                    >
                      {convo.unreadCount > 99 ? '99+' : convo.unreadCount}
                    </Badge>
                  )}
                </div>

                <div className="flex-1 min-w-0">
                  <div className="flex justify-between items-center mb-1">
                    <h4 className="font-medium truncate">
                      {convo.profile.first_name} {convo.profile.last_name}
                    </h4>
                    {convo.lastMessage && (
                      <span className="text-xs text-muted-foreground whitespace-nowrap ml-2">
                        {formatMessageDate(convo.lastMessage.created_at)}
                      </span>
                    )}
                  </div>

                  <div className="flex items-center">
                    <p className="text-sm text-muted-foreground truncate">
                      {convo.lastMessage ? (
                        convo.lastMessage.content ? (
                          truncateMessage(convo.lastMessage.content)
                        ) : convo.lastMessage.attachment_url ? (
                          "📎 Pièce jointe"
                        ) : convo.lastMessage.audio_url ? (
                          "🎤 Message vocal"
                        ) : (
                          "Message vide"
                        )
                      ) : (
                        "Nouvelle conversation"
                      )}
                    </p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </ScrollArea>
      )}
    </div>
  );
}
