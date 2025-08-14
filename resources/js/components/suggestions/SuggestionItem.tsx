
import React, { useState } from "react";
import { Button } from "@/components/ui/button";
import { getRelationLabel } from "@/utils/relationUtils";
import { SuggestionHeader } from "./SuggestionHeader";
import { SuggestionReason } from "./SuggestionReason";
import { SuggestionActions } from "./SuggestionActions";
import { RelationSelector } from "./RelationSelector";
import { Suggestion } from "./types";
import { correctSuggestedRelation, getTargetName } from "./utils";
import { FamilyRelationType } from "@/types/family";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";

interface SuggestionItemProps {
  suggestion: Suggestion;
  onAccept: (id: string, selectedRelationType?: string) => Promise<void>;
  onReject: (id: string) => Promise<void>;
}

export function SuggestionItem({ suggestion, onAccept, onReject }: SuggestionItemProps) {
  if (!suggestion || !suggestion.id) {
    console.error("Invalid suggestion received:", suggestion);
    return null;
  }
  
  // Apply relation correction before any processing
  const correctedSuggestion = correctSuggestedRelation(suggestion);
  
  const [showRelationSelect, setShowRelationSelect] = useState(false);
  const [selectedRelationType, setSelectedRelationType] = useState<string>(correctedSuggestion.suggested_relation_code || '');
  const [isLoading, setIsLoading] = useState(false);

  // Determine if this is a family-derived suggestion
  const isFamilySuggestion = correctedSuggestion.id?.startsWith('family-suggestion-');

  // Use the French name if available, otherwise use the relation label from code
  const relationLabel = correctedSuggestion.suggested_relation_name ||
                       getRelationLabel(correctedSuggestion.suggested_relation_code as FamilyRelationType);
  
  const targetName = getTargetName(correctedSuggestion);
  
  const handleSendRequest = async () => {
    if (!selectedRelationType) {
      return;
    }
    
    setIsLoading(true);
    try {
      // Process the selected relation
      await onAccept(correctedSuggestion.id, selectedRelationType);
    } catch (error) {
      console.error("Error sending family request:", error);
    } finally {
      setIsLoading(false);
    }
  };

  // Function to get complete relation categories for dropdown (like RelationSelector)
  const getRelationOptions = () => {
    const targetGender = correctedSuggestion.profiles?.gender;

    // Complete list of relations organized by category
    const relationCategories = {
      family: [
        "father", "mother", "son", "daughter",
        "brother", "sister",
        "grandfather", "grandmother", "grandson", "granddaughter",
        "uncle", "aunt", "nephew", "niece",
        "cousin", "cousin_paternal_m", "cousin_maternal_m",
        "cousin_paternal_f", "cousin_maternal_f",
        "half_brother", "half_sister"
      ],
      spouse: ["husband", "wife"],
      inlaws: [
        "father_in_law", "mother_in_law", "son_in_law", "daughter_in_law",
        "brother_in_law", "sister_in_law",
        "stepfather", "stepmother", "stepson", "stepdaughter"
      ]
    };

    // Filter by gender
    const filterByGender = (types: string[]) => {
      if (!targetGender) return types;

      const maleSpecific = [
        "father", "son", "brother", "grandfather", "grandson", "uncle", "nephew",
        "husband", "father_in_law", "son_in_law", "brother_in_law", "stepfather",
        "stepson", "half_brother", "cousin_paternal_m", "cousin_maternal_m"
      ];

      const femaleSpecific = [
        "mother", "daughter", "sister", "grandmother", "granddaughter", "aunt", "niece",
        "wife", "mother_in_law", "daughter_in_law", "sister_in_law", "stepmother",
        "stepdaughter", "half_sister", "cousin_paternal_f", "cousin_maternal_f"
      ];

      if (targetGender === 'M') {
        return types.filter(type => !femaleSpecific.includes(type));
      } else if (targetGender === 'F') {
        return types.filter(type => !maleSpecific.includes(type));
      }

      return types;
    };

    // Create grouped options
    const options: Array<{value: string, label: string, group?: string}> = [];

    // Add family relations
    filterByGender(relationCategories.family).forEach(type => {
      options.push({
        value: type,
        label: getRelationLabel(type as FamilyRelationType),
        group: 'Famille'
      });
    });

    // Add spouse relations
    filterByGender(relationCategories.spouse).forEach(type => {
      options.push({
        value: type,
        label: getRelationLabel(type as FamilyRelationType),
        group: 'Époux/Épouse'
      });
    });

    // Add in-law relations
    filterByGender(relationCategories.inlaws).forEach(type => {
      options.push({
        value: type,
        label: getRelationLabel(type as FamilyRelationType),
        group: 'Belle-famille'
      });
    });

    return options;
  };

  const handleRelationSelect = (value: string) => {
    setSelectedRelationType(value);
    // Plus besoin de logique "other" car on a toutes les relations dans le dropdown principal
  };

  return (
    <div className="border rounded-lg p-3 bg-white shadow-sm">
      <SuggestionHeader 
        name={targetName}
        avatarUrl={correctedSuggestion.target_avatar_url}
        relationLabel={relationLabel}
      />
      
      <SuggestionReason reason={correctedSuggestion.reason} />
      
      {isFamilySuggestion && (
        <div className="text-sm text-amber-600 mb-1">
          Cette personne fait partie de la famille élargie
        </div>
      )}
      
      {/* Afficher "Demande en cours" ou le sélecteur de relation */}
      {correctedSuggestion.has_pending_request ? (
        <div className="mb-3 mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
          <div className="flex items-center gap-2">
            <div className="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
            <span className="text-sm font-medium text-blue-700">Demande en cours</span>
          </div>
          <p className="text-xs text-blue-600 mt-1">
            Une demande de relation a déjà été envoyée à cette personne
          </p>
        </div>
      ) : (
        <div className="mb-3 mt-2">
          <Select
            value={selectedRelationType}
            onValueChange={handleRelationSelect}
          >
            <SelectTrigger className="w-full h-9 bg-white border-gray-300 text-sm">
              <SelectValue placeholder="Sélectionner une relation" />
            </SelectTrigger>
            <SelectContent className="bg-white max-h-60 overflow-y-auto">
              {/* Group by categories */}
              <div className="py-1.5 pl-2 text-xs font-semibold text-gray-500">Famille</div>
              {getRelationOptions()
                .filter(option => option.group === 'Famille')
                .map(option => (
                  <SelectItem key={option.value} value={option.value} className="text-sm">
                    {option.label}
                  </SelectItem>
                ))}

              <div className="py-1.5 pl-2 text-xs font-semibold text-gray-500">Époux/Épouse</div>
              {getRelationOptions()
                .filter(option => option.group === 'Époux/Épouse')
                .map(option => (
                  <SelectItem key={option.value} value={option.value} className="text-sm">
                    {option.label}
                  </SelectItem>
                ))}

              <div className="py-1.5 pl-2 text-xs font-semibold text-gray-500">Belle-famille</div>
              {getRelationOptions()
                .filter(option => option.group === 'Belle-famille')
                .map(option => (
                  <SelectItem key={option.value} value={option.value} className="text-sm">
                    {option.label}
                  </SelectItem>
                ))}
            </SelectContent>
          </Select>
          {selectedRelationType && (
            <div className="mt-1 text-xs text-gray-600">
              {targetName} sera votre "{getRelationLabel(selectedRelationType as FamilyRelationType)}"
            </div>
          )}
        </div>
      )}
      
      {/* Plus besoin du RelationSelector étendu car tout est dans le dropdown principal */}
      
      {/* Afficher les actions seulement si aucune demande n'est en cours */}
      {!correctedSuggestion.has_pending_request && (
        <SuggestionActions
          showRelationSelect={showRelationSelect}
          setShowRelationSelect={setShowRelationSelect}
          onReject={() => onReject(correctedSuggestion.id)}
          onSendRequest={handleSendRequest}
          isLoading={isLoading}
          hasSelectedRelation={!!selectedRelationType && selectedRelationType !== 'other'}
          suggestionId={correctedSuggestion.id}
          isFamilySuggestion={isFamilySuggestion}
        />
      )}
    </div>
  );
}
