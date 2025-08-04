import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Smartphone, Tablet, Monitor } from 'lucide-react';

export function ResponsiveTestGrid() {
  return (
    <div className="space-y-8 p-4">
      {/* Responsive indicators */}
      <div className="flex items-center gap-4 justify-center">
        <div className="flex items-center gap-2 sm:hidden">
          <Smartphone className="h-4 w-4 text-blue-600" />
          <Badge variant="secondary" className="bg-blue-100 text-blue-800">Mobile</Badge>
        </div>
        <div className="hidden sm:flex md:hidden items-center gap-2">
          <Tablet className="h-4 w-4 text-green-600" />
          <Badge variant="secondary" className="bg-green-100 text-green-800">Tablet</Badge>
        </div>
        <div className="hidden md:flex items-center gap-2">
          <Monitor className="h-4 w-4 text-purple-600" />
          <Badge variant="secondary" className="bg-purple-100 text-purple-800">Desktop</Badge>
        </div>
      </div>

      {/* Grid responsive test */}
      <div>
        <h2 className="text-xl font-semibold mb-4">Test de Grille Responsive</h2>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          {Array.from({ length: 8 }, (_, i) => (
            <Card key={i} className="border-0 shadow-sm bg-gradient-to-br from-white to-gray-50">
              <CardHeader className="pb-2">
                <CardTitle className="text-sm">Carte {i + 1}</CardTitle>
              </CardHeader>
              <CardContent className="pt-0">
                <p className="text-xs text-gray-600">
                  Contenu de test pour vérifier la responsivité
                </p>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>

      {/* Typography responsive test */}
      <div>
        <h2 className="text-xl font-semibold mb-4">Test de Typographie Responsive</h2>
        <div className="space-y-4">
          <h1 className="text-2xl sm:text-3xl lg:text-4xl font-bold">
            Titre Principal Responsive
          </h1>
          <h2 className="text-xl sm:text-2xl lg:text-3xl font-semibold">
            Sous-titre Responsive
          </h2>
          <p className="text-sm sm:text-base lg:text-lg text-gray-600">
            Paragraphe avec taille responsive qui s'adapte à la taille de l'écran
          </p>
        </div>
      </div>

      {/* Spacing responsive test */}
      <div>
        <h2 className="text-xl font-semibold mb-4">Test d'Espacement Responsive</h2>
        <div className="space-y-2 sm:space-y-4 lg:space-y-6">
          <div className="p-2 sm:p-4 lg:p-6 bg-blue-50 rounded-lg">
            <p className="text-sm">Padding responsive: p-2 sm:p-4 lg:p-6</p>
          </div>
          <div className="p-2 sm:p-4 lg:p-6 bg-green-50 rounded-lg">
            <p className="text-sm">Espacement vertical: space-y-2 sm:space-y-4 lg:space-y-6</p>
          </div>
        </div>
      </div>

      {/* Button responsive test */}
      <div>
        <h2 className="text-xl font-semibold mb-4">Test de Boutons Responsive</h2>
        <div className="flex flex-col sm:flex-row gap-2 sm:gap-4">
          <button className="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg text-sm sm:text-base">
            Bouton Responsive
          </button>
          <button className="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg text-sm sm:text-base">
            Bouton Outline
          </button>
        </div>
      </div>

      {/* Container responsive test */}
      <div>
        <h2 className="text-xl font-semibold mb-4">Test de Conteneur Responsive</h2>
        <div className="max-w-sm sm:max-w-md lg:max-w-lg xl:max-w-xl mx-auto p-4 bg-gray-50 rounded-lg">
          <p className="text-sm text-center">
            Conteneur avec largeur maximale responsive:
            <br />
            <code className="text-xs bg-white px-2 py-1 rounded">
              max-w-sm sm:max-w-md lg:max-w-lg xl:max-w-xl
            </code>
          </p>
        </div>
      </div>

      {/* Breakpoints info */}
      <div>
        <h2 className="text-xl font-semibold mb-4">Points de Rupture Tailwind</h2>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <Card className="border-0 shadow-sm">
            <CardContent className="p-4 text-center">
              <h3 className="font-semibold text-sm">Mobile</h3>
              <p className="text-xs text-gray-600">0px - 639px</p>
              <p className="text-xs text-gray-600">Défaut</p>
            </CardContent>
          </Card>
          <Card className="border-0 shadow-sm">
            <CardContent className="p-4 text-center">
              <h3 className="font-semibold text-sm">Tablet</h3>
              <p className="text-xs text-gray-600">640px+</p>
              <p className="text-xs text-gray-600">sm:</p>
            </CardContent>
          </Card>
          <Card className="border-0 shadow-sm">
            <CardContent className="p-4 text-center">
              <h3 className="font-semibold text-sm">Desktop</h3>
              <p className="text-xs text-gray-600">768px+</p>
              <p className="text-xs text-gray-600">md:</p>
            </CardContent>
          </Card>
          <Card className="border-0 shadow-sm">
            <CardContent className="p-4 text-center">
              <h3 className="font-semibold text-sm">Large</h3>
              <p className="text-xs text-gray-600">1024px+</p>
              <p className="text-xs text-gray-600">lg:</p>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
