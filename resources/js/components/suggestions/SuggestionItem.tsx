
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

  // Function to get simplified relation categories for dropdown
  const getRelationOptions = () => {
    const targetGender = correctedSuggestion.profiles?.gender;
    
    // Simple categories with most common relations
    const options = [
      { value: targetGender === 'F' ? 'sister' : 'brother', label: targetGender === 'F' ? 'Sœur' : 'Frère' },
      { value: targetGender === 'F' ? 'niece' : 'nephew', label: targetGender === 'F' ? 'Nièce' : 'Neveu' },
      { value: targetGender === 'F' ? 'cousin_paternal_f' : 'cousin_paternal_m', label: 'Cousin(e)' },
      { value: targetGender === 'F' ? 'aunt' : 'uncle', label: targetGender === 'F' ? 'Tante' : 'Oncle' },
      { value: 'other', label: 'Autre...' }
    ];
    
    return options;
  };

  const handleRelationSelect = (value: string) => {
    setSelectedRelationType(value);
    if (value === 'other') {
      setShowRelationSelect(true);
    }
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
      
      {/* Quick relation selector dropdown directly in the suggestion card */}
      {!showRelationSelect && (
        <div className="mb-3 mt-2">
          <Select 
            value={selectedRelationType} 
            onValueChange={handleRelationSelect}
          >
            <SelectTrigger className="w-full bg-white border-amber-300">
              <SelectValue placeholder="Sélectionner une relation" />
            </SelectTrigger>
            <SelectContent className="bg-white">
              {getRelationOptions().map(option => (
                <SelectItem key={option.value} value={option.value}>
                  {option.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
          {selectedRelationType && selectedRelationType !== 'other' && (
            <div className="mt-2 text-xs text-amber-700">
              {targetName} pourrait être votre "{getRelationLabel(selectedRelationType as FamilyRelationType)}"
            </div>
          )}
        </div>
      )}
      
      {showRelationSelect && (
        <RelationSelector
          selectedRelationType={selectedRelationType === 'other' ? '' : selectedRelationType}
          setSelectedRelationType={setSelectedRelationType}
          targetGender={correctedSuggestion.profiles?.gender}
        />
      )}
      
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
    </div>
  );
}
