import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { route } from 'ziggy-js';
import {
  Heart,
  MessageSquare,
  Users,
  TreePine,
  ArrowRight,
  Star,
  Globe,
  Shield,
  Zap
} from 'lucide-react';

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

      <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
        {/* Header */}
        <header className="container mx-auto px-4 py-6">
          <nav className="flex items-center justify-between">
            <div className="flex items-center space-x-3">
              <div className="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                <span className="text-white font-bold text-xl">Y</span>
              </div>
              <span className="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                Yamsoo
              </span>
            </div>

            <div className="flex items-center space-x-4">
              {canLogin && (
                <Link href={route('login')}>
                  <Button variant="ghost" className="font-medium">Se connecter</Button>
                </Link>
              )}
              {canRegister && (
                <Link href={route('register')}>
                  <Button className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 shadow-lg">
                    S'inscrire
                  </Button>
                </Link>
              )}
            </div>
          </nav>
        </header>

        {/* Hero Section */}
        <main className="container mx-auto px-4 py-16">
          <div className="text-center mb-20">
            <div className="mb-8">
              <div className="inline-flex items-center px-4 py-2 bg-blue-100 dark:bg-blue-900/30 rounded-full text-blue-700 dark:text-blue-300 text-sm font-medium mb-6">
                <Star className="w-4 h-4 mr-2" />
                La plateforme familiale la plus avancée
              </div>
            </div>

            <h1 className="text-5xl md:text-6xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">
              Connectez votre
              <span className="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent"> famille</span>
            </h1>

            <p className="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-3xl mx-auto leading-relaxed">
              Yamsoo vous aide à créer et maintenir des liens familiaux forts.
              Découvrez votre arbre généalogique, partagez des moments précieux
              et restez connecté avec vos proches en temps réel.
            </p>

            <div className="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4 mb-12">
              <Link href={route('register')}>
                <Button size="lg" className="px-8 py-4 text-lg bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 shadow-xl">
                  Commencer gratuitement
                  <ArrowRight className="w-5 h-5 ml-2" />
                </Button>
              </Link>
              <Link href={route('login')}>
                <Button variant="outline" size="lg" className="px-8 py-4 text-lg border-2">
                  Se connecter
                </Button>
              </Link>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-2xl mx-auto">
              <div className="text-center">
                <div className="text-3xl font-bold text-blue-600 mb-2">10K+</div>
                <div className="text-gray-600 dark:text-gray-400">Familles connectées</div>
              </div>
              <div className="text-center">
                <div className="text-3xl font-bold text-purple-600 mb-2">50K+</div>
                <div className="text-gray-600 dark:text-gray-400">Relations créées</div>
              </div>
              <div className="text-center">
                <div className="text-3xl font-bold text-green-600 mb-2">99%</div>
                <div className="text-gray-600 dark:text-gray-400">Satisfaction client</div>
              </div>
            </div>
          </div>

          {/* Features */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20">
            <Card className="text-center border-0 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
              <CardHeader className="pb-4">
                <div className="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                  <TreePine className="w-8 h-8 text-white" />
                </div>
                <CardTitle className="text-xl">Arbre Généalogique</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-gray-600 dark:text-gray-400 leading-relaxed">
                  Créez et visualisez votre arbre généalogique interactif.
                  Découvrez vos racines et partagez votre histoire familiale
                  avec des outils de visualisation avancés.
                </p>
              </CardContent>
            </Card>

            <Card className="text-center border-0 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
              <CardHeader className="pb-4">
                <div className="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                  <MessageSquare className="w-8 h-8 text-white" />
                </div>
                <CardTitle className="text-xl">Messagerie Familiale</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-gray-600 dark:text-gray-400 leading-relaxed">
                  Communiquez en privé avec vos proches. Partagez des photos,
                  des messages et restez connecté en temps réel avec une
                  messagerie sécurisée et intuitive.
                </p>
              </CardContent>
            </Card>

            <Card className="text-center border-0 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
              <CardHeader className="pb-4">
                <div className="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                  <Users className="w-8 h-8 text-white" />
                </div>
                <CardTitle className="text-xl">Suggestions Intelligentes</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-gray-600 dark:text-gray-400 leading-relaxed">
                  Recevez des suggestions de connexions familiales basées sur
                  vos relations existantes et votre réseau. Notre IA vous aide
                  à découvrir de nouveaux liens familiaux.
                </p>
              </CardContent>
            </Card>
          </div>

          {/* Benefits Section */}
          <div className="mb-20">
            <div className="text-center mb-12">
              <h2 className="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                Pourquoi choisir Yamsoo ?
              </h2>
              <p className="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Une plateforme conçue spécifiquement pour les familles modernes
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div className="text-center p-6">
                <div className="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                  <Shield className="w-6 h-6 text-blue-600" />
                </div>
                <h3 className="font-semibold mb-2">Sécurisé</h3>
                <p className="text-sm text-gray-600 dark:text-gray-400">
                  Vos données familiales sont protégées par un chiffrement de niveau bancaire
                </p>
              </div>

              <div className="text-center p-6">
                <div className="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                  <Zap className="w-6 h-6 text-green-600" />
                </div>
                <h3 className="font-semibold mb-2">Rapide</h3>
                <p className="text-sm text-gray-600 dark:text-gray-400">
                  Interface ultra-rapide et responsive pour tous vos appareils
                </p>
              </div>

              <div className="text-center p-6">
                <div className="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                  <Globe className="w-6 h-6 text-purple-600" />
                </div>
                <h3 className="font-semibold mb-2">Accessible</h3>
                <p className="text-sm text-gray-600 dark:text-gray-400">
                  Disponible partout dans le monde, 24h/24 et 7j/7
                </p>
              </div>

              <div className="text-center p-6">
                <div className="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center mx-auto mb-4">
                  <Heart className="w-6 h-6 text-orange-600" />
                </div>
                <h3 className="font-semibold mb-2">Familial</h3>
                <p className="text-sm text-gray-600 dark:text-gray-400">
                  Conçu avec amour pour renforcer les liens familiaux
                </p>
              </div>
            </div>
          </div>

          {/* CTA Section */}
          <div className="text-center">
            <Card className="max-w-4xl mx-auto border-0 shadow-2xl bg-gradient-to-r from-blue-600 to-purple-600 text-white">
              <CardContent className="py-16 px-8">
                <h2 className="text-3xl font-bold mb-4">
                  Prêt à connecter votre famille ?
                </h2>
                <p className="text-blue-100 mb-8 text-lg max-w-2xl mx-auto">
                  Rejoignez des milliers de familles qui utilisent Yamsoo pour
                  maintenir des liens forts et créer des souvenirs durables.
                </p>
                <div className="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                  <Link href={route('register')}>
                    <Button size="lg" className="px-8 py-4 text-lg bg-white text-blue-600 hover:bg-gray-100 shadow-xl">
                      Créer mon compte gratuitement
                      <ArrowRight className="w-5 h-5 ml-2" />
                    </Button>
                  </Link>
                  <Link href={route('login')}>
                    <Button variant="outline" size="lg" className="px-8 py-4 text-lg border-white text-white hover:bg-white hover:text-blue-600">
                      Découvrir la démo
                    </Button>
                  </Link>
                </div>
              </CardContent>
            </Card>
          </div>
        </main>

        {/* Footer */}
        <footer className="container mx-auto px-4 py-12 border-t border-gray-200 dark:border-gray-700 mt-20">
          <div className="text-center">
            <div className="flex items-center justify-center space-x-2 mb-4">
              <div className="w-8 h-8 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                <span className="text-white font-bold text-sm">Y</span>
              </div>
              <span className="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                Yamsoo
              </span>
            </div>
            <p className="text-gray-600 dark:text-gray-400 mb-2">
              &copy; 2024 Yamsoo. Tous droits réservés.
            </p>
            <p className="text-sm text-gray-500 dark:text-gray-500">
              Laravel {laravelVersion} | PHP {phpVersion}
            </p>
          </div>
        </footer>
      </div>
    </>
  );
}
