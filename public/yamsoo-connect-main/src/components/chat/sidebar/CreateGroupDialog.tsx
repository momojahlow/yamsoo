
import { useState } from "react";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger, DialogFooter } from "@/components/ui/dialog";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { useToast } from "@/hooks/use-toast";
import type { DatabaseProfile } from "@/types/chat";

interface CreateGroupDialogProps {
  profiles: DatabaseProfile[];
  searchQuery: string;
  onCreateGroup: (name: string, description: string, memberIds: string[]) => Promise<void>;
}

export function CreateGroupDialog({
  profiles,
  searchQuery,
  onCreateGroup,
}: CreateGroupDialogProps) {
  const [isOpen, setIsOpen] = useState(false);
  const [newGroupName, setNewGroupName] = useState("");
  const [newGroupDescription, setNewGroupDescription] = useState("");
  const [selectedMembers, setSelectedMembers] = useState<string[]>([]);
  const { toast } = useToast();

  const filteredProfiles = profiles.filter(profile =>
    profile.first_name?.toLowerCase().includes(searchQuery.toLowerCase()) ||
    profile.last_name?.toLowerCase().includes(searchQuery.toLowerCase()) ||
    profile.email?.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const handleCreateGroup = async () => {
    if (!newGroupName.trim()) {
      toast({
        title: "Erreur",
        description: "Le nom du groupe ne peut pas être vide",
        variant: "destructive",
      });
      return;
    }

    try {
      await onCreateGroup(newGroupName, newGroupDescription, selectedMembers);
      setIsOpen(false);
      setNewGroupName("");
      setNewGroupDescription("");
      setSelectedMembers([]);
    } catch (error) {
      console.error("Error creating group:", error);
      toast({
        title: "Erreur",
        description: "Une erreur est survenue lors de la création du groupe",
        variant: "destructive",
      });
    }
  };

  const handleSelectMember = (userId: string) => {
    setSelectedMembers(prev => {
      if (prev.includes(userId)) {
        return prev.filter(id => id !== userId);
      } else {
        return [...prev, userId];
      }
    });
  };

  return (
    <Dialog open={isOpen} onOpenChange={setIsOpen}>
      <DialogTrigger asChild>
        <Button variant="ghost" size="sm">
          Créer un groupe
        </Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Créer un nouveau groupe</DialogTitle>
        </DialogHeader>
        <div className="grid gap-4 py-4">
          <div className="grid grid-cols-4 items-center gap-4">
            <Label htmlFor="name" className="text-right">
              Nom
            </Label>
            <Input
              id="name"
              value={newGroupName}
              onChange={e => setNewGroupName(e.target.value)}
              className="col-span-3"
            />
          </div>
          <div className="grid grid-cols-4 items-center gap-4">
            <Label htmlFor="description" className="text-right">
              Description
            </Label>
            <Input
              id="description"
              value={newGroupDescription}
              onChange={e => setNewGroupDescription(e.target.value)}
              className="col-span-3"
            />
          </div>
          <div>
            <Label>Ajouter des membres</Label>
            <ScrollArea className="h-40">
              <div className="space-y-2 p-2">
                {filteredProfiles.map(profile => (
                  <div
                    key={profile.id}
                    className="flex items-center justify-between"
                  >
                    <div className="flex items-center space-x-2">
                      <Avatar className="h-5 w-5">
                        {profile.avatar_url ? (
                          <AvatarImage
                            src={profile.avatar_url}
                            alt={
                              `${profile.first_name?.[0] || ''}${profile.last_name?.[0] || ''}`
                            }
                            className="object-cover"
                          />
                        ) : (
                          <AvatarFallback>
                            {`${profile.first_name?.[0] || ''}${profile.last_name?.[0] || ''}`}
                          </AvatarFallback>
                        )}
                      </Avatar>
                      <span>
                        {profile.first_name} {profile.last_name}
                      </span>
                    </div>
                    <input
                      type="checkbox"
                      checked={selectedMembers.includes(profile.id)}
                      onChange={() => handleSelectMember(profile.id)}
                    />
                  </div>
                ))}
              </div>
            </ScrollArea>
          </div>
        </div>
        <DialogFooter>
          <Button type="button" onClick={handleCreateGroup}>
            Créer
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
