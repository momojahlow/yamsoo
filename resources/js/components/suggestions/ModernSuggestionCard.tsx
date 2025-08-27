import React, { useState } from "react";
import { Button } from "@/components/ui/button";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import { Send, Clock, CheckCircle } from "lucide-react";
import { getRelationLabel } from "@/utils/relationUtils";
import { FamilyRelationType } from "@/types/family";

interface ModernSuggestionCardProps {
  suggestion: {
    id: string;
    target_name?: string;
    target_avatar_url?: string;
    reason?: string;
    has_pending_request?: boolean;
    profiles?: {
      first_name: string | null;
      last_name: string | null;
      avatar_url: string | null;
      gender?: string | null;
    };
  };
  onSendRequest: (id: string, relationCode: string) => Promise<void>;
  isLoading?: boolean;
}

export function ModernSuggestionCard({
  suggestion,
  onSendRequest,
  isLoading = false
}: ModernSuggestionCardProps) {
  const [selectedRelationType, setSelectedRelationType] = useState<string>('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const targetName = suggestion.target_name || 'Utilisateur';
  const targetGender = suggestion.profiles?.gender;

  // Complete list of relations organized by category
  const getRelationOptions = () => {
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

  const handleSendRequest = async () => {
    if (!selectedRelationType || isSubmitting) return;

    setIsSubmitting(true);
    try {
      await onSendRequest(suggestion.id, selectedRelationType);
    } catch (error) {
      console.error('Error sending request:', error);
    } finally {
      setIsSubmitting(false);
    }
  };

  // Extract simplified reason
  const getSimplifiedReason = (reason?: string): string => {
    if (!reason) return '';

    // Extract name from various reason formats
    if (reason.includes("Via ") && reason.includes(" - ")) {
      const viaMatch = reason.match(/Via\s+([^-]+)/);
      if (viaMatch) {
        return `Via ${viaMatch[1].trim()}`;
      }
    }

    if (reason.includes("via ")) {
      const viaPattern = /via\s+([^(]+)/;
      const match = reason.match(viaPattern);
      if (match) {
        return `Via ${match[1].trim()}`;
      }
    }

    return reason;
  };

  return (
    <Card className="group hover:shadow-lg transition-all duration-300 border-0 shadow-sm bg-gradient-to-br from-white to-gray-50/50 h-full w-full">
      <CardContent className="p-3 sm:p-4 md:p-6 h-full flex flex-col">
        {/* Header with avatar and name - Mobile optimized */}
        <div className="flex items-start gap-2 sm:gap-3 md:gap-4 mb-3 sm:mb-4">
          <Avatar className="h-10 w-10 sm:h-12 sm:w-12 md:h-14 md:w-14 ring-2 ring-orange-100 group-hover:ring-orange-200 transition-all flex-shrink-0">
            <AvatarImage
              src={suggestion.target_avatar_url || suggestion.profiles?.avatar_url || ''}
              alt={targetName}
            />
            <AvatarFallback className="bg-gradient-to-br from-orange-500 to-red-600 text-white font-semibold text-xs sm:text-sm">
              {targetName.charAt(0).toUpperCase()}
            </AvatarFallback>
          </Avatar>

          <div className="flex-1 min-w-0">
            <h3 className="font-semibold text-gray-900 text-sm sm:text-base md:text-lg truncate leading-tight">
              {targetName}
            </h3>
            <p className="text-xs sm:text-sm text-gray-600 mt-1 leading-tight">
              Connaissez-vous cette personne ?
            </p>

            {/* Simplified reason */}
            {suggestion.reason && (
              <div className="mt-1 sm:mt-2">
                <Badge variant="secondary" className="text-xs bg-orange-50 text-orange-700 border-orange-200 px-2 py-0.5">
                  {getSimplifiedReason(suggestion.reason)}
                </Badge>
              </div>
            )}
          </div>
        </div>

        {/* Pending request status */}
        {suggestion.has_pending_request ? (
          <div className="bg-gradient-to-r from-orange-50 to-red-50 border border-orange-200 rounded-xl p-3 sm:p-4 mb-4 flex-1 flex items-center">
            <div className="flex items-center gap-3 w-full">
              <div className="flex-shrink-0">
                <Clock className="h-4 w-4 sm:h-5 sm:w-5 text-orange-600 animate-pulse" />
              </div>
              <div className="flex-1">
                <p className="font-medium text-orange-900 text-xs sm:text-sm">Demande en attente</p>
                <p className="text-orange-700 text-xs mt-1 leading-tight">
                  Une demande de relation a été envoyée
                </p>
              </div>
            </div>
          </div>
        ) : (
          <div className="flex-1 flex flex-col">
            {/* Relation selector */}
            <div className="space-y-3 mb-4 flex-1">
              <Select value={selectedRelationType} onValueChange={setSelectedRelationType}>
                <SelectTrigger className="w-full h-10 sm:h-11 bg-white border-gray-200 hover:border-gray-300 focus:border-orange-500 transition-colors text-sm">
                  <SelectValue placeholder="Sélectionner une relation" />
                </SelectTrigger>
                <SelectContent className="bg-white max-h-60 overflow-y-auto">
                  {/* Group by categories */}
                  <div className="py-2 pl-3 text-xs font-semibold text-gray-500 bg-gray-50">Famille</div>
                  {getRelationOptions()
                    .filter(option => option.group === 'Famille')
                    .map(option => (
                      <SelectItem key={option.value} value={option.value} className="text-sm py-2">
                        {option.label}
                      </SelectItem>
                    ))}

                  <div className="py-2 pl-3 text-xs font-semibold text-gray-500 bg-gray-50">Époux/Épouse</div>
                  {getRelationOptions()
                    .filter(option => option.group === 'Époux/Épouse')
                    .map(option => (
                      <SelectItem key={option.value} value={option.value} className="text-sm py-2">
                        {option.label}
                      </SelectItem>
                    ))}

                  <div className="py-2 pl-3 text-xs font-semibold text-gray-500 bg-gray-50">Belle-famille</div>
                  {getRelationOptions()
                    .filter(option => option.group === 'Belle-famille')
                    .map(option => (
                      <SelectItem key={option.value} value={option.value} className="text-sm py-2">
                        {option.label}
                      </SelectItem>
                    ))}
                </SelectContent>
              </Select>

              {selectedRelationType && (
                <div className="text-xs text-gray-600 bg-gray-50 px-3 py-2 rounded-lg">
                  <CheckCircle className="inline h-3 w-3 text-green-600 mr-1" />
                  {targetName} sera votre "{getRelationLabel(selectedRelationType as FamilyRelationType)}"
                </div>
              )}
            </div>

            {/* Send request button */}
            <div className="mt-auto">
              <Button
                onClick={handleSendRequest}
                disabled={!selectedRelationType || isSubmitting || isLoading}
                className="w-full h-10 sm:h-11 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white font-medium transition-all duration-200 disabled:opacity-50 text-sm"
              >
                {isSubmitting || isLoading ? (
                  <div className="flex items-center gap-2">
                    <div className="animate-spin rounded-full h-3 w-3 sm:h-4 sm:w-4 border-b-2 border-white"></div>
                    <span className="hidden sm:inline">Envoi en cours...</span>
                    <span className="sm:hidden">Envoi...</span>
                  </div>
                ) : (
                  <div className="flex items-center gap-2">
                    <Send className="h-3 w-3 sm:h-4 sm:w-4" />
                    <span className="hidden sm:inline">Envoyer une demande</span>
                    <span className="sm:hidden">Envoyer</span>
                  </div>
                )}
              </Button>
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
