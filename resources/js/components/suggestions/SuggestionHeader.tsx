
import React from "react";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";

interface SuggestionHeaderProps {
  name: string;
  avatarUrl?: string;
  relationLabel?: string;
}

export function SuggestionHeader({ name, avatarUrl, relationLabel }: SuggestionHeaderProps) {
  // Extract initials from name
  const initials = name.split(' ')
    .map(part => part.charAt(0))
    .join('')
    .toUpperCase();

  return (
    <div className="flex items-center gap-3 mb-2">
      <Avatar className="h-10 w-10">
        <AvatarImage src={avatarUrl || ''} alt={name} />
        <AvatarFallback className="bg-slate-100 text-slate-500">{initials || '?'}</AvatarFallback>
      </Avatar>
      <div className="flex-1">
        <h3 className="font-medium">{name}</h3>
        {relationLabel && (
          <p className="text-sm text-muted-foreground">
            {`${name} pourrait être un membre de votre famille`}
          </p>
        )}
      </div>
    </div>
  );
}
