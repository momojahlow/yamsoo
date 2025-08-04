
import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { EmptySuggestions } from '@/components/suggestions/EmptySuggestions';
import { ModernSuggestionCard } from '@/components/suggestions/ModernSuggestionCard';
import { FloatingLogoutButton } from '@/components/FloatingLogoutButton';

import AppSidebarLayout from '@/Layouts/app/app-sidebar-layout';

interface Suggestion {
  id: number;
  type: string;
  message?: string;
  status: 'pending' | 'accepted' | 'rejected';
  created_at: string;
  suggested_relation_code?: string;
  suggested_relation_name?: string;
  has_pending_request?: boolean; // Indique si une demande de relation est en cours
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

  const handleSendRelationRequest = async (suggestionId: string, relationCode?: string) => {
    if (!relationCode) {
      return;
    }

    try {
      // Utiliser Inertia pour envoyer la demande sans recharger la page
      router.post(`/suggestions/${suggestionId}/send-request`, {
        relation_code: relationCode,
      }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          // Recharger seulement les données des suggestions
          router.reload({ only: ['suggestions', 'pendingSuggestions', 'acceptedSuggestions'] });
        },
        onError: (errors) => {
          console.error('Erreur lors de l\'envoi de la demande:', errors);
        }
      });
    } catch (error) {
      console.error('Erreur:', error);
    }
  };

  const handleRejectSuggestion = async (suggestionId: string) => {
    // Utiliser Inertia pour rejeter la suggestion
    router.patch(`/suggestions/${suggestionId}`, {
      status: 'rejected',
    }, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        // Recharger la page pour voir les changements
        window.location.reload();
      },
      onError: (errors) => {
        console.error('Erreur lors du rejet:', errors);
      }
    });
  };

  const pendingSuggestions = suggestions.filter(s => s.status === 'pending');
  const acceptedSuggestions = suggestions.filter(s => s.status === 'accepted');
  const rejectedSuggestions = suggestions.filter(s => s.status === 'rejected');

  if (suggestions.length === 0) {
    return (
      <AppSidebarLayout>
        <Head title="Suggestions" />
        <EmptySuggestions />
      </AppSidebarLayout>
    );
  }

  return (
    <AppSidebarLayout>
      <Head title="Suggestions" />

      <div className="min-h-screen bg-gradient-to-br from-gray-50 to-white">
        <div className="w-full max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-6 md:py-8 lg:py-12">
          {/* Modern header - Mobile optimized */}
          <div className="mb-6 sm:mb-8 md:mb-12">
            <div className="text-center sm:text-left">
              <h1 className="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent leading-tight">
                Suggestions de Relations
              </h1>
              <p className="text-gray-600 mt-2 sm:mt-3 text-xs sm:text-sm md:text-base max-w-2xl mx-auto sm:mx-0 leading-relaxed">
                Découvrez et connectez-vous avec les membres de votre famille
              </p>
            </div>
          </div>

          {/* Modern suggestions grid - Mobile optimized */}
          {pendingSuggestions.length > 0 ? (
            <div className="space-y-4 sm:space-y-6 md:space-y-8">
              <div className="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                <h2 className="text-lg sm:text-xl md:text-2xl font-semibold text-gray-900">
                  Suggestions pour vous
                </h2>
                <Badge
                  variant="secondary"
                  className="bg-blue-100 text-blue-800 border-blue-200 px-2 py-1 text-xs sm:px-3 w-fit"
                >
                  {pendingSuggestions.length}
                </Badge>
              </div>

              {/* Responsive grid - Mobile first */}
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-6">
                {pendingSuggestions.map((suggestion) => (
                  <ModernSuggestionCard
                    key={suggestion.id}
                    suggestion={{
                      id: suggestion.id.toString(),
                      target_name: suggestion.suggested_user.name,
                      target_avatar_url: suggestion.suggested_user.profile?.avatar_url,
                      reason: suggestion.message || '',
                      has_pending_request: suggestion.has_pending_request,
                      profiles: {
                        first_name: suggestion.suggested_user.profile?.first_name || null,
                        last_name: suggestion.suggested_user.profile?.last_name || null,
                        avatar_url: suggestion.suggested_user.profile?.avatar_url || null,
                        gender: suggestion.suggested_user.profile?.gender || null,
                      }
                    }}
                    onSendRequest={handleSendRelationRequest}
                  />
                ))}
              </div>
            </div>
          ) : (
            <EmptySuggestions />
          )}

          {/* Suggestions acceptées */}
          {acceptedSuggestions.length > 0 && (
            <div className="mt-12 space-y-6">
              <div className="flex items-center gap-3">
                <h2 className="text-xl sm:text-2xl font-semibold text-gray-900">
                  Demandes envoyées
                </h2>
                <Badge
                  variant="secondary"
                  className="bg-green-100 text-green-800 border-green-200 px-3 py-1"
                >
                  {acceptedSuggestions.length}
                </Badge>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {acceptedSuggestions.map((suggestion) => (
                  <Card key={suggestion.id} className="border-0 shadow-sm bg-gradient-to-br from-green-50 to-emerald-50">
                    <CardContent className="p-4">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                          {suggestion.suggested_user.profile?.avatar_url ? (
                            <img
                              src={suggestion.suggested_user.profile.avatar_url}
                              alt={suggestion.suggested_user.name}
                              className="w-full h-full rounded-full object-cover"
                            />
                          ) : (
                            <span className="text-sm sm:text-base font-semibold text-green-700">
                              {suggestion.suggested_user.name.charAt(0).toUpperCase()}
                            </span>
                          )}
                        </div>
                        <div className="flex-1 min-w-0">
                          <h3 className="font-semibold text-gray-900 text-sm sm:text-base truncate">
                            {suggestion.suggested_user.name}
                          </h3>
                          <p className="text-xs sm:text-sm text-green-600 mt-1">
                            Demande envoyée le {new Date(suggestion.created_at).toLocaleDateString()}
                          </p>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            </div>
          )}

          {/* Suggestions rejetées */}
          {rejectedSuggestions.length > 0 && (
            <div className="mt-12 space-y-6">
              <div className="flex items-center gap-3">
                <h2 className="text-xl sm:text-2xl font-semibold text-gray-900">
                  Suggestions ignorées
                </h2>
                <Badge
                  variant="secondary"
                  className="bg-red-100 text-red-800 border-red-200 px-3 py-1"
                >
                  {rejectedSuggestions.length}
                </Badge>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {rejectedSuggestions.map((suggestion) => (
                  <Card key={suggestion.id} className="border-0 shadow-sm bg-gradient-to-br from-red-50 to-rose-50">
                    <CardContent className="p-4">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                          {suggestion.suggested_user.profile?.avatar_url ? (
                            <img
                              src={suggestion.suggested_user.profile.avatar_url}
                              alt={suggestion.suggested_user.name}
                              className="w-full h-full rounded-full object-cover"
                            />
                          ) : (
                            <span className="text-sm sm:text-base font-semibold text-red-700">
                              {suggestion.suggested_user.name.charAt(0).toUpperCase()}
                            </span>
                          )}
                        </div>
                        <div className="flex-1 min-w-0">
                          <h3 className="font-semibold text-gray-900 text-sm sm:text-base truncate">
                            {suggestion.suggested_user.name}
                          </h3>
                          <p className="text-xs sm:text-sm text-red-600 mt-1">
                            Ignorée le {new Date(suggestion.created_at).toLocaleDateString()}
                          </p>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            </div>
          )}

          <FloatingLogoutButton showOnMobile={true} showOnDesktop={false} />
        </div>
      </div>
    </AppSidebarLayout>
  );
}
