
import { useState, useEffect } from "react";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Search, Send, X } from "lucide-react";
import { Checkbox } from "@/components/ui/checkbox";
import { ScrollArea } from "@/components/ui/scroll-area";
import { DatabaseProfile } from "@/types/chat";
import { useProfiles } from "@/hooks/useProfiles";
import { supabase } from "@/integrations/supabase/client";

interface ShareContactsListProps {
  imageUrl: string;
  onClose: () => void;
}

export function ShareContactsList({ imageUrl, onClose }: ShareContactsListProps) {
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedContacts, setSelectedContacts] = useState<string[]>([]);
  const [currentUser, setCurrentUser] = useState<any>(null);
  const [isSending, setIsSending] = useState(false);
  const profiles = useProfiles(currentUser);
  
  const filteredProfiles = profiles.filter(profile => {
    const fullName = `${profile.first_name || ""} ${profile.last_name || ""}`.toLowerCase();
    return fullName.includes(searchQuery.toLowerCase()) ||
           (profile.email || "").toLowerCase().includes(searchQuery.toLowerCase());
  });

  useEffect(() => {
    const fetchCurrentUser = async () => {
      const { data } = await supabase.auth.getUser();
      if (data?.user) {
        setCurrentUser(data.user);
      }
    };
    
    fetchCurrentUser();
  }, []);

  const toggleContactSelection = (contactId: string) => {
    setSelectedContacts(prev => 
      prev.includes(contactId)
        ? prev.filter(id => id !== contactId)
        : [...prev, contactId]
    );
  };

  const handleShareImage = async () => {
    if (!currentUser || selectedContacts.length === 0) return;
    
    setIsSending(true);
    try {
      // Envoyer le message avec l'image à chaque contact sélectionné
      for (const contactId of selectedContacts) {
        await sendMessageWithImage(contactId, imageUrl);
      }
      
      onClose();
    } catch (error) {
      console.error("Erreur lors du partage de l'image:", error);
    } finally {
      setIsSending(false);
    }
  };

  const sendMessageWithImage = async (receiverId: string, imageUrl: string) => {
    try {
      await supabase
        .from("messages")
        .insert([
          {
            content: "Image partagée",
            sender_id: currentUser.id,
            receiver_id: receiverId,
            attachment_url: imageUrl,
            attachment_name: "Image partagée"
          }
        ]);
    } catch (error) {
      console.error("Erreur lors de l'envoi du message avec image:", error);
      throw error;
    }
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h3 className="text-lg font-medium">Partager avec</h3>
        <Button variant="ghost" size="icon" onClick={onClose}>
          <X className="h-4 w-4" />
        </Button>
      </div>
      
      <div className="relative">
        <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
        <Input
          type="text"
          placeholder="Rechercher un contact..."
          className="pl-8"
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
        />
      </div>
      
      <ScrollArea className="h-[250px] rounded-md border p-2">
        {filteredProfiles.length > 0 ? (
          <div className="space-y-2">
            {filteredProfiles.map((profile) => (
              <div 
                key={profile.id}
                className="flex items-center space-x-3 p-2 rounded-md hover:bg-accent"
              >
                <Checkbox
                  id={`contact-${profile.id}`}
                  checked={selectedContacts.includes(profile.id)}
                  onCheckedChange={() => toggleContactSelection(profile.id)}
                />
                <Avatar className="h-8 w-8">
                  <AvatarImage src={profile.avatar_url || undefined} />
                  <AvatarFallback>
                    {profile.first_name?.[0]}{profile.last_name?.[0]}
                  </AvatarFallback>
                </Avatar>
                <label 
                  htmlFor={`contact-${profile.id}`}
                  className="flex-1 text-sm font-medium cursor-pointer"
                >
                  {profile.first_name} {profile.last_name}
                </label>
              </div>
            ))}
          </div>
        ) : (
          <div className="flex items-center justify-center h-full text-muted-foreground">
            Aucun contact trouvé
          </div>
        )}
      </ScrollArea>
      
      <div className="flex justify-end space-x-2">
        <Button variant="outline" onClick={onClose}>Annuler</Button>
        <Button 
          onClick={handleShareImage}
          disabled={selectedContacts.length === 0 || isSending}
        >
          <Send className="h-4 w-4 mr-2" />
          Partager {selectedContacts.length > 0 && `(${selectedContacts.length})`}
        </Button>
      </div>
    </div>
  );
}
