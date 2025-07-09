
import React from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Network, Users } from 'lucide-react';

export function EmptyProfilesState() {
  return (
    <div className="container mx-auto px-4 py-8">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
          Réseaux
        </h1>
        <p className="text-gray-600 dark:text-gray-400 mt-2">
          Découvrez et connectez-vous avec d'autres utilisateurs
        </p>
      </div>

      <Card className="max-w-md mx-auto">
        <CardContent className="text-center py-12">
          <div className="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
            <Network className="w-8 h-8 text-gray-400" />
          </div>

          <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            Aucun utilisateur trouvé
          </h3>

          <p className="text-gray-600 dark:text-gray-400 mb-6">
            Il n'y a pas encore d'autres utilisateurs sur la plateforme.
            Invitez vos amis et votre famille à rejoindre Yamsoo !
          </p>

          <div className="space-y-3">
            <Button className="w-full">
              <Users className="w-4 h-4 mr-2" />
              Inviter des Amis
            </Button>

            <Button variant="outline" className="w-full">
              Partager Yamsoo
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
