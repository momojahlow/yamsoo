
import React from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Users, Plus, Heart } from 'lucide-react';
import { router } from '@inertiajs/react';

export function EmptySuggestions() {
  return (
    <div className="flex items-center justify-center min-h-[60vh] px-4">
      <Card className="max-w-lg mx-auto border-0 shadow-xl bg-gradient-to-br from-white to-gray-50">
        <CardContent className="text-center py-12 px-6 sm:px-8">
          {/* Modern icon with gradient */}
          <div className="relative mb-8">
            <div className="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto shadow-lg">
              <Users className="w-10 h-10 sm:w-12 sm:h-12 text-white" />
            </div>
            <div className="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-pink-500 to-red-500 rounded-full flex items-center justify-center">
              <Heart className="w-4 h-4 text-white" />
            </div>
          </div>

          <h3 className="text-xl sm:text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent mb-4">
            Aucune suggestion pour le moment
          </h3>

          <p className="text-gray-600 mb-8 text-sm sm:text-base leading-relaxed">
            Nous n'avons pas encore trouvé de suggestions de relations familiales pour vous.
            <br className="hidden sm:block" />
            Commencez par explorer notre réseau ou ajoutez des membres à votre famille.
          </p>

          <div className="space-y-4">
            <Button
              className="w-full h-12 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium transition-all duration-200 shadow-lg hover:shadow-xl"
              onClick={() => router.visit('/reseaux')}
            >
              <Plus className="w-5 h-5 mr-2" />
              Explorer le Réseau
            </Button>

            <Button
              variant="outline"
              className="w-full h-12 border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-200"
              onClick={() => router.visit('/famille')}
            >
              <Users className="w-5 h-5 mr-2" />
              Voir ma Famille
            </Button>
          </div>

          <div className="mt-8 pt-6 border-t border-gray-100">
            <p className="text-xs text-gray-500">
              Les suggestions apparaîtront automatiquement lorsque nous détecterons des connexions potentielles
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
