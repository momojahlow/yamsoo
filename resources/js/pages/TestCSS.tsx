import React from 'react';
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export default function TestCSS() {
    return (
        <>
            <Head title="Test CSS" />
            <div className="min-h-screen bg-background p-8">
                <div className="max-w-4xl mx-auto space-y-8">
                    <h1 className="text-4xl font-bold text-foreground mb-8">Test CSS - Tailwind CSS v4</h1>
                    
                    {/* Test des couleurs de base */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Test des couleurs</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div className="bg-primary text-primary-foreground p-4 rounded">Primary</div>
                                <div className="bg-secondary text-secondary-foreground p-4 rounded">Secondary</div>
                                <div className="bg-muted text-muted-foreground p-4 rounded">Muted</div>
                                <div className="bg-accent text-accent-foreground p-4 rounded">Accent</div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Test des boutons */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Test des boutons</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex flex-wrap gap-4">
                                <Button>Bouton par défaut</Button>
                                <Button variant="secondary">Bouton secondaire</Button>
                                <Button variant="outline">Bouton outline</Button>
                                <Button variant="destructive">Bouton destructif</Button>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Test du responsive */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Test responsive</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div className="bg-blue-100 p-4 rounded">Colonne 1</div>
                                <div className="bg-green-100 p-4 rounded">Colonne 2</div>
                                <div className="bg-yellow-100 p-4 rounded">Colonne 3</div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Test des animations */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Test des animations</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="animate-pulse bg-gray-200 h-4 rounded"></div>
                            <div className="animate-bounce bg-blue-500 w-8 h-8 rounded-full"></div>
                            <div className="animate-spin bg-red-500 w-8 h-8 rounded-full"></div>
                        </CardContent>
                    </Card>

                    {/* Test du mode sombre */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Test mode sombre</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-foreground">
                                Ce texte devrait s'adapter automatiquement au mode sombre/clair.
                            </p>
                            <div className="mt-4 p-4 border border-border rounded">
                                <p className="text-muted-foreground">Texte en couleur muted</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
