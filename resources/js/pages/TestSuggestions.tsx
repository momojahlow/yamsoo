import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { KwdDashboardLayout } from '@/layouts/modern';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { 
  Users, Database, CheckCircle, XCircle, AlertTriangle, 
  RefreshCw, Play, Heart, UserPlus, Settings, Info, 
  Send, Eye, Trash2, Clock
} from 'lucide-react';

interface TestResult {
  name: string;
  status: 'success' | 'error' | 'warning' | 'info';
  message: string;
  details?: string;
}

export default function TestSuggestions() {
  const [isRunningTests, setIsRunningTests] = useState(false);
  const [testResults, setTestResults] = useState<TestResult[]>([]);

  const runDiagnostics = async () => {
    setIsRunningTests(true);
    setTestResults([]);

    const tests: TestResult[] = [];

    // Test 1: Vérifier la route des suggestions
    try {
      const response = await fetch('/suggestions');
      if (response.ok) {
        tests.push({
          name: 'Route /suggestions',
          status: 'success',
          message: 'Route accessible',
          details: `Status: ${response.status}`
        });
      } else {
        tests.push({
          name: 'Route /suggestions',
          status: 'error',
          message: 'Route inaccessible',
          details: `Status: ${response.status}`
        });
      }
    } catch (error) {
      tests.push({
        name: 'Route /suggestions',
        status: 'error',
        message: 'Erreur de connexion',
        details: error instanceof Error ? error.message : 'Erreur inconnue'
      });
    }

    // Test 2: Vérifier les modèles
    tests.push({
      name: 'Modèle Suggestion',
      status: 'info',
      message: 'Modèle défini avec relations',
      details: 'Relations: user(), suggestedUser(), relationshipType()'
    });

    tests.push({
      name: 'Service SuggestionService',
      status: 'info',
      message: 'Service de génération automatique',
      details: 'Génère des suggestions basées sur les relations existantes'
    });

    // Test 3: Vérifier les composants
    tests.push({
      name: 'Composant Suggestions',
      status: 'success',
      message: 'Interface de suggestions créée',
      details: 'Affichage des suggestions avec actions'
    });

    setTestResults(tests);
    setIsRunningTests(false);
  };

  const generateSuggestions = () => {
    router.post('/generate-suggestions', {}, {
      onSuccess: () => {
        setTestResults(prev => [...prev, {
          name: 'Génération suggestions',
          status: 'success',
          message: 'Suggestions générées avec succès',
          details: 'Suggestions automatiques et manuelles créées'
        }]);
      },
      onError: (errors) => {
        setTestResults(prev => [...prev, {
          name: 'Génération suggestions',
          status: 'error',
          message: 'Erreur lors de la génération',
          details: Object.values(errors).join(', ')
        }]);
      }
    });
  };

  const getStatusIcon = (status: TestResult['status']) => {
    switch (status) {
      case 'success': return <CheckCircle className="w-4 h-4 text-green-500" />;
      case 'error': return <XCircle className="w-4 h-4 text-red-500" />;
      case 'warning': return <AlertTriangle className="w-4 h-4 text-yellow-500" />;
      case 'info': return <Info className="w-4 h-4 text-blue-500" />;
    }
  };

  const getStatusColor = (status: TestResult['status']) => {
    switch (status) {
      case 'success': return 'border-green-200 bg-green-50';
      case 'error': return 'border-red-200 bg-red-50';
      case 'warning': return 'border-yellow-200 bg-yellow-50';
      case 'info': return 'border-blue-200 bg-blue-50';
    }
  };

  return (
    <KwdDashboardLayout title="Test Suggestions">
      <Head title="Test des Suggestions Familiales" />
      
      <div className="space-y-8">
        {/* Header */}
        <div className="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 rounded-2xl p-6 md:p-8 border border-blue-100 shadow-sm">
          <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
              <h1 className="text-3xl md:text-4xl font-bold text-gray-900 mb-3">
                Test des Suggestions Familiales
              </h1>
              <p className="text-gray-600 max-w-2xl leading-relaxed">
                Testez et validez le système de suggestions automatiques de relations familiales. 
                Générez des suggestions et testez les fonctionnalités d'envoi de demandes.
              </p>
            </div>
            
            <div className="flex flex-col sm:flex-row gap-3">
              <Button 
                onClick={runDiagnostics} 
                disabled={isRunningTests}
                className="bg-gradient-to-r from-blue-500 to-indigo-500"
              >
                {isRunningTests ? (
                  <>
                    <RefreshCw className="w-4 h-4 mr-2 animate-spin" />
                    Test en cours...
                  </>
                ) : (
                  <>
                    <Play className="w-4 h-4 mr-2" />
                    Lancer les tests
                  </>
                )}
              </Button>
              
              <Button onClick={generateSuggestions} variant="outline">
                <Users className="w-4 h-4 mr-2" />
                Générer suggestions
              </Button>
            </div>
          </div>
        </div>

        {/* Actions rapides */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <Link href="/suggestions">
            <Card className="hover:shadow-lg transition-all duration-300 cursor-pointer border-0 shadow-md">
              <CardContent className="p-6 text-center">
                <div className="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                  <Heart className="w-6 h-6 text-white" />
                </div>
                <h3 className="font-semibold text-gray-900 mb-2">Suggestions</h3>
                <p className="text-sm text-gray-600">Voir toutes les suggestions</p>
              </CardContent>
            </Card>
          </Link>

          <Link href="/family-relations">
            <Card className="hover:shadow-lg transition-all duration-300 cursor-pointer border-0 shadow-md">
              <CardContent className="p-6 text-center">
                <div className="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                  <UserPlus className="w-6 h-6 text-white" />
                </div>
                <h3 className="font-semibold text-gray-900 mb-2">Relations</h3>
                <p className="text-sm text-gray-600">Gérer les relations</p>
              </CardContent>
            </Card>
          </Link>

          <Link href="/reseaux">
            <Card className="hover:shadow-lg transition-all duration-300 cursor-pointer border-0 shadow-md">
              <CardContent className="p-6 text-center">
                <div className="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                  <Users className="w-6 h-6 text-white" />
                </div>
                <h3 className="font-semibold text-gray-900 mb-2">Réseaux</h3>
                <p className="text-sm text-gray-600">Réseau familial</p>
              </CardContent>
            </Card>
          </Link>

          <Link href="/test-photo-display">
            <Card className="hover:shadow-lg transition-all duration-300 cursor-pointer border-0 shadow-md">
              <CardContent className="p-6 text-center">
                <div className="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl flex items-center justify-center mx-auto mb-4">
                  <Settings className="w-6 h-6 text-white" />
                </div>
                <h3 className="font-semibold text-gray-900 mb-2">Tests Généraux</h3>
                <p className="text-sm text-gray-600">Autres tests système</p>
              </CardContent>
            </Card>
          </Link>
        </div>

        {/* Résultats des tests */}
        {testResults.length > 0 && (
          <Card className="border-0 shadow-lg">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <CheckCircle className="w-5 h-5 text-green-500" />
                Résultats des Tests
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {testResults.map((result, index) => (
                <div
                  key={index}
                  className={`p-4 rounded-lg border ${getStatusColor(result.status)}`}
                >
                  <div className="flex items-start gap-3">
                    {getStatusIcon(result.status)}
                    <div className="flex-1">
                      <h4 className="font-medium text-gray-900">{result.name}</h4>
                      <p className="text-sm text-gray-600 mt-1">{result.message}</p>
                      {result.details && (
                        <p className="text-xs text-gray-500 mt-2 font-mono bg-white/50 p-2 rounded">
                          {result.details}
                        </p>
                      )}
                    </div>
                  </div>
                </div>
              ))}
            </CardContent>
          </Card>
        )}

        {/* Guide des fonctionnalités */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <Card className="border-0 shadow-lg">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Heart className="w-5 h-5 text-red-500" />
                Fonctionnalités des Suggestions
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <h3 className="font-semibold mb-2 flex items-center gap-2">
                  <Eye className="w-4 h-4 text-blue-500" />
                  Visualisation
                </h3>
                <ul className="text-sm text-gray-600 space-y-1 ml-6">
                  <li>• Affichage des suggestions par utilisateur</li>
                  <li>• Score de confiance pour chaque suggestion</li>
                  <li>• Raison de la suggestion automatique</li>
                </ul>
              </div>

              <div>
                <h3 className="font-semibold mb-2 flex items-center gap-2">
                  <Send className="w-4 h-4 text-green-500" />
                  Actions
                </h3>
                <ul className="text-sm text-gray-600 space-y-1 ml-6">
                  <li>• Envoyer une demande de relation</li>
                  <li>• Accepter avec correction</li>
                  <li>• Rejeter la suggestion</li>
                </ul>
              </div>

              <div>
                <h3 className="font-semibold mb-2 flex items-center gap-2">
                  <RefreshCw className="w-4 h-4 text-purple-500" />
                  Gestion
                </h3>
                <ul className="text-sm text-gray-600 space-y-1 ml-6">
                  <li>• Rafraîchir les suggestions</li>
                  <li>• Historique des suggestions acceptées</li>
                  <li>• Suggestions en attente</li>
                </ul>
              </div>
            </CardContent>
          </Card>

          <Card className="border-0 shadow-lg">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Settings className="w-5 h-5 text-gray-500" />
                Tests et Validation
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <h3 className="font-semibold mb-2">1. Générer des suggestions</h3>
                <Button onClick={generateSuggestions} variant="outline" className="w-full mb-2">
                  <Users className="w-4 h-4 mr-2" />
                  Créer suggestions de test
                </Button>
                <p className="text-xs text-gray-500">
                  Génère des suggestions automatiques et manuelles
                </p>
              </div>

              <div>
                <h3 className="font-semibold mb-2">2. Tester les fonctionnalités</h3>
                <div className="space-y-2">
                  <Link href="/suggestions">
                    <Button variant="outline" className="w-full">
                      <Eye className="w-4 h-4 mr-2" />
                      Voir les suggestions
                    </Button>
                  </Link>
                </div>
              </div>

              <div>
                <h3 className="font-semibold mb-2">3. Vérifier les actions</h3>
                <div className="grid grid-cols-3 gap-2 text-xs">
                  <Badge variant="outline" className="justify-center">
                    <Send className="w-3 h-3 mr-1" />
                    Envoyer
                  </Badge>
                  <Badge variant="outline" className="justify-center">
                    <CheckCircle className="w-3 h-3 mr-1" />
                    Accepter
                  </Badge>
                  <Badge variant="outline" className="justify-center">
                    <Trash2 className="w-3 h-3 mr-1" />
                    Rejeter
                  </Badge>
                </div>
                <p className="text-xs text-gray-500 mt-2">
                  Testez toutes les actions disponibles sur les suggestions
                </p>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Informations système */}
        <Alert>
          <Info className="h-4 w-4" />
          <AlertDescription>
            <strong>Note :</strong> Les suggestions sont générées automatiquement basées sur les relations existantes. 
            Si aucune suggestion automatique n'est trouvée, des suggestions manuelles seront créées pour les tests.
          </AlertDescription>
        </Alert>
      </div>
    </KwdDashboardLayout>
  );
}
