
import React from 'react';
import { Button } from '@/components/ui/button';
import { Check, X } from 'lucide-react';

interface Suggestion {
  id: number;
  type: string;
  message?: string;
  status: 'pending' | 'accepted' | 'rejected';
  created_at: string;
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
}

export function SuggestionActions({ suggestion }: Props) {
  const handleAccept = () => {
    // Utiliser Inertia pour envoyer la requête
    window.location.href = `/suggestions/${suggestion.id}?_method=PATCH&status=accepted`;
  };

  const handleReject = () => {
    // Utiliser Inertia pour envoyer la requête
    window.location.href = `/suggestions/${suggestion.id}?_method=PATCH&status=rejected`;
  };

  const handleDelete = () => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette suggestion ?')) {
      // Utiliser Inertia pour envoyer la requête
      window.location.href = `/suggestions/${suggestion.id}?_method=DELETE`;
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

  return (
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
  );
}
