
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { MessageSquare, Trash2, Heart } from "lucide-react";
import { useNavigate } from "react-router-dom";
import { useIsMobile } from "@/hooks/use-mobile";

interface FamilyMemberCardProps {
  id?: string;
  name: string;
  avatarUrl?: string;
  relation: string;
  onDelete?: () => void;
  style?: React.CSSProperties;
}

function getBackgroundColor(relation: string): string {
  if (relation.includes('Grand-père') || relation.includes('Grand-mère')) {
    return 'bg-purple-100 border-purple-300';
  }
  if (relation.includes('Père') || relation.includes('Mère')) {
    return 'bg-blue-100 border-blue-300';
  }
  if (relation.includes('Frère') || relation.includes('Sœur')) {
    return 'bg-green-100 border-green-300';
  }
  if (relation === 'Moi') {
    return 'bg-yellow-100 border-yellow-300';
  }
  if (relation.includes('Fils') || relation.includes('Fille')) {
    return 'bg-pink-100 border-pink-300';
  }
  if (relation.includes('Petit-fils') || relation.includes('Petite-fille')) {
    return 'bg-orange-100 border-orange-300';
  }
  if (relation.includes('Mari') || relation.includes('Épouse')) {
    return 'bg-rose-100 border-rose-300';
  }
  if (relation.includes('Oncle') || relation.includes('Tante')) {
    return 'bg-cyan-100 border-cyan-300';
  }
  if (relation.includes('Neveu') || relation.includes('Nièce')) {
    return 'bg-teal-100 border-teal-300';
  }
  if (relation.includes('Cousin')) {
    return 'bg-emerald-100 border-emerald-300';
  }
  return 'bg-gray-100 border-gray-300';
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
  const navigate = useNavigate();
  const isMobile = useIsMobile();
  
  // Create initials from first and last name parts
  const initials = name.split(' ')
    .map(part => part.charAt(0))
    .join('')
    .toUpperCase();

  const handleSendMessage = () => {
    if (id && relation !== 'Moi') {
      navigate("/messagerie", { state: { selectedContactId: id } });
    }
  };
  
  return (
    <Card 
      className={`p-2 flex flex-col items-center gap-1 ${bgColor} border-2 shadow-md rounded-xl`}
      style={{
        width: '110px',
        ...style
      }}
    >
      <Avatar className="w-12 h-12 border-2 border-white shadow-sm">
        <AvatarImage src={avatarUrl} alt={name} />
        <AvatarFallback className="text-base font-semibold bg-slate-100 text-slate-500">
          {initials}
        </AvatarFallback>
      </Avatar>
      <div className="text-center w-full">
        <div className="font-medium truncate text-sm" title={name}>{name}</div>
        <div className="text-xs text-gray-700 font-medium" title={relation}>{relation}</div>
      </div>
      <div className="flex justify-center gap-1 mt-1 w-full">
        {relation !== 'Moi' && (relation.includes('Fils') || relation.includes('Petit-fils')) && (
          <Button 
            variant="ghost" 
            size="sm"
            className="p-1 h-6 w-6 text-amber-500 hover:bg-amber-50"
            onClick={handleSendMessage}
            title="Envoyer un message"
          >
            <MessageSquare className="w-3 h-3" />
          </Button>
        )}
        {(relation === 'Mari' || relation === 'Épouse') && (
          <Button 
            variant="ghost" 
            size="sm"
            className="p-1 h-6 w-6 text-rose-500 hover:bg-rose-50"
            title="Lien conjugal"
          >
            <Heart className="w-3 h-3" />
          </Button>
        )}
        {onDelete && (
          <Button 
            variant="ghost" 
            size="sm"
            className="p-1 h-6 w-6 text-gray-500 hover:text-red-600 hover:bg-red-50"
            onClick={onDelete}
            title="Supprimer la relation"
          >
            <Trash2 className="w-3 h-3" />
          </Button>
        )}
      </div>
    </Card>
  );
}
