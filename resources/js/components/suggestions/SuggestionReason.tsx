
import React from "react";

interface SuggestionReasonProps {
  reason?: string;
}

export function SuggestionReason({ reason }: SuggestionReasonProps) {
  if (!reason) return null;

  // Simplify the reason text to just show "Via [Name]"
  const formatReason = (reason: string): JSX.Element => {
    // Extract the name from various reason formats
    const extractNameFromReason = (text: string): string => {
      // Pattern 1: "Via Mohammed Alami - Parent - père via relation familiale avec Mohammed Alami"
      if (text.includes("Via ") && text.includes(" - ")) {
        const viaMatch = text.match(/Via\s+([^-]+)/);
        if (viaMatch) {
          return viaMatch[1].trim();
        }
      }

      // Pattern 2: "Relation suggérée via [Name] (relation details)"
      if (text.includes("via ")) {
        const viaPattern = /via\s+([^(]+)/;
        const match = text.match(viaPattern);
        if (match) {
          return match[1].trim();
        }
      }

      // Pattern 3: Extract name from parentheses or other patterns
      const parenthesisMatch = text.match(/\(([^)]+)\)/);
      if (parenthesisMatch) {
        return parenthesisMatch[1];
      }

      return '';
    };

    const extractedName = extractNameFromReason(reason);

    if (extractedName) {
      return (
        <>
          <span className="text-blue-600 font-medium">Via {extractedName}</span>
        </>
      );
    }

    // Fallback to original reason if no name extracted
    return <>{reason}</>;
  };

  return (
    <p className="mb-2 text-xs text-gray-600 bg-gray-50 px-2 py-1 rounded">
      {formatReason(reason)}
    </p>
  );
}
