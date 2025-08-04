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

interface User {
  id: number;
  name: string;
  email: string;
}

interface Props {
  canLogin: boolean;
  canRegister: boolean;
  laravelVersion: string;
  phpVersion: string;
  auth: {
    user: User | null;
  };
}

export default function Welcome({ canLogin, canRegister, laravelVersion, phpVersion, auth }: Props) {
  const { user } = auth;
  return (
    <>
      <Head title="Yamsoo - Connexions Familiales" />

      <div className="min-h-screen bg-gradient-to-br from-orange-50 via-white to-red-50">
        {/* Header responsive */}
        <header className="container mx-auto px-3 sm:px-4 py-4 sm:py-6">
          <nav className="flex items-center justify-between">
            <div className="flex items-center space-x-2 sm:space-x-3">
              {/* Logo responsive - thumb sur mobile, complet sur desktop */}
              <div className="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg transform hover:scale-105 transition-transform duration-200">
                <span className="text-white font-bold text-sm sm:text-lg md:text-2xl">Y</span>
              </div>
              {/* Texte Yamsoo caché sur mobile, visible à partir de sm */}
              <span className="hidden sm:block text-xl sm:text-2xl md:text-3xl font-bold bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent">
                Yamsoo!
              </span>
            </div>

            <div className="flex items-center space-x-2 sm:space-x-4">
              {user ? (
                // Utilisateur connecté
                <>
                  <span className="hidden sm:block text-gray-600 font-medium text-sm sm:text-base">
                    Bonjour, {user.name}
                  </span>
                  <Link href={route('dashboard')}>
                    <Button className="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 shadow-lg transform hover:scale-105 transition-all duration-200 text-sm sm:text-base px-3 sm:px-4 py-2">
                      <span className="hidden sm:inline">Mon compte</span>
                      <span className="sm:hidden">Compte</span>
                    </Button>
                  </Link>
                </>
              ) : (
                // Utilisateur non connecté
                <>
                  {canLogin && (
                    <Link href={route('login')}>
                      <Button variant="ghost" className="font-medium text-gray-700 hover:text-orange-600 hover:bg-orange-50 transition-colors duration-200 text-sm sm:text-base px-2 sm:px-4 py-2">
                        <span className="hidden sm:inline">Se connecter</span>
                        <span className="sm:hidden">Connexion</span>
                      </Button>
                    </Link>
                  )}
                  {canRegister && (
                    <Link href={route('register')}>
                      <Button className="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 shadow-lg transform hover:scale-105 transition-all duration-200 text-sm sm:text-base px-3 sm:px-4 py-2">
                        <span className="hidden sm:inline">S'inscrire</span>
                        <span className="sm:hidden">Inscription</span>
                      </Button>
                    </Link>
                  )}
                </>
              )}
            </div>
          </nav>
        </header>

        {/* Hero Section */}
        <main className="container mx-auto px-4 py-16">
          <div className="text-center mb-20">
            <div className="mb-8">
              <div className="inline-flex items-center px-6 py-3 bg-gradient-to-r from-orange-100 to-red-100 rounded-full text-orange-700 text-sm font-medium mb-8 shadow-md">
                <Heart className="w-5 h-5 mr-2 text-red-500" />
                La plateforme familiale qui rapproche les cœurs
              </div>
            </div>

            <h1 className="text-5xl md:text-7xl font-bold text-gray-900 mb-8 leading-tight">
              Votre famille,
              <br />
              <span className="bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent">
                plus proche que jamais
              </span>
            </h1>

            <p className="text-xl text-gray-600 mb-12 max-w-4xl mx-auto leading-relaxed">
              Yamsoo révolutionne les liens familiaux. Créez votre arbre généalogique interactif,
              partagez vos souvenirs précieux et restez connecté avec tous vos proches,
              où qu'ils soient dans le monde.
            </p>

            <div className="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-6 mb-16">
              {user ? (
                // Utilisateur connecté - Boutons vers les fonctionnalités
                <>
                  <Link href={route('dashboard')}>
                    <Button size="lg" className="px-10 py-4 text-lg font-semibold bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 shadow-xl transform hover:scale-105 transition-all duration-200 rounded-xl">
                      Accéder à mon tableau de bord
                      <ArrowRight className="w-5 h-5 ml-2" />
                    </Button>
                  </Link>
                  <Link href={route('family')}>
                    <Button variant="outline" size="lg" className="px-10 py-4 text-lg font-semibold border-2 border-orange-300 text-orange-600 hover:bg-orange-50 hover:border-orange-400 transition-all duration-200 rounded-xl">
                      Ma famille
                    </Button>
                  </Link>
                </>
              ) : (
                // Utilisateur non connecté - Boutons d'inscription/connexion
                <>
                  <Link href={route('register')}>
                    <Button size="lg" className="px-10 py-4 text-lg font-semibold bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 shadow-xl transform hover:scale-105 transition-all duration-200 rounded-xl">
                      Créer ma famille
                      <ArrowRight className="w-5 h-5 ml-2" />
                    </Button>
                  </Link>
                  <Link href={route('login')}>
                    <Button variant="outline" size="lg" className="px-10 py-4 text-lg font-semibold border-2 border-orange-300 text-orange-600 hover:bg-orange-50 hover:border-orange-400 transition-all duration-200 rounded-xl">
                      Se connecter
                    </Button>
                  </Link>
                </>
              )}
            </div>

            {/* Stats */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-3xl mx-auto">
              <div className="text-center p-6 bg-white/70 backdrop-blur-sm rounded-2xl shadow-lg border border-orange-100">
                <div className="text-4xl font-bold bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent mb-2">15K+</div>
                <div className="text-gray-600 font-medium">Familles connectées</div>
              </div>
              <div className="text-center p-6 bg-white/70 backdrop-blur-sm rounded-2xl shadow-lg border border-red-100">
                <div className="text-4xl font-bold bg-gradient-to-r from-red-500 to-pink-500 bg-clip-text text-transparent mb-2">75K+</div>
                <div className="text-gray-600 font-medium">Relations créées</div>
              </div>
              <div className="text-center p-6 bg-white/70 backdrop-blur-sm rounded-2xl shadow-lg border border-orange-100">
                <div className="text-4xl font-bold bg-gradient-to-r from-orange-500 to-yellow-500 bg-clip-text text-transparent mb-2">99%</div>
                <div className="text-gray-600 font-medium">Satisfaction client</div>
              </div>
            </div>
          </div>

          {/* Features */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20">
            <Card className="text-center border-0 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 bg-white/80 backdrop-blur-sm">
              <CardHeader className="pb-4">
                <div className="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg transform hover:scale-110 transition-transform duration-200">
                  <TreePine className="w-8 h-8 text-white" />
                </div>
                <CardTitle className="text-xl text-gray-800">Arbre Généalogique</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-gray-600 leading-relaxed">
                  Créez et visualisez votre arbre généalogique interactif.
                  Découvrez vos racines et partagez votre histoire familiale
                  avec des outils de visualisation avancés.
                </p>
              </CardContent>
            </Card>

            <Card className="text-center border-0 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 bg-white/80 backdrop-blur-sm">
              <CardHeader className="pb-4">
                <div className="w-16 h-16 bg-gradient-to-br from-red-500 to-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg transform hover:scale-110 transition-transform duration-200">
                  <MessageSquare className="w-8 h-8 text-white" />
                </div>
                <CardTitle className="text-xl text-gray-800">Messagerie Familiale</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-gray-600 leading-relaxed">
                  Communiquez en privé avec vos proches. Partagez des photos,
                  des messages et restez connecté en temps réel avec une
                  messagerie sécurisée et intuitive.
                </p>
              </CardContent>
            </Card>

            <Card className="text-center border-0 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 bg-white/80 backdrop-blur-sm">
              <CardHeader className="pb-4">
                <div className="w-16 h-16 bg-gradient-to-br from-orange-400 to-red-400 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg transform hover:scale-110 transition-transform duration-200">
                  <Users className="w-8 h-8 text-white" />
                </div>
                <CardTitle className="text-xl text-gray-800">Suggestions Intelligentes</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-gray-600 leading-relaxed">
                  Recevez des suggestions de connexions familiales basées sur
                  vos relations existantes et votre réseau. Notre IA vous aide
                  à découvrir de nouveaux liens familiaux.
                </p>
              </CardContent>
            </Card>
          </div>

          {/* Benefits Section */}
          <div className="mb-20">
            <div className="text-center mb-16">
              <h2 className="text-4xl font-bold text-gray-900 mb-6">
                Pourquoi choisir
                <span className="bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent"> Yamsoo</span> ?
              </h2>
              <p className="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                Une plateforme révolutionnaire conçue spécifiquement pour renforcer
                les liens familiaux à l'ère numérique
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
              <div className="text-center p-8 bg-white/70 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-orange-100">
                <div className="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                  <Shield className="w-8 h-8 text-white" />
                </div>
                <h3 className="font-bold text-lg mb-3 text-gray-800">Ultra Sécurisé</h3>
                <p className="text-gray-600 leading-relaxed">
                  Vos données familiales sont protégées par un chiffrement de niveau bancaire et des protocoles de sécurité avancés
                </p>
              </div>

              <div className="text-center p-8 bg-white/70 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-red-100">
                <div className="w-16 h-16 bg-gradient-to-br from-red-500 to-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                  <Zap className="w-8 h-8 text-white" />
                </div>
                <h3 className="font-bold text-lg mb-3 text-gray-800">Ultra Rapide</h3>
                <p className="text-gray-600 leading-relaxed">
                  Interface ultra-rapide et responsive optimisée pour tous vos appareils, mobile et desktop
                </p>
              </div>

              <div className="text-center p-8 bg-white/70 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-orange-100">
                <div className="w-16 h-16 bg-gradient-to-br from-orange-400 to-red-400 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                  <Globe className="w-8 h-8 text-white" />
                </div>
                <h3 className="font-bold text-lg mb-3 text-gray-800">Toujours Accessible</h3>
                <p className="text-gray-600 leading-relaxed">
                  Disponible partout dans le monde, 24h/24 et 7j/7 avec une disponibilité de 99.9%
                </p>
              </div>

              <div className="text-center p-8 bg-white/70 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-red-100">
                <div className="w-16 h-16 bg-gradient-to-br from-red-400 to-pink-400 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                  <Heart className="w-8 h-8 text-white" />
                </div>
                <h3 className="font-bold text-lg mb-3 text-gray-800">Fait avec ❤️</h3>
                <p className="text-gray-600 leading-relaxed">
                  Conçu avec passion pour renforcer les liens familiaux et créer des souvenirs durables
                </p>
              </div>
            </div>
          </div>

          {/* CTA Section */}
          <div className="text-center">
            <Card className="max-w-5xl mx-auto border-0 shadow-2xl bg-gradient-to-r from-orange-500 to-red-500 text-white overflow-hidden">
              <CardContent className="py-20 px-8 relative">
                {/* Decorative elements */}
                <div className="absolute top-0 left-0 w-full h-full opacity-10">
                  <div className="absolute top-10 left-10 w-20 h-20 bg-white rounded-full"></div>
                  <div className="absolute bottom-10 right-10 w-16 h-16 bg-white rounded-full"></div>
                  <div className="absolute top-1/2 right-20 w-12 h-12 bg-white rounded-full"></div>
                </div>

                <div className="relative z-10">
                  <h2 className="text-4xl md:text-5xl font-bold mb-6">
                    Prêt à réunir votre famille ?
                  </h2>
                  <p className="text-orange-100 mb-10 text-xl max-w-3xl mx-auto leading-relaxed">
                    Rejoignez plus de 15 000 familles qui utilisent Yamsoo pour
                    maintenir des liens forts et créer des souvenirs inoubliables.
                  </p>
                  <div className="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-6">
                    {user ? (
                      // Utilisateur connecté
                      <>
                        <Link href={route('messages.index')}>
                          <Button size="lg" className="px-10 py-4 text-lg font-semibold bg-white text-orange-600 hover:bg-gray-100 shadow-xl transform hover:scale-105 transition-all duration-200 rounded-xl">
                            Mes messages
                            <ArrowRight className="w-5 h-5 ml-2" />
                          </Button>
                        </Link>
                        <Link href={route('family.tree')}>
                          <Button variant="outline" size="lg" className="px-10 py-4 text-lg font-semibold border-2 border-white text-white hover:bg-white hover:text-orange-600 transition-all duration-200 rounded-xl">
                            Mon arbre familial
                          </Button>
                        </Link>
                      </>
                    ) : (
                      // Utilisateur non connecté
                      <>
                        <Link href={route('register')}>
                          <Button size="lg" className="px-10 py-4 text-lg font-semibold bg-white text-orange-600 hover:bg-gray-100 shadow-xl transform hover:scale-105 transition-all duration-200 rounded-xl">
                            Créer ma famille maintenant
                            <ArrowRight className="w-5 h-5 ml-2" />
                          </Button>
                        </Link>
                        <Link href={route('login')}>
                          <Button variant="outline" size="lg" className="px-10 py-4 text-lg font-semibold border-2 border-white text-white hover:bg-white hover:text-orange-600 transition-all duration-200 rounded-xl">
                            Découvrir Yamsoo
                          </Button>
                        </Link>
                      </>
                    )}
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </main>

        {/* Footer */}
        <footer className="container mx-auto px-4 py-16 border-t border-orange-200 mt-20 bg-gradient-to-r from-orange-50 to-red-50">
          <div className="text-center">
            <div className="flex items-center justify-center space-x-2 sm:space-x-3 mb-6">
              {/* Logo responsive - thumb sur mobile, complet sur desktop */}
              <div className="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-orange-500 to-red-500 rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg">
                <span className="text-white font-bold text-sm sm:text-lg">Y</span>
              </div>
              {/* Texte Yamsoo caché sur très petit mobile, visible à partir de sm */}
              <span className="hidden sm:block text-xl sm:text-2xl font-bold bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent">
                Yamsoo!
              </span>
            </div>
            <p className="text-gray-600 mb-4 text-lg">
              Connecter les familles, créer des souvenirs
            </p>
            <p className="text-gray-500 mb-2">
              &copy; 2024 Yamsoo. Tous droits réservés. •
              <Link href="/conditions-generales" className="text-orange-600 hover:text-orange-700 underline ml-1">
                Conditions Générales
              </Link>
            </p>
            <p className="text-sm text-gray-400">
              Propulsé par Laravel {laravelVersion} & PHP {phpVersion}
            </p>
          </div>
        </footer>
      </div>
    </>
  );
}
