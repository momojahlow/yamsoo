
import { Button } from "@/components/ui/button";
import { Phone, Video } from "lucide-react";
import { DatabaseProfile } from "@/types/chat";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";

interface ChatHeaderProps {
  selectedConversation: DatabaseProfile | null;
  showConversationList?: () => void;
  toggleSidebar?: () => void;
}

export function ChatHeader({ selectedConversation, showConversationList, toggleSidebar }: ChatHeaderProps) {
  if (!selectedConversation) return null;
  
  return (
    <div className="p-3 border-b flex items-center justify-between bg-background sticky top-0 z-20 shadow-sm">
      <div className="flex items-center">
        {showConversationList && (
          <Button 
            variant="ghost" 
            size="icon" 
            onClick={showConversationList}
            className="mr-2"
            aria-label="Retour"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="lucide lucide-chevron-left">
              <path d="m15 18-6-6 6-6"/>
            </svg>
          </Button>
        )}
        
        <Avatar className="h-10 w-10 mr-3 border border-muted/30">
          <AvatarImage src={selectedConversation.avatar_url || ''} alt={`${selectedConversation.first_name} ${selectedConversation.last_name}`} />
          <AvatarFallback>
            {selectedConversation.first_name?.[0]}
            {selectedConversation.last_name?.[0]}
          </AvatarFallback>
        </Avatar>
        
        <div className="flex-1 min-w-0">
          <div className="font-semibold text-base truncate">
            {selectedConversation.first_name} {selectedConversation.last_name}
          </div>
          <div className="text-xs text-muted-foreground">
            En ligne
          </div>
        </div>
      </div>
      
      <div className="flex gap-2 ml-2">
        <Button variant="ghost" size="icon" className="text-muted-foreground" aria-label="Appel audio">
          <Phone size={20} />
        </Button>
        <Button variant="ghost" size="icon" className="text-muted-foreground" aria-label="Appel vidéo">
          <Video size={20} />
        </Button>
      </div>
    </div>
  );
}
