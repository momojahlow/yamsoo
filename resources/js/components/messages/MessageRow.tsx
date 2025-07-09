
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { MessageSquare } from "lucide-react";
import { DatabaseProfile } from "@/types/chat";

interface MessageRowProps {
  profile: DatabaseProfile;
  onSelect: (profile: DatabaseProfile) => void;
  isSelected?: boolean;
}

export const MessageRow = ({ profile, onSelect, isSelected }: MessageRowProps) => {
  // Create initials from first and last name
  const initials = `${profile.first_name?.[0] || ''}${profile.last_name?.[0] || ''}`.toUpperCase();

  return (
    <div 
      className={`flex items-center gap-3 p-3 cursor-pointer hover:bg-accent/50 transition-colors ${
        isSelected ? 'bg-accent' : ''
      }`}
      onClick={() => onSelect(profile)}
    >
      <Avatar>
        <AvatarImage src={profile.avatar_url || ''} />
        <AvatarFallback className="bg-slate-100 text-slate-500">
          {initials}
        </AvatarFallback>
      </Avatar>
      <div className="flex-1">
        <p className="font-medium">
          {profile.first_name} {profile.last_name}
        </p>
        <p className="text-sm text-muted-foreground">
          {profile.email}
        </p>
      </div>
      <Button variant="ghost" size="icon">
        <MessageSquare className="h-4 w-4" />
      </Button>
    </div>
  );
}
