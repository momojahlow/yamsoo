
import React, { useState } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Users, Plus, RefreshCw } from 'lucide-react';
import { router } from '@inertiajs/react';

export function EmptySuggestions() {
  const [isRefreshing, setIsRefreshing] = useState(false);

  const handleRefreshSuggestions = () => {
    setIsRefreshing(true);
    router.post('/suggestions/refresh', {}, {
      onFinish: () => setIsRefreshing(false),
      onSuccess: () => {
        // Recharger la page pour afficher les nouvelles suggestions
        router.reload();
      }
    });
  };

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
          Suggestions de Relations
        </h1>
        <p className="text-gray-600 dark:text-gray-400 mt-2">
          Gérez vos suggestions de connexions familiales
        </p>
      </div>

      <Card className="max-w-md mx-auto">
        <CardContent className="text-center py-12">
          <div className="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
            <Users className="w-8 h-8 text-gray-400" />
          </div>

          <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            Aucune suggestion
          </h3>

          <p className="text-gray-600 dark:text-gray-400 mb-6">
            Vous n'avez pas encore reçu de suggestions de relations familiales.
            Ajoutez des membres à votre famille pour générer des suggestions automatiques.
          </p>

          <div className="space-y-3">
            <Button
              className="w-full"
              onClick={handleRefreshSuggestions}
              disabled={isRefreshing}
            >
              <RefreshCw className={`w-4 h-4 mr-2 ${isRefreshing ? 'animate-spin' : ''}`} />
              {isRefreshing ? 'Génération en cours...' : 'Générer des suggestions'}
            </Button>

            <Button variant="outline" className="w-full" onClick={() => router.visit('/reseaux')}>
              <Plus className="w-4 h-4 mr-2" />
              Explorer les Réseaux
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
