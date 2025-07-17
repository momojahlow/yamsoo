
import { useState } from "react";
import { FamilyRelationType } from "@/types/family";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { getRelationLabel } from "@/utils/relationUtils";
import { MessageSquare } from "lucide-react";
import { useTranslation } from "react-i18next";
import { VALID_DB_RELATION_TYPES } from "@/hooks/family-relations/relationTypeUtils";

interface ProfileCardProps {
  profile: any;
  onSendMessage: (profileId: string) => void;
  onAddRelation: (profileId: string, relationType: FamilyRelationType) => void;
  relationTypes: FamilyRelationType[];
}

export const ProfileCard = ({
  profile,
  onSendMessage,
  onAddRelation,
  relationTypes,
}: ProfileCardProps) => {
  const [selectedRelationType, setSelectedRelationType] = useState<FamilyRelationType | "">("");
  const { t } = useTranslation();

  // Préparer une liste organisée des types de relations
  const relationCategories = {
    family: [
      // Relations parents/enfants
      "father", "mother", "son", "daughter", "child", "baby", "boy",

      // Relations fratrie
      "brother", "sister", "sibling",
      "half_brother", "half_brother_maternal", "half_brother_paternal",
      "half_sister_maternal", "half_sister_paternal",

      // Relations grands-parents/petits-enfants
      "grandfather", "grandmother", "grandson", "granddaughter",

      // Relations oncles/tantes et neveux/nièces
      "uncle", "aunt", "uncle_paternal", "uncle_maternal", "aunt_paternal", "aunt_maternal",
      "nephew", "niece", "nephew_brother", "niece_brother", "nephew_sister", "niece_sister",

      // Relations cousins
      "cousin", "cousin_paternal_m", "cousin_maternal_m",
      "cousin_paternal_f", "cousin_maternal_f",
    ],
    spouse: [
      "husband", "wife", "spouse",
    ],
    inlaws: [
      "father_in_law", "mother_in_law", "son_in_law", "daughter_in_law",
      "brother_in_law", "sister_in_law",
      "stepfather", "stepmother", "stepson", "stepdaughter", "stepbrother", "stepsister",
    ],
    other: [
      "friend_m", "friend_f", "colleague", "other"
    ]
  };

  // Fonction pour filtrer les relations en fonction du genre
  const filterByGender = (types: string[]) => {
    if (!profile.gender) return types;

    return types.filter(type => {
      // Relations spécifiques au genre masculin
      const maleSpecific = [
        "father", "son", "brother", "grandfather", "grandson", "uncle", "nephew",
        "husband", "father_in_law", "son_in_law", "brother_in_law", "stepfather",
        "stepson", "stepbrother", "half_brother", "half_brother_maternal", "half_brother_paternal",
        "nephew_brother", "nephew_sister", "cousin_paternal_m", "cousin_maternal_m",
        "friend_m", "boy", "uncle_paternal", "uncle_maternal"
      ];

      // Relations spécifiques au genre féminin
      const femaleSpecific = [
        "mother", "daughter", "sister", "grandmother", "granddaughter", "aunt", "niece",
        "wife", "mother_in_law", "daughter_in_law", "sister_in_law", "stepmother",
        "stepdaughter", "stepsister", "half_sister_maternal", "half_sister_paternal",
        "niece_brother", "niece_sister", "cousin_paternal_f", "cousin_maternal_f",
        "friend_f", "aunt_paternal", "aunt_maternal"
      ];

      // Filtrer selon le genre
      if (profile.gender === 'M') {
        return !femaleSpecific.includes(type);
      } else if (profile.gender === 'F') {
        return !maleSpecific.includes(type);
      }

      return true;
    });
  };

  const handleRelationTypeChange = (value: FamilyRelationType) => {
    console.log(`Changing relation type for profile ${profile.id} to ${value}`);
    setSelectedRelationType(value);
  };

  const handleAddRelation = () => {
    if (selectedRelationType) {
      onAddRelation(profile.id, selectedRelationType as FamilyRelationType);
    } else {
      // Optionally show a message that relation type must be selected
      console.log("Veuillez sélectionner un type de relation");
    }
  };

  // Get first letter of first name and last name for initials
  const firstInitial = profile.first_name?.[0] || '';
  const lastInitial = profile.last_name?.[0] || '';
  const initials = `${firstInitial}${lastInitial}`.toUpperCase();

  return (
    <div className="border rounded-lg p-4 space-y-4 bg-white/50 backdrop-blur-sm shadow-sm">
      <div className="flex items-center gap-3">
        <Avatar>
          <AvatarImage src={profile.avatar_url || ''} />
          <AvatarFallback className="bg-slate-100 text-slate-500">
            {initials}
          </AvatarFallback>
        </Avatar>
        <div>
          <h3 className="font-medium">
            {profile.first_name} {profile.last_name}
          </h3>
          <p className="text-sm text-muted-foreground">
            {profile.email}
          </p>
          {profile.gender && (
            <p className="text-xs text-muted-foreground">
              {profile.gender === 'M' ? t('gender.male') : t('gender.female')}
            </p>
          )}
        </div>
      </div>

      <div className="space-y-2">
        <div className="space-y-1">
          <label className="text-sm font-medium">Ajoutez en tant que</label>
          <Select
            value={selectedRelationType}
            onValueChange={(value: FamilyRelationType) => handleRelationTypeChange(value)}
          >
            <SelectTrigger className="w-full">
              <SelectValue placeholder="Sélectionner une relation familiale">
                {selectedRelationType ? getRelationLabel(selectedRelationType) : "Sélectionner une relation familiale"}
              </SelectValue>
            </SelectTrigger>
            <SelectContent className="max-h-72 overflow-y-auto bg-white">
              {/* Groupe: Famille proche */}
              <div className="py-1.5 pl-2 text-xs font-semibold text-muted-foreground">Famille proche</div>
              {filterByGender(relationCategories.family).map((type) => (
                <SelectItem key={type} value={type}>
                  {getRelationLabel(type as FamilyRelationType)}
                </SelectItem>
              ))}

              {/* Groupe: Époux/Épouse */}
              <div className="py-1.5 pl-2 text-xs font-semibold text-muted-foreground">Époux/Épouse</div>
              {filterByGender(relationCategories.spouse).map((type) => (
                <SelectItem key={type} value={type}>
                  {getRelationLabel(type as FamilyRelationType)}
                </SelectItem>
              ))}

              {/* Groupe: Belle-famille */}
              <div className="py-1.5 pl-2 text-xs font-semibold text-muted-foreground">Belle-famille</div>
              {filterByGender(relationCategories.inlaws).map((type) => (
                <SelectItem key={type} value={type}>
                  {getRelationLabel(type as FamilyRelationType)}
                </SelectItem>
              ))}

              {/* Groupe: Autre */}
              <div className="py-1.5 pl-2 text-xs font-semibold text-muted-foreground">Autre</div>
              {filterByGender(relationCategories.other).map((type) => (
                <SelectItem key={type} value={type}>
                  {getRelationLabel(type as FamilyRelationType)}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
      </div>

      <div className="flex gap-2">
        <Button
          variant="default"
          onClick={handleAddRelation}
          className="flex-1"
          disabled={!selectedRelationType}
        >
          Demander une relation
        </Button>
        <Button
          variant="outline"
          onClick={() => onSendMessage(profile.id)}
          size="icon"
          className="flex items-center justify-center"
          title={t('messages.sendMessage')}
        >
          <MessageSquare className="h-5 w-5" />
        </Button>
      </div>
    </div>
  );
};
