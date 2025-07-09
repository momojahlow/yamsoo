
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { MessageSquare, User } from "lucide-react";

interface FamilyMemberProps {
  id: string;
  fullName: string;
  avatarUrl: string | null;
  relationLabel: string;
  email: string;
}

export function FamilyMemberItem({ id, fullName, avatarUrl, relationLabel, email }: FamilyMemberProps) {
  const handleSendMessage = () => {
    // Inertia.js navigation
    Inertia.visit(`/messagerie?selectedContactId=${id}`);
  };

  // Create initials from full name
  const initials = fullName.split(' ')
    .map(part => part.charAt(0))
    .join('')
    .toUpperCase();

  return (
    <div className="border rounded-lg p-4 flex items-start justify-between gap-3 bg-white hover:bg-slate-50 transition-colors">
      <div className="flex items-start gap-3">
        <Avatar className="h-12 w-12 border-2 border-primary/10">
          <AvatarImage src={avatarUrl || ''} alt={fullName} />
          <AvatarFallback className="bg-slate-100 text-slate-500">
            {initials}
          </AvatarFallback>
        </Avatar>
        <div>
          <h3 className="font-medium">{fullName}</h3>
          <p className="text-sm text-muted-foreground">{email}</p>
          <div className="mt-1 inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold bg-primary/5 text-primary">
            {relationLabel}
          </div>
        </div>
      </div>
      <Button
        variant="outline"
        size="sm"
        className="text-primary hover:bg-primary/10"
        onClick={handleSendMessage}
        title="Envoyer un message"
      >
        <MessageSquare className="h-4 w-4 mr-1" />
        Message
      </Button>
    </div>
  );
}
