import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Props {
  canLogin: boolean;
  canRegister: boolean;
  laravelVersion: string;
  phpVersion: string;
}

export default function Welcome({ canLogin, canRegister, laravelVersion, phpVersion }: Props) {
  return (
    <>
      <Head title="Yamsoo - Connexions Familiales" />

      <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
        {/* Header */}
        <header className="container mx-auto px-4 py-6">
          <nav className="flex items-center justify-between">
            <div className="flex items-center space-x-2">
              <div className="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <span className="text-white font-bold text-lg">Y</span>
              </div>
              <span className="text-2xl font-bold text-gray-900 dark:text-white">Yamsoo</span>
            </div>

            <div className="flex items-center space-x-4">
              {canLogin && (
                <Link href={route('login')}>
                  <Button variant="ghost">Se connecter</Button>
                </Link>
              )}
              {canRegister && (
                <Link href={route('register')}>
                  <Button>S'inscrire</Button>
                </Link>
              )}
            </div>
          </nav>
        </header>

        {/* Hero Section */}
        <main className="container mx-auto px-4 py-16">
          <div className="text-center mb-16">
            <h1 className="text-5xl font-bold text-gray-900 dark:text-white mb-6">
              Connectez votre famille
            </h1>
            <p className="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
              Yamsoo vous aide à créer et maintenir des liens familiaux forts.
              Découvrez votre arbre généalogique, partagez des moments précieux
              et restez connecté avec vos proches.
            </p>
            <div className="flex justify-center space-x-4">
              <Link href={route('register')}>
                <Button size="lg" className="px-8 py-3">
                  Commencer gratuitement
                </Button>
              </Link>
              <Link href={route('login')}>
                <Button variant="outline" size="lg" className="px-8 py-3">
                  Se connecter
                </Button>
              </Link>
            </div>
          </div>

          {/* Features */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            <Card className="text-center">
              <CardHeader>
                <div className="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mx-auto mb-4">
                  <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <CardTitle>Arbre Généalogique</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-gray-600 dark:text-gray-400">
                  Créez et visualisez votre arbre généalogique interactif.
                  Découvrez vos racines et partagez votre histoire familiale.
                </p>
              </CardContent>
            </Card>

            <Card className="text-center">
              <CardHeader>
                <div className="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mx-auto mb-4">
                  <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                  </svg>
                </div>
                <CardTitle>Messagerie Familiale</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-gray-600 dark:text-gray-400">
                  Communiquez en privé avec vos proches. Partagez des photos,
                  des messages et restez connecté en temps réel.
                </p>
              </CardContent>
            </Card>

            <Card className="text-center">
              <CardHeader>
                <div className="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mx-auto mb-4">
                  <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 15h6v-2H4v2zM4 11h6V9H4v2zM4 7h6V5H4v2zM10 7h10V5H10v2zM10 11h10V9H10v2zM10 15h10v-2H10v2zM10 19h10v-2H10v2z" />
                  </svg>
                </div>
                <CardTitle>Suggestions Intelligentes</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-gray-600 dark:text-gray-400">
                  Recevez des suggestions de connexions familiales basées sur
                  vos relations existantes et votre réseau.
                </p>
              </CardContent>
            </Card>
          </div>

          {/* CTA Section */}
          <div className="text-center">
            <Card className="max-w-2xl mx-auto">
              <CardContent className="py-12">
                <h2 className="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                  Prêt à connecter votre famille ?
                </h2>
                <p className="text-gray-600 dark:text-gray-400 mb-8">
                  Rejoignez des milliers de familles qui utilisent Yamsoo pour
                  maintenir des liens forts et créer des souvenirs durables.
                </p>
                <Link href={route('register')}>
                  <Button size="lg" className="px-8 py-3">
                    Créer mon compte gratuitement
                  </Button>
                </Link>
              </CardContent>
            </Card>
          </div>
        </main>

        {/* Footer */}
        <footer className="container mx-auto px-4 py-8 border-t border-gray-200 dark:border-gray-700">
          <div className="text-center text-gray-600 dark:text-gray-400">
            <p>&copy; 2024 Yamsoo. Tous droits réservés.</p>
            <p className="text-sm mt-2">
              Laravel {laravelVersion} | PHP {phpVersion}
            </p>
          </div>
        </footer>
      </div>
    </>
  );
}
