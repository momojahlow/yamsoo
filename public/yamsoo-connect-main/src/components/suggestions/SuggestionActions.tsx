
import React from "react";
import { Button } from "@/components/ui/button";

interface SuggestionActionsProps {
  showRelationSelect: boolean;
  setShowRelationSelect: (show: boolean) => void;
  onReject: () => void;
  onSendRequest: () => void;
  isLoading: boolean;
  hasSelectedRelation: boolean;
  suggestionId: string;
  isFamilySuggestion?: boolean;
}

export function SuggestionActions({
  showRelationSelect,
  setShowRelationSelect,
  onReject,
  onSendRequest,
  isLoading,
  hasSelectedRelation,
  isFamilySuggestion
}: SuggestionActionsProps) {
  if (showRelationSelect) {
    return (
      <div className="flex justify-between mt-2 space-x-2">
        <Button 
          variant="outline" 
          onClick={() => setShowRelationSelect(false)}
          className="flex-1"
        >
          Annuler
        </Button>
        <Button
          onClick={onSendRequest}
          disabled={!hasSelectedRelation || isLoading}
          className="flex-1 bg-amber-600 hover:bg-amber-700"
        >
          {isLoading ? 'Envoi...' : 'Confirmer'}
        </Button>
      </div>
    );
  }
  
  return (
    <div className="flex justify-between mt-2 space-x-2">
      <Button 
        variant="outline" 
        onClick={onReject}
        className="flex-1"
      >
        {isFamilySuggestion ? "Pas intéressé" : "Rejeter"}
      </Button>
      <Button
        onClick={onSendRequest}
        disabled={!hasSelectedRelation || isLoading}
        className="flex-1 bg-amber-600 hover:bg-amber-700"
      >
        {hasSelectedRelation ? "Confirmer" : "Choisir une relation"}
      </Button>
    </div>
  );
}
