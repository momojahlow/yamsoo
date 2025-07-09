
import { ScrollArea } from "@/components/ui/scroll-area";
import { CardContent } from "@/components/ui/card";
import { MessageItem, MessageWithSender } from "./MessageItem";
import { Database } from "@/integrations/supabase/types";

type Profile = Database['public']['Tables']['profiles']['Row'];

interface MessageListContentProps {
  messages: MessageWithSender[];
  onReply: (sender: Profile) => void;
  onEdit: (message: MessageWithSender) => void;
}

export function MessageListContent({ 
  messages, 
  onReply, 
  onEdit 
}: MessageListContentProps) {
  return (
    <ScrollArea className="h-[600px] w-full rounded-md border">
      <CardContent className="p-4 space-y-4">
        {messages.map((message) => {
          // Create a formatted message object with all required properties
          const formattedMessage = {
            id: message.id,
            content: message.content,
            sender_id: message.sender_id,
            sender_name: message.sender?.first_name + ' ' + message.sender?.last_name || 'Unknown',
            sender_avatar: message.sender?.avatar_url,
            created_at: message.created_at,
            formatted_date: new Date(message.created_at).toLocaleString()
          };
          
          return (
            <MessageItem
              key={message.id}
              message={formattedMessage}
              onReply={() => message.sender && onReply(message.sender)}
              onEdit={() => onEdit(message)}
            />
          );
        })}
      </CardContent>
    </ScrollArea>
  );
}
