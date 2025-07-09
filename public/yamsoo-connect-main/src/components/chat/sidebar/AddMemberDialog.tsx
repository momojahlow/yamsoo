
import { useState } from "react";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { useToast } from "@/hooks/use-toast";

interface AddMemberDialogProps {
  isOpen: boolean;
  onOpenChange: (open: boolean) => void;
  groupId: string | null;
  groupName: string;
  onAddMember: (groupId: string, userId: string) => Promise<void>;
}

export function AddMemberDialog({
  isOpen,
  onOpenChange,
  groupId,
  groupName,
  onAddMember,
}: AddMemberDialogProps) {
  const [newMemberId, setNewMemberId] = useState("");
  const { toast } = useToast();

  const handleAddMember = async () => {
    if (!groupId || !newMemberId) return;

    try {
      await onAddMember(groupId, newMemberId);
      onOpenChange(false);
      setNewMemberId("");
    } catch (error) {
      console.error("Error adding member:", error);
      toast({
        title: "Erreur",
        description: "Une erreur est survenue lors de l'ajout du membre",
        variant: "destructive",
      });
    }
  };

  return (
    <Dialog open={isOpen} onOpenChange={onOpenChange}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Ajouter un membre à {groupName}</DialogTitle>
        </DialogHeader>
        <div className="grid gap-4 py-4">
          <div className="grid grid-cols-4 items-center gap-4">
            <Label htmlFor="memberId" className="text-right">
              ID de l'utilisateur
            </Label>
            <Input
              id="memberId"
              value={newMemberId}
              onChange={e => setNewMemberId(e.target.value)}
              className="col-span-3"
            />
          </div>
        </div>
        <DialogFooter>
          <Button type="button" onClick={handleAddMember}>
            Ajouter
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
