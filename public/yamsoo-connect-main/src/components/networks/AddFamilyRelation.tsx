
import React, { useState } from "react";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  DialogDescription,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { FamilyRelationType } from "@/types/family";
import { useFamilyRelation } from "@/hooks/useFamilyRelation";
import { Database } from "@/integrations/supabase/types";
import { getRelationLabel } from "@/utils/relationUtils";
import { UserPlus } from "lucide-react";

type Profile = Database['public']['Tables']['profiles']['Row'];

interface AddFamilyRelationProps {
  profile: Profile;
  onRelationAdded?: () => void;
}

const familyRelations: FamilyRelationType[] = [
  'father',
  'mother',
  'son',
  'daughter',
  'brother',
  'sister',
  'uncle',
  'aunt',
  'nephew',
  'niece',
  'grandfather',
  'grandmother',
  'grandson',
  'granddaughter',
  'husband',
  'wife',
  'stepfather',
  'stepmother',
  'stepson',
  'stepdaughter',
  'father_in_law',
  'mother_in_law',
  'son_in_law',
  'daughter_in_law',
  'brother_in_law',
  'sister_in_law',
  'spouse',
  'cousin',
  'friend_m',
  'friend_f',
  'colleague'
];

export const AddFamilyRelation = ({ profile, onRelationAdded }: AddFamilyRelationProps) => {
  const [open, setOpen] = useState(false);
  const [selectedRelation, setSelectedRelation] = useState<FamilyRelationType | null>(null);
  const { addRelation, isLoading } = useFamilyRelation();

  const handleAddRelation = async () => {
    if (!selectedRelation) return;
    
    const success = await addRelation(profile.id, selectedRelation);
    if (success) {
      setOpen(false);
      onRelationAdded?.();
      setSelectedRelation(null);
    }
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="outline" size="sm" className="whitespace-nowrap">
          <UserPlus className="h-4 w-4 mr-2" />
          Ajouter une relation
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Ajouter une relation familiale</DialogTitle>
          <DialogDescription>
            Sélectionnez le type de relation que vous avez avec cette personne
          </DialogDescription>
        </DialogHeader>
        <div className="grid gap-4 py-4">
          <div className="flex flex-col gap-2">
            <span className="text-sm font-medium">Relation avec {profile.first_name}</span>
            <Select
              onValueChange={(value) => setSelectedRelation(value as FamilyRelationType)}
              value={selectedRelation || undefined}
            >
              <SelectTrigger>
                <SelectValue placeholder="Choisir une relation" />
              </SelectTrigger>
              <SelectContent>
                {familyRelations.map((relation) => (
                  <SelectItem key={relation} value={relation}>
                    {getRelationLabel(relation)}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          <div className="flex justify-end gap-2">
            <Button
              variant="outline"
              onClick={() => setOpen(false)}
              disabled={isLoading}
            >
              Annuler
            </Button>
            <Button
              onClick={handleAddRelation}
              disabled={!selectedRelation || isLoading}
            >
              {isLoading ? "Ajout en cours..." : "Ajouter"}
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
};
