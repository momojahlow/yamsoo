
import { Suggestion } from "./types";

export function correctSuggestedRelation(suggestion: Suggestion): Suggestion {
  // Create a deep copy to avoid mutating the original object
  const correctedSuggestion = { ...suggestion };
  
  // Si la raison mentionne un partage de parent, la relation devrait être sibling
  if (suggestion.reason && (
      suggestion.reason.includes("mère →") || 
      suggestion.reason.includes("père →") ||
      suggestion.reason.includes("partagez la même")
    )) {
    const targetGender = suggestion.profiles?.gender || '';
    correctedSuggestion.suggested_relation_type = targetGender === 'F' ? 'sister' : 'brother';
  }
  
  // Correction spécifique pour oncle/nièce et tante/neveu basée sur le genre des utilisateurs
  if (suggestion.reason && suggestion.reason.includes("frère → fille")) {
    // Si c'est la fille d'un frère, alors c'est une nièce
    correctedSuggestion.suggested_relation_type = 'niece';
  } else if (suggestion.reason && suggestion.reason.includes("frère → fils")) {
    // Si c'est le fils d'un frère, alors c'est un neveu
    correctedSuggestion.suggested_relation_type = 'nephew';
  } else if (suggestion.reason && suggestion.reason.includes("sœur → fille")) {
    // Si c'est la fille d'une sœur, alors c'est une nièce
    correctedSuggestion.suggested_relation_type = 'niece';
  } else if (suggestion.reason && suggestion.reason.includes("sœur → fils")) {
    // Si c'est le fils d'une sœur, alors c'est un neveu
    correctedSuggestion.suggested_relation_type = 'nephew';
  }
  
  return correctedSuggestion;
}

export function getTargetName(suggestion: Suggestion): string {
  return suggestion.target_name || 
         (suggestion.profiles ? 
           `${suggestion.profiles.first_name || ''} ${suggestion.profiles.last_name || ''}`.trim() : 
           'Cette personne');
}
