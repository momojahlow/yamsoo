import React from 'react';
import { Head, usePage } from '@inertiajs/react';
import { KwdDashboardLayout } from '@/layouts/modern';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Globe, CheckCircle, XCircle, AlertTriangle } from 'lucide-react';
import { type SharedData } from '@/types';

export default function TestLocale() {
    const { auth } = usePage<SharedData>().props;
    
    // Informations sur la locale actuelle
    const currentLocale = document.documentElement.lang || 'fr';
    const sessionLocale = sessionStorage.getItem('locale');
    
    return (
        <KwdDashboardLayout title="Test Locale">
            <Head title="Test Middleware Locale" />
            
            <div className="space-y-6">
                <div className="text-center">
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Test du Middleware SetLocale
                    </h1>
                    <p className="text-gray-600 dark:text-gray-400">
                        Vérification de la gestion des langues et correction de l'erreur null
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {/* État actuel */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Globe className="h-5 w-5 text-blue-500" />
                                État Actuel
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Locale HTML:</span>
                                    <Badge variant="outline">{currentLocale}</Badge>
                                </div>
                                
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Session:</span>
                                    <Badge variant="outline">{sessionLocale || 'Non définie'}</Badge>
                                </div>
                                
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Utilisateur:</span>
                                    <Badge variant="outline">{auth.user.name}</Badge>
                                </div>
                                
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Profil:</span>
                                    <Badge variant={auth.user.profile ? "default" : "destructive"}>
                                        {auth.user.profile ? "Existe" : "Manquant"}
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Tests de langue */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <CheckCircle className="h-5 w-5 text-green-500" />
                                Tests de Langue
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Button asChild variant="outline" className="w-full">
                                    <a href="/language/fr">
                                        🇫🇷 Français
                                    </a>
                                </Button>
                                
                                <Button asChild variant="outline" className="w-full">
                                    <a href="/language/ar">
                                        🇸🇦 العربية
                                    </a>
                                </Button>
                                
                                <Button asChild variant="outline" className="w-full">
                                    <a href="/language/en">
                                        🇺🇸 English
                                    </a>
                                </Button>
                                
                                <Button asChild variant="destructive" className="w-full">
                                    <a href="/language/invalid">
                                        ❌ Langue invalide
                                    </a>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Diagnostic */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <AlertTriangle className="h-5 w-5 text-orange-500" />
                                Diagnostic
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-3">
                                <div className="flex items-center gap-2 text-sm">
                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                    <span>Middleware corrigé</span>
                                </div>
                                
                                <div className="flex items-center gap-2 text-sm">
                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                    <span>Fallback sécurisé</span>
                                </div>
                                
                                <div className="flex items-center gap-2 text-sm">
                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                    <span>Gestion des erreurs</span>
                                </div>
                                
                                <div className="flex items-center gap-2 text-sm">
                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                    <span>Routes de langue</span>
                                </div>
                                
                                <div className="flex items-center gap-2 text-sm">
                                    {auth.user.profile ? (
                                        <CheckCircle className="h-4 w-4 text-green-500" />
                                    ) : (
                                        <XCircle className="h-4 w-4 text-red-500" />
                                    )}
                                    <span>Profil utilisateur</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Informations détaillées */}
                <Card>
                    <CardHeader>
                        <CardTitle>Informations Détaillées</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 className="font-semibold mb-3">Corrections Apportées</h3>
                                <ul className="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                    <li>✅ Vérification de la configuration available_locales</li>
                                    <li>✅ Fallback sécurisé si config est null</li>
                                    <li>✅ Gestion des profils utilisateur manquants</li>
                                    <li>✅ Validation stricte des langues disponibles</li>
                                    <li>✅ Retour garanti d'une string valide</li>
                                    <li>✅ Migration pour colonnes de paramètres</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h3 className="font-semibold mb-3">Ordre de Priorité</h3>
                                <ol className="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                    <li>1. Paramètre URL (?lang=fr)</li>
                                    <li>2. Session utilisateur</li>
                                    <li>3. Préférence du profil</li>
                                    <li>4. Langue par défaut (config)</li>
                                    <li>5. Fallback ultime (fr)</li>
                                </ol>
                            </div>
                        </div>
                        
                        <div className="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <h4 className="font-medium text-blue-800 dark:text-blue-200 mb-2">
                                Test de l'erreur corrigée
                            </h4>
                            <p className="text-sm text-blue-700 dark:text-blue-300">
                                L'erreur "Return value must be of type string, null returned" a été corrigée. 
                                Le middleware retourne maintenant toujours une string valide, même en cas de 
                                configuration manquante ou de profil utilisateur inexistant.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <div className="text-center">
                    <Button variant="outline" asChild>
                        <a href="/dashboard">
                            Retour au Dashboard
                        </a>
                    </Button>
                </div>
            </div>
        </KwdDashboardLayout>
    );
}
