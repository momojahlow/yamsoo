
import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { EmptySuggestions } from '@/components/suggestions/EmptySuggestions';
import { SuggestionActions } from '@/components/suggestions/SuggestionActions';
import { RelationSelector } from '@/components/suggestions/RelationSelector';
import { FloatingLogoutButton } from '@/components/FloatingLogoutButton';
import AppLayout from '@/layouts/app-layout';

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
  suggestions: Suggestion[];
}

export default function Suggestions({ suggestions }: Props) {
  const [selectedSuggestion, setSelectedSuggestion] = useState<Suggestion | null>(null);

  const handleAcceptWithCorrection = (suggestionId: number, relationCode: string) => {
    // Créer un formulaire pour envoyer la requête avec la relation corrigée
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/suggestions/${suggestionId}/accept-with-correction`;

    // Ajouter le token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
      const csrfInput = document.createElement('input');
      csrfInput.type = 'hidden';
      csrfInput.name = '_token';
      csrfInput.value = csrfToken;
      form.appendChild(csrfInput);
    }

    // Ajouter la méthode PATCH
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'PATCH';
    form.appendChild(methodInput);

    // Ajouter le code de relation
    const relationInput = document.createElement('input');
    relationInput.type = 'hidden';
    relationInput.name = 'relation_code';
    relationInput.value = relationCode;
    form.appendChild(relationInput);

    // Soumettre le formulaire
    document.body.appendChild(form);
    form.submit();
  };

  const pendingSuggestions = suggestions.filter(s => s.status === 'pending');
  const acceptedSuggestions = suggestions.filter(s => s.status === 'accepted');
  const rejectedSuggestions = suggestions.filter(s => s.status === 'rejected');

  if (suggestions.length === 0) {
    return (
      <>
        <Head title="Suggestions" />
        <EmptySuggestions />
      </>
    );
  }

  return (
    <AppLayout>
      <Head title="Suggestions" />

      <div className="container mx-auto px-4 py-8">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
            Suggestions de Relations
          </h1>
          <p className="text-gray-600 dark:text-gray-400 mt-2">
            Gérez vos suggestions de connexions familiales
          </p>
        </div>

        {/* Suggestions en attente */}
        {pendingSuggestions.length > 0 && (
          <Card className="mb-8">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                En attente
                <Badge variant="secondary">{pendingSuggestions.length}</Badge>
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {pendingSuggestions.map((suggestion) => (
                  <div
                    key={suggestion.id}
                    className="flex items-center justify-between p-4 border rounded-lg"
                  >
                    <div className="flex items-center gap-4">
                      <div className="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                        {suggestion.suggested_user.profile?.avatar_url ? (
                          <img
                            src={suggestion.suggested_user.profile.avatar_url}
                            alt={suggestion.suggested_user.name}
                            className="w-12 h-12 rounded-full object-cover"
                          />
                        ) : (
                          <span className="text-lg font-semibold text-gray-600">
                            {suggestion.suggested_user.name.charAt(0).toUpperCase()}
                          </span>
                        )}
                      </div>
                      <div>
                        <h3 className="font-semibold text-gray-900 dark:text-white">
                          {suggestion.suggested_user.name}
                        </h3>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                          {suggestion.type}
                        </p>
                        {suggestion.suggested_relation_name && (
                          <p className="text-sm font-medium text-blue-600 mt-1">
                            Relation suggérée : {suggestion.suggested_relation_name}
                          </p>
                        )}
                        {suggestion.message && (
                          <p className="text-sm text-gray-500 mt-1">
                            "{suggestion.message}"
                          </p>
                        )}
                      </div>
                    </div>
                    <SuggestionActions
                      suggestion={suggestion}
                      onAcceptWithRelation={handleAcceptWithCorrection}
                    />
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        )}

        {/* Suggestions acceptées */}
        {acceptedSuggestions.length > 0 && (
          <Card className="mb-8">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                Acceptées
                <Badge variant="success">{acceptedSuggestions.length}</Badge>
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {acceptedSuggestions.map((suggestion) => (
                  <div
                    key={suggestion.id}
                    className="flex items-center justify-between p-4 border rounded-lg bg-green-50 dark:bg-green-900/20"
                  >
                    <div className="flex items-center gap-4">
                      <div className="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                        {suggestion.suggested_user.profile?.avatar_url ? (
                          <img
                            src={suggestion.suggested_user.profile.avatar_url}
                            alt={suggestion.suggested_user.name}
                            className="w-12 h-12 rounded-full object-cover"
                          />
                        ) : (
                          <span className="text-lg font-semibold text-gray-600">
                            {suggestion.suggested_user.name.charAt(0).toUpperCase()}
                          </span>
                        )}
                      </div>
                      <div>
                        <h3 className="font-semibold text-gray-900 dark:text-white">
                          {suggestion.suggested_user.name}
                        </h3>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                          {suggestion.type}
                        </p>
                        <p className="text-sm text-green-600 dark:text-green-400">
                          Acceptée le {new Date(suggestion.created_at).toLocaleDateString()}
                        </p>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        )}

        {/* Suggestions rejetées */}
        {rejectedSuggestions.length > 0 && (
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                Rejetées
                <Badge variant="destructive">{rejectedSuggestions.length}</Badge>
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {rejectedSuggestions.map((suggestion) => (
                  <div
                    key={suggestion.id}
                    className="flex items-center justify-between p-4 border rounded-lg bg-red-50 dark:bg-red-900/20"
                  >
                    <div className="flex items-center gap-4">
                      <div className="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                        {suggestion.suggested_user.profile?.avatar_url ? (
                          <img
                            src={suggestion.suggested_user.profile.avatar_url}
                            alt={suggestion.suggested_user.name}
                            className="w-12 h-12 rounded-full object-cover"
                          />
                        ) : (
                          <span className="text-lg font-semibold text-gray-600">
                            {suggestion.suggested_user.name.charAt(0).toUpperCase()}
                          </span>
                        )}
                      </div>
                      <div>
                        <h3 className="font-semibold text-gray-900 dark:text-white">
                          {suggestion.suggested_user.name}
                        </h3>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                          {suggestion.type}
                        </p>
                        <p className="text-sm text-red-600 dark:text-red-400">
                          Rejetée le {new Date(suggestion.created_at).toLocaleDateString()}
                        </p>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        )}
      </div>
      <FloatingLogoutButton showOnMobile={true} showOnDesktop={false} />
    </AppLayout>
  );
}
