
import React from 'react';
import { Button } from "@/components/ui/button";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { MessageCircle, Edit } from "lucide-react";
import { MessageNotification } from '@/hooks/useMessageNotifications';

// Add the MessageWithSender type export
export interface MessageWithSender {
  id: string;
  content: string;
  sender_id: string;
  receiver_id: string;
  created_at: string;
  read_at?: string | null;
  sender?: any;
}

interface MessageItemProps {
  message: MessageNotification;
  onReply: () => void;
  onEdit?: () => void;
}

export function MessageItem({ message, onReply, onEdit }: MessageItemProps) {
  // Create proper uppercase initials from sender name
  const initials = message.sender_name
    .split(' ')
    .map(part => part.charAt(0))
    .join('')
    .toUpperCase();

  return (
    <div className="flex items-start gap-4 p-4 bg-white rounded-lg shadow">
      <Avatar>
        <AvatarImage src={message.sender_avatar || ''} />
        <AvatarFallback className="bg-slate-100 text-slate-500">{initials}</AvatarFallback>
      </Avatar>
      
      <div className="flex-1">
        <div className="flex justify-between">
          <h3 className="font-medium">{message.sender_name}</h3>
          <span className="text-xs text-gray-500">{message.formatted_date}</span>
        </div>
        <p className="mt-1 text-gray-700">{message.content}</p>
        
        <div className="mt-2 flex gap-2">
          <Button 
            variant="ghost" 
            size="sm" 
            className="text-blue-600 hover:text-blue-800" 
            onClick={onReply}
          >
            <MessageCircle className="h-4 w-4 mr-1" />
            Répondre
          </Button>
          
          {onEdit && (
            <Button 
              variant="ghost" 
              size="sm" 
              className="text-gray-600 hover:text-gray-800" 
              onClick={onEdit}
            >
              <Edit className="h-4 w-4 mr-1" />
              Modifier
            </Button>
          )}
        </div>
      </div>
    </div>
  );
}
