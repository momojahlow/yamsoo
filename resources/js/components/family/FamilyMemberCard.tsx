
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { MessageSquare, Trash2 } from "lucide-react";
import { Inertia } from '@inertiajs/inertia';

interface FamilyMemberCardProps {
  id?: string;
  name: string;
  avatarUrl?: string;
  relation: string;
  onDelete?: () => void;
  style?: React.CSSProperties;
}

function getBackgroundColor(relation: string | null | undefined): string {
  if (typeof relation === 'string' && (relation.includes('Grand-père') || relation.includes('Grand-mère'))) {
    return 'bg-purple-100 hover:bg-purple-200 border-purple-300';
  }
  if (typeof relation === 'string' && (relation.includes('Père') || relation.includes('Mère'))) {
    return 'bg-blue-100 hover:bg-blue-200 border-blue-300';
  }
  if (typeof relation === 'string' && (relation.includes('Frère') || relation.includes('Sœur'))) {
    return 'bg-green-100 hover:bg-green-200 border-green-300';
  }
  if (relation === 'Moi') {
    return 'bg-yellow-100 hover:bg-yellow-200 border-yellow-300';
  }
  if (typeof relation === 'string' && (relation.includes('Fils') || relation.includes('Fille'))) {
    return 'bg-pink-100 hover:bg-pink-200 border-pink-300';
  }
  if (typeof relation === 'string' && (relation.includes('Petit-fils') || relation.includes('Petite-fille'))) {
    return 'bg-orange-100 hover:bg-orange-200 border-orange-300';
  }
  if (typeof relation === 'string' && (relation.includes('Mari') || relation.includes('Épouse'))) {
    return 'bg-rose-100 hover:bg-rose-200 border-rose-300';
  }
  return 'bg-gray-100 hover:bg-gray-200 border-gray-300';
}

export function FamilyMemberCard({
  id,
  name,
  avatarUrl,
  relation,
  onDelete,
  style
}: FamilyMemberCardProps) {
  const bgColor = getBackgroundColor(relation);

  // Create initials from first and last name
  const nameParts = name.split(' ');
  const initials = nameParts.length > 1
    ? `${nameParts[0][0]}${nameParts[1][0]}`.toUpperCase()
    : name.slice(0, 2).toUpperCase();

  const handleSendMessage = () => {
    if (id && relation !== 'Moi') {
      Inertia.visit(`/messagerie?selectedContactId=${id}`);
    }
  };

  return (
    <Card
      className={`p-2 flex flex-col items-center gap-1 transition-colors ${bgColor} border-2 shadow-md rounded-xl`}
      style={{
        width: '110px',
        fontSize: '0.875rem',
        ...style
      }}
    >
      <Avatar className="w-9 h-9 border-2 border-white shadow-sm">
        <AvatarImage src={avatarUrl} alt={name} />
        <AvatarFallback className="text-sm font-semibold bg-slate-100 text-slate-500">
          {initials}
        </AvatarFallback>
      </Avatar>
      <div className="text-center w-full mt-1">
        <div className="font-medium truncate text-xs" title={name}>{name}</div>
        <div className="text-xs font-bold text-blue-700 mt-1" title={relation}>{relation ?? '[relation absente]'}</div>
      </div>
      {(onDelete || relation !== 'Moi') && (
        <div className="flex gap-1 mt-1">
          {relation !== 'Moi' && id && (
            <Button
              variant="ghost"
              size="sm"
              className="p-0 h-6 w-6 text-primary hover:bg-primary/10"
              onClick={handleSendMessage}
              title="Envoyer un message"
            >
              <MessageSquare className="w-3 h-3" />
            </Button>
          )}
          {onDelete && (
            <Button
              variant="ghost"
              size="sm"
              className="p-0 h-6 w-6 text-gray-500 hover:text-red-600 hover:bg-red-50"
              onClick={onDelete}
              title="Supprimer la relation"
            >
              <Trash2 className="w-3 h-3" />
            </Button>
          )}
        </div>
      )}
    </Card>
  );
}
