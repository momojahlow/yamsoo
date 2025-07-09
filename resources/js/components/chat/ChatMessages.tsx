
import { useEffect, useRef } from "react";
import { ScrollArea } from "@/components/ui/scroll-area";
import { ChatMessage } from "./message/ChatMessage";
import { Message } from "@/types/chat";

interface ChatMessagesProps {
  messages: Message[];
  currentUserId?: string;
  onMarkAsRead: (messageId: string) => void;
  onAddReaction: (messageId: string, emoji: string) => void;
}

export function ChatMessages({
  messages,
  currentUserId,
  onMarkAsRead,
  onAddReaction,
}: ChatMessagesProps) {
  const scrollRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (scrollRef.current) {
      scrollRef.current.scrollTop = scrollRef.current.scrollHeight;
    }

    messages.forEach(message => {
      if (message.sender_id !== currentUserId && !message.read_at) {
        onMarkAsRead(message.id);
      }
    });
  }, [messages, currentUserId, onMarkAsRead]);

  return (
    <ScrollArea ref={scrollRef} className="flex-1 p-4">
      <div className="space-y-4 flex flex-col">
        {messages.map((message) => (
          <ChatMessage 
            key={message.id}
            message={message}
            currentUserId={currentUserId}
            onAddReaction={(emoji) => onAddReaction(message.id, emoji)}
          />
        ))}
      </div>
    </ScrollArea>
  );
}
