import React from 'react';
import { Head } from '@inertiajs/react';
import { YamsooHeader } from '@/components/layout/YamsooHeader';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';

interface User {
  id: number;
  name: string;
  email: string;
}

interface TermsOfServiceProps {
  auth?: {
    user: User | null;
  };
}

export default function TermsOfService({ auth }: TermsOfServiceProps) {
  return (
    <>
      <Head title="Conditions Générales d'Utilisation" />

      <div className="min-h-screen bg-gradient-to-br from-orange-50 via-white to-red-50">
        <YamsooHeader user={auth?.user} showNavigation={true} />

        <div className="min-h-screen bg-gradient-to-br from-gray-50 to-white pt-8">
        <div className="w-full max-w-5xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-6 md:py-8 lg:py-12">
          {/* Header */}
          <div className="text-center mb-8 sm:mb-12">
            <h1 className="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent leading-tight mb-4">
              Conditions Générales d'Utilisation
            </h1>
            <div className="flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-4 mb-6">
              <Badge variant="secondary" className="bg-blue-100 text-blue-800 border-blue-200 px-3 py-1">
                Yamsoo.com
              </Badge>
              <Badge variant="outline" className="border-gray-300 text-gray-700 px-3 py-1">
                Entrée en vigueur : 1er août 2025
              </Badge>
            </div>
            <p className="text-gray-600 text-sm sm:text-base max-w-3xl mx-auto leading-relaxed">
              Les présentes Conditions Générales d'Utilisation régissent votre accès et votre utilisation 
              de la plateforme Yamsoo.com et de ses services.
            </p>
          </div>

          {/* Content */}
          <Card className="border-0 shadow-lg bg-gradient-to-br from-white to-gray-50/50">
            <CardHeader className="p-4 sm:p-6 md:p-8">
              <CardTitle className="text-lg sm:text-xl md:text-2xl font-semibold text-gray-900 flex items-center gap-3">
                <div className="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                  <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                Document Légal
              </CardTitle>
            </CardHeader>
            
            <CardContent className="p-4 sm:p-6 md:p-8 pt-0">
              <ScrollArea className="h-[70vh] pr-4">
                <div className="prose prose-sm sm:prose-base max-w-none space-y-6 sm:space-y-8">
                  
                  {/* Présentation */}
                  <section>
                    <h2 className="text-lg sm:text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                      <span className="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-bold">1</span>
                      Présentation
                    </h2>
                    <div className="bg-blue-50 p-4 rounded-lg mb-4">
                      <p className="text-sm sm:text-base text-gray-700 leading-relaxed">
                        Bienvenue sur Yamsoo.com. Les présentes Conditions Générales d'Utilisation (ci-après les "Conditions") 
                        régissent votre accès et votre utilisation du site web Yamsoo.com, de ses services, fonctionnalités, 
                        applications, technologies et logiciels que nous proposons (ci-après le "Service" ou les "Services").
                      </p>
                    </div>
                    <p className="text-sm sm:text-base text-gray-700 leading-relaxed mb-4">
                      Ces Services vous sont fournis par Yamsoo.com. Par conséquent, les présentes Conditions constituent 
                      un accord juridiquement contraignant entre vous (l'utilisateur) et Yamsoo.com. Si vous n'acceptez pas 
                      les présentes Conditions, veuillez ne pas accéder au site ni utiliser nos Services.
                    </p>
                    <p className="text-sm sm:text-base text-gray-700 leading-relaxed mb-4">
                      Ces Conditions Générales forment l'intégralité de l'accord entre vous et Yamsoo.com concernant votre 
                      utilisation de nos Services. Elles remplacent tout accord antérieur et constituent la version la plus 
                      récente de nos conditions d'utilisation.
                    </p>
                  </section>

                  {/* Les Services que nous fournissons */}
                  <section>
                    <h2 className="text-lg sm:text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                      <span className="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm font-bold">2</span>
                      Les Services que nous fournissons
                    </h2>
                    <p className="text-sm sm:text-base text-gray-700 leading-relaxed mb-4">
                      Notre mission est de vous offrir une plateforme numérique de qualité qui répond à vos besoins et attentes. 
                      Pour accomplir cette mission, nous vous fournissons les Services décrits ci-dessous.
                    </p>
                    
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                      <div className="bg-green-50 p-4 rounded-lg">
                        <h3 className="font-semibold text-green-900 mb-2 text-sm sm:text-base">🎯 Expérience personnalisée</h3>
                        <p className="text-xs sm:text-sm text-green-700 leading-relaxed">
                          Nous vous proposons un contenu et des fonctionnalités adaptés à vos préférences et à votre utilisation.
                        </p>
                      </div>
                      <div className="bg-purple-50 p-4 rounded-lg">
                        <h3 className="font-semibold text-purple-900 mb-2 text-sm sm:text-base">🤝 Connexions facilitées</h3>
                        <p className="text-xs sm:text-sm text-purple-700 leading-relaxed">
                          Nous vous aidons à découvrir et interagir avec d'autres utilisateurs partageant vos intérêts.
                        </p>
                      </div>
                      <div className="bg-orange-50 p-4 rounded-lg">
                        <h3 className="font-semibold text-orange-900 mb-2 text-sm sm:text-base">✨ Expression créative</h3>
                        <p className="text-xs sm:text-sm text-orange-700 leading-relaxed">
                          Nous vous offrons diverses possibilités d'expression et de partage selon nos fonctionnalités.
                        </p>
                      </div>
                      <div className="bg-red-50 p-4 rounded-lg">
                        <h3 className="font-semibold text-red-900 mb-2 text-sm sm:text-base">🔒 Sécurité garantie</h3>
                        <p className="text-xs sm:text-sm text-red-700 leading-relaxed">
                          Nous assurons la sécurité et l'intégrité de nos Services avec des mesures avancées.
                        </p>
                      </div>
                    </div>
                  </section>

                  {/* Financement de nos Services */}
                  <section>
                    <h2 className="text-lg sm:text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                      <span className="w-6 h-6 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center text-sm font-bold">3</span>
                      Financement de nos Services
                    </h2>
                    <div className="bg-yellow-50 p-4 rounded-lg mb-4">
                      <p className="text-sm sm:text-base text-gray-700 leading-relaxed">
                        Yamsoo.com peut proposer des Services gratuits et des Services payants. Les Services gratuits peuvent 
                        être financés par la publicité, les partenariats commerciaux, ou d'autres modèles économiques que nous 
                        jugeons appropriés.
                      </p>
                    </div>
                    <p className="text-sm sm:text-base text-gray-700 leading-relaxed">
                      Nous ne vendons jamais vos données personnelles à des tiers, mais nous pouvons partager des informations 
                      anonymisées et agrégées avec nos partenaires pour améliorer la pertinence des services.
                    </p>
                  </section>

                  {/* Vos engagements */}
                  <section>
                    <h2 className="text-lg sm:text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                      <span className="w-6 h-6 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center text-sm font-bold">4</span>
                      Vos engagements
                    </h2>
                    <p className="text-sm sm:text-base text-gray-700 leading-relaxed mb-4">
                      En utilisant nos Services, vous vous engagez à respecter certaines règles qui garantissent une 
                      expérience positive pour tous les utilisateurs.
                    </p>
                    
                    <div className="space-y-4">
                      <div className="border-l-4 border-blue-500 pl-4 bg-blue-50 p-3 rounded-r-lg">
                        <h3 className="font-semibold text-blue-900 mb-2 text-sm sm:text-base">Éligibilité</h3>
                        <p className="text-xs sm:text-sm text-blue-700">
                          Vous devez être âgé d'au moins 13 ans pour utiliser nos Services.
                        </p>
                      </div>
                      <div className="border-l-4 border-green-500 pl-4 bg-green-50 p-3 rounded-r-lg">
                        <h3 className="font-semibold text-green-900 mb-2 text-sm sm:text-base">Utilisation appropriée</h3>
                        <p className="text-xs sm:text-sm text-green-700">
                          Vous vous engagez à utiliser nos Services de manière responsable et conforme à leur destination.
                        </p>
                      </div>
                      <div className="border-l-4 border-red-500 pl-4 bg-red-50 p-3 rounded-r-lg">
                        <h3 className="font-semibold text-red-900 mb-2 text-sm sm:text-base">Contenu autorisé</h3>
                        <p className="text-xs sm:text-sm text-red-700">
                          Votre contenu doit respecter nos Standards de la Communauté et ne pas violer la loi.
                        </p>
                      </div>
                    </div>
                  </section>

                  {/* Dispositions supplémentaires */}
                  <section>
                    <h2 className="text-lg sm:text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                      <span className="w-6 h-6 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-sm font-bold">5</span>
                      Dispositions supplémentaires
                    </h2>

                    <div className="space-y-4">
                      <div className="bg-indigo-50 p-4 rounded-lg">
                        <h3 className="font-semibold text-indigo-900 mb-2 text-sm sm:text-base">📝 Modification des Conditions</h3>
                        <p className="text-xs sm:text-sm text-indigo-700 leading-relaxed">
                          Nous nous réservons le droit de modifier ces Conditions à tout moment. Nous vous informerons
                          des modifications substantielles au moins 30 jours avant leur entrée en vigueur.
                        </p>
                      </div>

                      <div className="bg-orange-50 p-4 rounded-lg">
                        <h3 className="font-semibold text-orange-900 mb-2 text-sm sm:text-base">⚠️ Limitation de responsabilité</h3>
                        <p className="text-xs sm:text-sm text-orange-700 leading-relaxed">
                          Nos Services sont fournis "en l'état". Notre responsabilité est limitée au montant que vous
                          avez payé au cours des 12 derniers mois, ou 100 euros si aucun paiement n'a été effectué.
                        </p>
                      </div>

                      <div className="bg-red-50 p-4 rounded-lg">
                        <h3 className="font-semibold text-red-900 mb-2 text-sm sm:text-base">🚫 Suspension d'accès</h3>
                        <p className="text-xs sm:text-sm text-red-700 leading-relaxed">
                          En cas de violation de ces Conditions, nous pouvons prendre des mesures graduelles incluant
                          des avertissements, restrictions temporaires, ou suppression définitive d'accès.
                        </p>
                      </div>
                    </div>
                  </section>

                  {/* Résolution des litiges */}
                  <section>
                    <h2 className="text-lg sm:text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                      <span className="w-6 h-6 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold">6</span>
                      Résolution des litiges
                    </h2>

                    <div className="bg-gray-50 p-4 rounded-lg mb-4">
                      <h3 className="font-semibold text-gray-900 mb-2 text-sm sm:text-base">⚖️ Droit applicable</h3>
                      <p className="text-xs sm:text-sm text-gray-700 leading-relaxed">
                        Les présentes Conditions sont régies par le droit français. Tout litige sera soumis à la
                        juridiction exclusive des tribunaux français compétents.
                      </p>
                    </div>

                    <div className="bg-blue-50 p-4 rounded-lg">
                      <h3 className="font-semibold text-blue-900 mb-2 text-sm sm:text-base">🤝 Résolution amiable</h3>
                      <p className="text-xs sm:text-sm text-blue-700 leading-relaxed">
                        Avant toute procédure judiciaire, nous vous encourageons à nous contacter directement pour
                        tenter de résoudre tout différend à l'amiable.
                      </p>
                    </div>
                  </section>

                  {/* Contact */}
                  <section>
                    <h2 className="text-lg sm:text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                      <span className="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-bold">7</span>
                      Contact et informations légales
                    </h2>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div className="bg-blue-50 p-4 rounded-lg">
                        <h3 className="font-semibold text-blue-900 mb-2 text-sm sm:text-base">📧 Nous contacter</h3>
                        <p className="text-xs sm:text-sm text-blue-700 leading-relaxed">
                          Email : contact@yamsoo.com<br />
                          Pour toute question concernant ces Conditions ou nos Services.
                        </p>
                      </div>

                      <div className="bg-green-50 p-4 rounded-lg">
                        <h3 className="font-semibold text-green-900 mb-2 text-sm sm:text-base">🏢 Informations légales</h3>
                        <p className="text-xs sm:text-sm text-green-700 leading-relaxed">
                          Éditeur du site : Yamsoo.com<br />
                          Dernière mise à jour : 1er août 2025
                        </p>
                      </div>
                    </div>
                  </section>

                  {/* Acceptation */}
                  <section className="border-t pt-6 mt-8">
                    <div className="bg-gradient-to-r from-blue-50 to-purple-50 p-6 rounded-xl border border-blue-200">
                      <h3 className="font-bold text-gray-900 mb-3 text-base sm:text-lg text-center">
                        ✅ Acceptation des Conditions
                      </h3>
                      <p className="text-sm sm:text-base text-gray-700 leading-relaxed text-center">
                        En utilisant les Services de Yamsoo.com, vous reconnaissez avoir lu, compris et accepté
                        les présentes Conditions Générales d'Utilisation dans leur intégralité.
                      </p>
                    </div>
                  </section>

                </div>
              </ScrollArea>
            </CardContent>
          </Card>

          {/* Footer */}
          <div className="mt-8 text-center">
            <p className="text-xs sm:text-sm text-gray-500">
              Dernière mise à jour : 1er août 2025 • 
              <a href="mailto:contact@yamsoo.com" className="text-blue-600 hover:text-blue-700 ml-1">
                contact@yamsoo.com
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>
    </>
  );
}
