
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { MessageReactions } from "../MessageReactions";
import { MessageContent } from "./MessageContent";
import { MessageMetadata } from "./MessageMetadata";
import { Message } from "@/types/chat";

interface ChatMessageProps {
  message: Message;
  currentUserId?: string;
  onAddReaction: (emoji: string) => void;
}

export function ChatMessage({ message, currentUserId, onAddReaction }: ChatMessageProps) {
  const isOwnMessage = message.sender_id === currentUserId;
  const initials = message.sender_profile ? 
    `${message.sender_profile.first_name?.[0] || ''}${message.sender_profile.last_name?.[0] || ''}`.toUpperCase() : 
    '';

  return (
    <div
      className={`flex items-start gap-2 ${
        isOwnMessage ? "flex-row-reverse" : ""
      }`}
    >
      <Avatar className="h-8 w-8 flex-shrink-0 mt-1">
        {message.sender_profile?.avatar_url ? (
          <AvatarImage 
            src={message.sender_profile.avatar_url} 
            alt={initials}
            className="object-cover"
          />
        ) : (
          <AvatarFallback className="bg-slate-100 text-slate-500">{initials}</AvatarFallback>
        )}
      </Avatar>

      <div
        className={`flex flex-col max-w-[80%] ${
          isOwnMessage ? "items-end" : "items-start"
        }`}
      >
        <MessageContent 
          message={message}
          isOwnMessage={isOwnMessage}
        />

        <MessageReactions
          reactions={message.reactions || {}}
          onAddReaction={(emoji) => onAddReaction(emoji)}
          currentUserId={currentUserId}
        />

        <MessageMetadata 
          createdAt={message.created_at}
          readAt={message.read_at}
          isOwnMessage={isOwnMessage}
        />
      </div>
    </div>
  );
}
