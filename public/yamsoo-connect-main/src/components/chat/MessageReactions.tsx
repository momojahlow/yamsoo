
import { useState } from "react";
import { Button } from "@/components/ui/button";
import { SmilePlus } from "lucide-react";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import data from '@emoji-mart/data';
import Picker from '@emoji-mart/react';

interface MessageReactionsProps {
  reactions: Record<string, string[]>;
  onAddReaction: (emoji: string) => void;
  currentUserId?: string;
}

export function MessageReactions({ reactions, onAddReaction, currentUserId }: MessageReactionsProps) {
  const [isOpen, setIsOpen] = useState(false);

  const handleEmojiSelect = (emoji: any) => {
    onAddReaction(emoji.native);
    setIsOpen(false);
  };

  return (
    <div className="flex items-center gap-1 mt-1">
      {Object.entries(reactions).map(([emoji, users]) => (
        <Button
          key={emoji}
          variant="ghost"
          size="sm"
          className="h-6 px-2 text-xs"
          onClick={() => onAddReaction(emoji)}
        >
          {emoji} {users.length}
        </Button>
      ))}
      <Popover open={isOpen} onOpenChange={setIsOpen}>
        <PopoverTrigger asChild>
          <Button variant="ghost" size="sm" className="h-6 px-2">
            <SmilePlus className="h-4 w-4" />
          </Button>
        </PopoverTrigger>
        <PopoverContent className="w-auto p-0" align="start">
          <Picker
            data={data}
            onEmojiSelect={handleEmojiSelect}
            theme="light"
          />
        </PopoverContent>
      </Popover>
    </div>
  );
}
