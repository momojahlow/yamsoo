
import React from "react";
import { SuggestionItem } from "./SuggestionItem";
import { EmptySuggestions } from "./EmptySuggestions";
import { Suggestion } from "./types";

interface SuggestionsListProps {
  suggestions: Suggestion[];
  loading: boolean;
  onAcceptSuggestion: (id: string, selectedRelationType?: string) => Promise<void>;
  onRejectSuggestion: (id: string) => Promise<void>;
}

export function SuggestionsList({
  suggestions,
  loading,
  onAcceptSuggestion,
  onRejectSuggestion,
}: SuggestionsListProps) {
  if (loading) {
    return (
      <div className="space-y-4">
        {[...Array(3)].map((_, index) => (
          <div
            key={index}
            className="border rounded-lg p-4 bg-gray-50 animate-pulse h-40"
          />
        ))}
      </div>
    );
  }

  if (suggestions.length === 0) {
    return <EmptySuggestions />;
  }

  return (
    <div className="space-y-4">
      {suggestions.map((suggestion) => (
        <SuggestionItem
          key={suggestion.id}
          suggestion={suggestion}
          onAccept={onAcceptSuggestion}
          onReject={onRejectSuggestion}
        />
      ))}
    </div>
  );
}
