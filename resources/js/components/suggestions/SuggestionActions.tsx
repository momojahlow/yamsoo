
import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Send, X } from 'lucide-react';
import { router } from '@inertiajs/react';

interface SuggestionActionsProps {
  showRelationSelect: boolean;
  setShowRelationSelect: (show: boolean) => void;
  onReject: () => void;
  onSendRequest: () => void;
  isLoading: boolean;
  hasSelectedRelation: boolean;
  suggestionId: string;
  isFamilySuggestion: boolean;
}

export function SuggestionActions({
  showRelationSelect,
  setShowRelationSelect,
  onReject,
  onSendRequest,
  isLoading,
  hasSelectedRelation,
  suggestionId,
  isFamilySuggestion
}: SuggestionActionsProps) {
  return (
    <div className="flex gap-2 mt-2">
      {/* Bouton Envoyer une demande - plus petit et user-friendly */}
      <Button
        size="sm"
        onClick={onSendRequest}
        disabled={!hasSelectedRelation || isLoading}
        className="bg-orange-600 hover:bg-orange-700 h-8 text-sm px-4 flex-1"
      >
        {isLoading ? (
          <div className="animate-spin rounded-full h-3 w-3 border-b-2 border-white mr-2"></div>
        ) : (
          <Send className="w-3 h-3 mr-2" />
        )}
        Envoyer une demande
      </Button>

      {/* Plus de bouton Rejeter - supprimé pour simplifier l'interface */}
    </div>
  );
}


