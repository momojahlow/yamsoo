
import React from "react";

interface SuggestionReasonProps {
  reason?: string;
}

export function SuggestionReason({ reason }: SuggestionReasonProps) {
  if (!reason) return null;

  // Format the reason text, handling special formatting like strikethrough
  const formatReason = (reason: string): JSX.Element => {
    // Check if the reason contains strikethrough indicators
    if (reason.includes(" → ")) {
      // Split by the arrow to identify the strikethrough part
      const parts = reason.split(" → ");
      if (parts.length >= 2) {
        return (
          <>
            <span className="text-amber-600">{parts[0]}</span>
            {" → "}
            <span>{parts.slice(1).join(" → ")}</span>
          </>
        );
      }
    }
    
    // Regular formatting for parent relation reasons
    const getParentNameFromReason = (text: string): string => {
      // Extract the name from text like "Relation suggérée via fati Tifouri (frère → fille)"
      // We need to get the name before the parentheses
      const parenthesisPos = text.indexOf('(');
      if (parenthesisPos > 0) {
        // Extract everything before the parenthesis and trim
        let textBeforeParenthesis = text.substring(0, parenthesisPos).trim();
        
        // If the text contains "via", extract the name after "via"
        if (textBeforeParenthesis.includes("via")) {
          const parts = textBeforeParenthesis.split("via");
          if (parts.length > 1) {
            return parts[1].trim();
          }
        }
        
        return textBeforeParenthesis;
      }
      
      // Fallback to original pattern if no parentheses found
      const matchParent = text.match(/\((.*?)\)/);
      return matchParent ? matchParent[1] : '';
    };
    
    const parentName = getParentNameFromReason(reason);
    
    // If the reason contains "même père" but it's a woman (Aicha), 
    // replace "père" by "mère"
    if (parentName.includes("Aicha") && reason.includes("même père")) {
      return <>{reason.replace("même père", "même mère")}</>;
    }
    
    // If the reason contains a mention of a common parent
    if (reason.includes("mère →") || reason.includes("père →")) {
      const parentGender = reason.includes("mère →") ? "mère" : "père";
      return <>{`Vous partagez la même ${parentGender} ${parentName ? '(' + parentName + ')' : ''}`}</>;
    }
    
    // For family relation suggestions (via a sibling)
    if (reason.includes("frère →") || reason.includes("sœur →")) {
      // If this is a suggestion via someone (like "Relation suggérée via fati Tifouri (frère → fille)")
      if (reason.includes("via")) {
        // Extract the person name after "via" but before the parenthesis
        const viaPattern = /via\s+([^(]+)/;
        const match = reason.match(viaPattern);
        const personName = match ? match[1].trim() : parentName;
        
        return (
          <>
            <span className="text-amber-600">Relation suggérée via {personName}</span>
          </>
        );
      }
      
      // For other sibling-based relations, format without showing relation path
      let relationText = "";
      
      if (reason.includes("frère → fils")) {
        relationText = "est le fils de votre frère";
      } else if (reason.includes("frère → fille")) {
        relationText = "est la fille de votre frère";
      } else if (reason.includes("sœur → fils")) {
        relationText = "est le fils de votre sœur";
      } else if (reason.includes("sœur → fille")) {
        relationText = "est la fille de votre sœur";
      }
      
      // Format with colored name and relation text
      return (
        <>
          <span className="text-amber-600 font-medium">{parentName}</span> {relationText}
        </>
      );
    }
    
    return <>{reason}</>;
  };

  return (
    <p className="mb-2 text-sm text-slate-700 bg-amber-100 p-2 rounded italic">
      {formatReason(reason)}
    </p>
  );
}
