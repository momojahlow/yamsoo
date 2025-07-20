
import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Check, X, Edit } from 'lucide-react';
import { router } from '@inertiajs/react';

interface Suggestion {
  id: number;
  type: string;
  message?: string;
  status: 'pending' | 'accepted' | 'rejected';
  created_at: string;
  suggested_relation_code?: string;
  suggested_relation_name?: string;
  suggested_user: {
    id: number;
    name: string;
    email: string;
    profile?: {
      avatar_url?: string;
    };
  };
}

interface Props {
  suggestion: Suggestion;
  onAcceptWithRelation?: (suggestionId: number, relationCode: string) => void;
}

export function SuggestionActions({ suggestion, onAcceptWithRelation }: Props) {
  const [showRelationSelector, setShowRelationSelector] = useState(false);
  const [selectedRelation, setSelectedRelation] = useState(suggestion.suggested_relation_code || '');
  const handleAccept = () => {
    if (onAcceptWithRelation && selectedRelation) {
      onAcceptWithRelation(suggestion.id, selectedRelation);
    } else {
      // Utiliser Inertia pour envoyer la requête PATCH
      router.patch(`/suggestions/${suggestion.id}`, {
        status: 'accepted'
      });
    }
  };

  const handleAcceptWithCorrection = () => {
    if (onAcceptWithRelation && selectedRelation) {
      onAcceptWithRelation(suggestion.id, selectedRelation);
    }
  };

  const handleReject = () => {
    // Utiliser Inertia pour envoyer la requête PATCH
    router.patch(`/suggestions/${suggestion.id}`, {
      status: 'rejected'
    });
  };

  const relationOptions = [
    // Relations directes
    { value: 'father', label: 'Père' },
    { value: 'mother', label: 'Mère' },
    { value: 'son', label: 'Fils' },
    { value: 'daughter', label: 'Fille' },
    { value: 'brother', label: 'Frère' },
    { value: 'sister', label: 'Sœur' },
    { value: 'husband', label: 'Mari' },
    { value: 'wife', label: 'Épouse' },
    // Relations par alliance
    { value: 'father_in_law', label: 'Beau-père' },
    { value: 'mother_in_law', label: 'Belle-mère' },
    { value: 'brother_in_law', label: 'Beau-frère' },
    { value: 'sister_in_law', label: 'Belle-sœur' },
    { value: 'stepson', label: 'Beau-fils' },
    { value: 'stepdaughter', label: 'Belle-fille' },
  ];

  const handleDelete = () => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette suggestion ?')) {
      // Utiliser Inertia pour envoyer la requête DELETE
      router.delete(`/suggestions/${suggestion.id}`);
    }
  };

  if (suggestion.status !== 'pending') {
    return (
      <Button
        variant="outline"
        size="sm"
        onClick={handleDelete}
        className="text-red-600 hover:text-red-700"
      >
        Supprimer
      </Button>
    );
  }

  if (showRelationSelector) {
    return (
      <div className="space-y-3">
        <div className="p-3 bg-blue-50 rounded-lg border border-blue-200">
          <p className="text-sm font-medium text-blue-800 mb-2">
            Corriger la relation suggérée :
          </p>
          <Select value={selectedRelation} onValueChange={setSelectedRelation}>
            <SelectTrigger className="w-full bg-white">
              <SelectValue placeholder="Choisir la relation correcte" />
            </SelectTrigger>
            <SelectContent>
              {relationOptions.map((option) => (
                <SelectItem key={option.value} value={option.value}>
                  {option.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
          {selectedRelation && (
            <p className="text-xs text-blue-600 mt-1">
              {suggestion.suggested_user.name} sera ajouté(e) comme votre {relationOptions.find(r => r.value === selectedRelation)?.label.toLowerCase()}
            </p>
          )}
        </div>

        <div className="flex gap-2">
          <Button
            size="sm"
            onClick={handleAcceptWithCorrection}
            disabled={!selectedRelation}
            className="bg-green-600 hover:bg-green-700"
          >
            <Check className="w-4 h-4 mr-1" />
            Accepter avec correction
          </Button>
          <Button
            variant="outline"
            size="sm"
            onClick={() => setShowRelationSelector(false)}
            className="text-gray-600 hover:text-gray-700"
          >
            Annuler
          </Button>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-2">
      {suggestion.suggested_relation_name && (
        <div className="flex items-center justify-between p-2 bg-gray-50 rounded">
          <span className="text-sm text-gray-700">
            Relation suggérée : <strong>{suggestion.suggested_relation_name}</strong>
          </span>
          <Button
            variant="ghost"
            size="sm"
            onClick={() => setShowRelationSelector(true)}
            className="text-blue-600 hover:text-blue-700 h-6 px-2"
          >
            <Edit className="w-3 h-3 mr-1" />
            Corriger
          </Button>
        </div>
      )}

      <div className="flex gap-2">
        <Button
          size="sm"
          onClick={handleAccept}
          className="bg-green-600 hover:bg-green-700"
        >
          <Check className="w-4 h-4 mr-1" />
          Accepter
        </Button>
        <Button
          variant="outline"
          size="sm"
          onClick={handleReject}
          className="text-red-600 hover:text-red-700"
        >
          <X className="w-4 h-4 mr-1" />
          Rejeter
        </Button>
      </div>
    </div>
  );
}
