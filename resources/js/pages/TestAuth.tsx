import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export default function TestAuth() {
    return (
        <>
            <Head title="Test Authentication" />
            <div className="min-h-screen bg-background p-8">
                <div className="max-w-4xl mx-auto space-y-8">
                    <h1 className="text-4xl font-bold text-foreground mb-8">Test des routes d'authentification</h1>
                    
                    <Card>
                        <CardHeader>
                            <CardTitle>Routes d'authentification disponibles</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <h3 className="font-semibold">Pages de connexion</h3>
                                    <div className="space-y-2">
                                        <Link href="/login">
                                            <Button variant="outline" className="w-full">
                                                Aller à /login
                                            </Button>
                                        </Link>
                                        <Link href="/register">
                                            <Button variant="outline" className="w-full">
                                                Aller à /register
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                                
                                <div className="space-y-2">
                                    <h3 className="font-semibold">Autres pages</h3>
                                    <div className="space-y-2">
                                        <Link href="/forgot-password">
                                            <Button variant="outline" className="w-full">
                                                Mot de passe oublié
                                            </Button>
                                        </Link>
                                        <Link href="/dashboard">
                                            <Button variant="outline" className="w-full">
                                                Dashboard (nécessite auth)
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Instructions de test</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ol className="list-decimal list-inside space-y-2 text-sm">
                                <li>Cliquez sur "Aller à /login" pour tester la page de connexion</li>
                                <li>Cliquez sur "Aller à /register" pour tester la page d'inscription</li>
                                <li>Vérifiez que les formulaires s'affichent correctement</li>
                                <li>Testez la soumission des formulaires</li>
                                <li>Vérifiez les redirections après connexion/inscription</li>
                            </ol>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Diagnostic</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2 text-sm">
                                <p><strong>URL actuelle:</strong> {window.location.href}</p>
                                <p><strong>Environnement:</strong> {import.meta.env.MODE}</p>
                                <p><strong>Base URL:</strong> {import.meta.env.VITE_APP_URL || 'Non définie'}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
