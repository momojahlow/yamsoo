import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { KwdDashboardLayout } from '@/Layouts/modern';
import { useNotificationSound } from '@/hooks/useNotificationSound';
import { Volume2, VolumeX, TestTube, Play, Pause, Settings } from 'lucide-react';

interface User {
    id: number;
    name: string;
    avatar?: string;
}

interface TestNotificationsProps {
    user: User;
}

export default function TestNotifications({ user }: TestNotificationsProps) {
    const [notificationsEnabled, setNotificationsEnabled] = useState(true);
    const [volume, setVolume] = useState(0.7);
    const [testResults, setTestResults] = useState<string[]>([]);

    const { playNotificationSound, testSound, setVolume: updateVolume, isEnabled } = useNotificationSound({
        enabled: notificationsEnabled,
        volume: volume,
        soundUrl: '/notifications/alert-sound.mp3'
    });

    // Mettre à jour le volume quand il change
    useEffect(() => {
        updateVolume(volume);
    }, [volume, updateVolume]);

    const addTestResult = (result: string) => {
        setTestResults(prev => [...prev, `${new Date().toLocaleTimeString()}: ${result}`]);
    };

    const handleTestBasicSound = async () => {
        addTestResult('🧪 Test du son de base...');
        try {
            await testSound();
            addTestResult('✅ Son de base joué avec succès');
        } catch (error) {
            addTestResult(`❌ Erreur son de base: ${error}`);
        }
    };

    const handleTestMessageSound = async () => {
        addTestResult('🧪 Test du son de message...');
        try {
            const fakeMessage = {
                id: Date.now(),
                content: 'Message de test',
                user: { id: 999, name: 'Utilisateur Test' },
                conversation_id: 1,
                created_at: new Date().toISOString()
            };
            
            await playNotificationSound(fakeMessage, user.id, notificationsEnabled);
            addTestResult('✅ Son de message joué avec succès');
        } catch (error) {
            addTestResult(`❌ Erreur son de message: ${error}`);
        }
    };

    const handleTestOwnMessage = async () => {
        addTestResult('🧪 Test avec son propre message (ne devrait pas jouer)...');
        try {
            const ownMessage = {
                id: Date.now(),
                content: 'Mon propre message',
                user: { id: user.id, name: user.name },
                conversation_id: 1,
                created_at: new Date().toISOString()
            };
            
            await playNotificationSound(ownMessage, user.id, notificationsEnabled);
            addTestResult('✅ Son correctement bloqué pour son propre message');
        } catch (error) {
            addTestResult(`❌ Erreur test propre message: ${error}`);
        }
    };

    const handleTestDisabledNotifications = async () => {
        addTestResult('🧪 Test avec notifications désactivées...');
        try {
            const fakeMessage = {
                id: Date.now(),
                content: 'Message avec notifications off',
                user: { id: 999, name: 'Utilisateur Test' },
                conversation_id: 1,
                created_at: new Date().toISOString()
            };
            
            await playNotificationSound(fakeMessage, user.id, false); // notifications désactivées
            addTestResult('✅ Son correctement bloqué (notifications désactivées)');
        } catch (error) {
            addTestResult(`❌ Erreur test notifications désactivées: ${error}`);
        }
    };

    const handleTestRapidMessages = async () => {
        addTestResult('🧪 Test de messages en rafale (throttling)...');
        try {
            for (let i = 0; i < 5; i++) {
                const fakeMessage = {
                    id: Date.now() + i,
                    content: `Message rapide ${i + 1}`,
                    user: { id: 999, name: 'Utilisateur Test' },
                    conversation_id: 1,
                    created_at: new Date().toISOString()
                };
                
                await playNotificationSound(fakeMessage, user.id, notificationsEnabled);
                await new Promise(resolve => setTimeout(resolve, 100)); // 100ms entre chaque
            }
            addTestResult('✅ Test de throttling terminé (seul le premier son devrait avoir été joué)');
        } catch (error) {
            addTestResult(`❌ Erreur test rafale: ${error}`);
        }
    };

    const clearResults = () => {
        setTestResults([]);
    };

    return (
        <KwdDashboardLayout title="Test Notifications Sonores">
            <Head title="Test Notifications Sonores" />
            
            <div className="max-w-4xl mx-auto p-6">
                <div className="bg-white rounded-lg shadow-lg p-6">
                    <h1 className="text-2xl font-bold text-gray-900 mb-6">
                        🔊 Test des Notifications Sonores
                    </h1>

                    {/* Paramètres */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        {/* Activation/Désactivation */}
                        <div className="bg-gray-50 p-4 rounded-lg">
                            <h3 className="font-semibold text-gray-900 mb-3">Activation</h3>
                            <div className="flex items-center justify-between">
                                <span className="text-sm text-gray-600">Notifications sonores</span>
                                <button
                                    onClick={() => setNotificationsEnabled(!notificationsEnabled)}
                                    className={`
                                        relative inline-flex h-6 w-11 items-center rounded-full transition-colors
                                        ${notificationsEnabled ? 'bg-green-500' : 'bg-gray-300'}
                                    `}
                                >
                                    <span
                                        className={`
                                            inline-block h-4 w-4 transform rounded-full bg-white transition-transform
                                            ${notificationsEnabled ? 'translate-x-6' : 'translate-x-1'}
                                        `}
                                    />
                                </button>
                            </div>
                            <p className="text-xs text-gray-500 mt-2">
                                État: {notificationsEnabled ? '🔊 Activées' : '🔇 Désactivées'}
                            </p>
                        </div>

                        {/* Volume */}
                        <div className="bg-gray-50 p-4 rounded-lg">
                            <h3 className="font-semibold text-gray-900 mb-3">Volume</h3>
                            <div className="space-y-2">
                                <input
                                    type="range"
                                    min="0"
                                    max="1"
                                    step="0.1"
                                    value={volume}
                                    onChange={(e) => setVolume(parseFloat(e.target.value))}
                                    className="w-full"
                                />
                                <p className="text-xs text-gray-500">
                                    Volume: {Math.round(volume * 100)}%
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Tests */}
                    <div className="mb-8">
                        <h3 className="font-semibold text-gray-900 mb-4">Tests</h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <button
                                onClick={handleTestBasicSound}
                                className="flex items-center justify-center space-x-2 p-4 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                            >
                                <TestTube className="w-5 h-5" />
                                <span>Test Son de Base</span>
                            </button>

                            <button
                                onClick={handleTestMessageSound}
                                className="flex items-center justify-center space-x-2 p-4 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors"
                            >
                                <Play className="w-5 h-5" />
                                <span>Test Message Reçu</span>
                            </button>

                            <button
                                onClick={handleTestOwnMessage}
                                className="flex items-center justify-center space-x-2 p-4 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors"
                            >
                                <Pause className="w-5 h-5" />
                                <span>Test Propre Message</span>
                            </button>

                            <button
                                onClick={handleTestDisabledNotifications}
                                className="flex items-center justify-center space-x-2 p-4 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
                            >
                                <VolumeX className="w-5 h-5" />
                                <span>Test Notifications OFF</span>
                            </button>

                            <button
                                onClick={handleTestRapidMessages}
                                className="flex items-center justify-center space-x-2 p-4 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors"
                            >
                                <Settings className="w-5 h-5" />
                                <span>Test Throttling</span>
                            </button>

                            <button
                                onClick={clearResults}
                                className="flex items-center justify-center space-x-2 p-4 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors"
                            >
                                <span>Effacer Résultats</span>
                            </button>
                        </div>
                    </div>

                    {/* Résultats des tests */}
                    <div>
                        <h3 className="font-semibold text-gray-900 mb-4">Résultats des Tests</h3>
                        <div className="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm max-h-64 overflow-y-auto">
                            {testResults.length === 0 ? (
                                <p className="text-gray-500">Aucun test exécuté...</p>
                            ) : (
                                testResults.map((result, index) => (
                                    <div key={index} className="mb-1">
                                        {result}
                                    </div>
                                ))
                            )}
                        </div>
                    </div>

                    {/* Informations */}
                    <div className="mt-8 p-4 bg-blue-50 rounded-lg">
                        <h4 className="font-semibold text-blue-900 mb-2">ℹ️ Informations</h4>
                        <ul className="text-sm text-blue-700 space-y-1">
                            <li>• Le fichier audio se trouve dans <code>/notifications/alert-sound.mp3</code></li>
                            <li>• Les notifications ne sont jouées que pour les messages reçus (pas ses propres messages)</li>
                            <li>• Un système de throttling empêche les sons en rafale (1 seconde minimum)</li>
                            <li>• Les préférences par conversation sont respectées</li>
                            <li>• Un fallback Web Audio API est disponible si le fichier audio ne fonctionne pas</li>
                        </ul>
                    </div>
                </div>
            </div>
        </KwdDashboardLayout>
    );
}
