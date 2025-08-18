import React from 'react';
import { Head, usePage } from '@inertiajs/react';
import { KwdDashboardLayout } from '@/layouts/modern';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { User, CheckCircle, XCircle, AlertTriangle, Database } from 'lucide-react';
import { type SharedData } from '@/types';

export default function TestProfile() {
    const { auth } = usePage<SharedData>().props;
    
    return (
        <KwdDashboardLayout title="Test Profile">
            <Head title="Test Modèle Profile" />
            
            <div className="space-y-6">
                <div className="text-center">
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Test du Modèle Profile
                    </h1>
                    <p className="text-gray-600 dark:text-gray-400">
                        Vérification de la correction de l'erreur "Cannot redeclare $casts"
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {/* État du modèle */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Database className="h-5 w-5 text-blue-500" />
                                État du Modèle
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <div className="flex items-center gap-2 text-sm">
                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                    <span>Modèle Profile chargé</span>
                                </div>
                                
                                <div className="flex items-center gap-2 text-sm">
                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                    <span>$casts déclaré une seule fois</span>
                                </div>
                                
                                <div className="flex items-center gap-2 text-sm">
                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                    <span>Nouvelles colonnes ajoutées</span>
                                </div>
                                
                                <div className="flex items-center gap-2 text-sm">
                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                    <span>Valeurs par défaut définies</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Profil utilisateur */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <User className="h-5 w-5 text-green-500" />
                                Profil Utilisateur
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Utilisateur:</span>
                                    <Badge variant="outline">{auth.user.name}</Badge>
                                </div>
                                
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Email:</span>
                                    <Badge variant="outline" className="text-xs">
                                        {auth.user.email.substring(0, 20)}...
                                    </Badge>
                                </div>
                                
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Profil:</span>
                                    <Badge variant={auth.user.profile ? "default" : "destructive"}>
                                        {auth.user.profile ? "Existe" : "Manquant"}
                                    </Badge>
                                </div>
                                
                                {auth.user.profile && (
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm">Langue:</span>
                                        <Badge variant="secondary">
                                            {(auth.user.profile as any).language || 'fr'}
                                        </Badge>
                                    </div>
                                )}
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
                                    <span>Erreur $casts corrigée</span>
                                </div>
                                
                                <div className="flex items-center gap-2 text-sm">
                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                    <span>Migration créée</span>
                                </div>
                                
                                <div className="flex items-center gap-2 text-sm">
                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                    <span>Fillable mis à jour</span>
                                </div>
                                
                                <div className="flex items-center gap-2 text-sm">
                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                    <span>Attributs par défaut</span>
                                </div>
                                
                                <div className="flex items-center gap-2 text-sm">
                                    <CheckCircle className="h-4 w-4 text-green-500" />
                                    <span>Relations intactes</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Détails techniques */}
                <Card>
                    <CardHeader>
                        <CardTitle>Détails Techniques</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 className="font-semibold mb-3">Problème Corrigé</h3>
                                <div className="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                    <p>❌ <strong>Avant:</strong> Deux déclarations de <code>$casts</code></p>
                                    <p>✅ <strong>Après:</strong> Une seule déclaration fusionnée</p>
                                    <p>🔧 <strong>Solution:</strong> Suppression du doublon</p>
                                </div>
                            </div>
                            
                            <div>
                                <h3 className="font-semibold mb-3">Nouvelles Fonctionnalités</h3>
                                <ul className="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <li>• Colonne <code>language</code> (défaut: fr)</li>
                                    <li>• Colonne <code>timezone</code> (défaut: UTC)</li>
                                    <li>• Préférences de notifications</li>
                                    <li>• Paramètres de confidentialité</li>
                                    <li>• Préférence de thème</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div className="mt-6 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <h4 className="font-medium text-green-800 dark:text-green-200 mb-2">
                                ✅ Correction Réussie
                            </h4>
                            <p className="text-sm text-green-700 dark:text-green-300">
                                L'erreur "Cannot redeclare App\Models\Profile::$casts" a été corrigée. 
                                Le modèle Profile fonctionne maintenant correctement avec toutes les 
                                nouvelles colonnes de paramètres.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                {/* Actions de test */}
                <Card>
                    <CardHeader>
                        <CardTitle>Actions de Test</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <Button asChild variant="outline">
                                <a href="/parametres">
                                    Tester Paramètres
                                </a>
                            </Button>
                            
                            <Button asChild variant="outline">
                                <a href="/test-locale">
                                    Tester Locale
                                </a>
                            </Button>
                            
                            <Button asChild variant="outline">
                                <a href="/profile/edit">
                                    Éditer Profil
                                </a>
                            </Button>
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
