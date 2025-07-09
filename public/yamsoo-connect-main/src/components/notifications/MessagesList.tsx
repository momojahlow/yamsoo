
import React from 'react';
import { MessageItem } from './message/MessageItem';
import { MessageNotification } from '@/hooks/useMessageNotifications';

interface MessagesListProps {
  messages: MessageNotification[];
  loading: boolean;
  onReply: (sender: { id: string; name: string }) => void;
  onEdit?: (message: MessageNotification) => void;
}

export function MessagesList({ messages, loading, onReply, onEdit }: MessagesListProps) {
  if (loading) {
    return <div className="text-center py-8">Chargement des messages...</div>;
  }

  if (!messages || messages.length === 0) {
    return <div className="text-center py-8">Aucun message récent</div>;
  }

  // Filter out duplicate messages based on ID
  const uniqueMessages = messages.reduce<MessageNotification[]>((acc, current) => {
    const isDuplicate = acc.find((item) => item.id === current.id);
    if (!isDuplicate) {
      return [...acc, current];
    }
    return acc;
  }, []);

  return (
    <div className="mt-8">
      <h2 className="text-2xl font-semibold mb-4">Messages récents</h2>
      <div className="space-y-4">
        {uniqueMessages.map((message) => (
          <MessageItem
            key={message.id}
            message={message}
            onReply={() => onReply({ 
              id: message.sender_id, 
              name: message.sender_name 
            })}
            onEdit={onEdit ? () => onEdit(message) : undefined}
          />
        ))}
      </div>
    </div>
  );
}
