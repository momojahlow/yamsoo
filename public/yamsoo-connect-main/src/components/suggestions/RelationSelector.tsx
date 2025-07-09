
import React from "react";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { getRelationLabel, FamilyRelationType } from "@/utils/relationUtils";
import { Separator } from "@/components/ui/separator";

interface RelationSelectorProps {
  selectedRelationType: string;
  setSelectedRelationType: (value: string) => void;
  targetGender?: string | null;
}

export function RelationSelector({ 
  selectedRelationType, 
  setSelectedRelationType,
  targetGender 
}: RelationSelectorProps) {
  // Organiser les types de relations par catégorie pour un affichage plus structuré
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
    if (!targetGender) return types;
    
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
      if (targetGender === 'M') {
        return !femaleSpecific.includes(type);
      } else if (targetGender === 'F') {
        return !maleSpecific.includes(type);
      }
      
      return true;
    });
  };

  // Get the target name from the parent component or use a default
  const targetName = "cette personne";

  return (
    <div className="border border-amber-200 rounded-md p-4 mb-4 bg-amber-100/50">
      <label htmlFor="relation-select" className="block text-sm font-medium mb-1">
        Choisir le type de relation:
      </label>
      <Select 
        value={selectedRelationType}
        onValueChange={(value) => setSelectedRelationType(value)}
      >
        <SelectTrigger className="w-full bg-white" id="relation-select">
          <SelectValue placeholder="Sélectionnez un type de relation" />
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
      
      <div className="mt-3 text-sm flex items-center gap-2 text-amber-700">
        <span>Vous serez "{getRelationLabel(selectedRelationType as FamilyRelationType)}" pour {targetName}</span>
      </div>
    </div>
  );
}
