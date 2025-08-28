import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { KwdDashboardLayout } from '@/Layouts/modern';
import { useMessenger } from '@/contexts/MessengerContext';
import { MessageSquare, RefreshCw, Users, Clock, Bell, Volume2 } from 'lucide-react';
import { Badge } from '@/components/ui/badge';

interface User {
    id: number;
    name: string;
    avatar?: string;
}

interface TestMessengerProps {
    user: User;
}

export default function TestMessenger({ user }: TestMessengerProps) {
    const [logs, setLogs] = useState<string[]>([]);
    const [autoRefresh, setAutoRefresh] = useState(true);

    // État du messenger depuis le Context
    const {
        state: { conversations, totalUnreadCount, loading, error, lastUpdated },
        fetchConversations,
        markAsRead,
        addMessage
    } = useMessenger();

    const addLog = (message: string) => {
        const timestamp = new Date().toLocaleTimeString();
        setLogs(prev => [...prev.slice(-20), `${timestamp}: ${message}`]);
    };

    // Auto-refresh des conversations
    useEffect(() => {
        if (autoRefresh) {
            const interval = setInterval(() => {
                fetchConversations();
                addLog('🔄 Actualisation automatique des conversations');
            }, 10000); // Toutes les 10 secondes

            return () => clearInterval(interval);
        }
    }, [autoRefresh, fetchConversations]);

    // Logger les changements d'état
    useEffect(() => {
        if (lastUpdated > 0) {
            addLog(`📊 État mis à jour: ${conversations.length} conversations, ${totalUnreadCount} non lus`);
        }
    }, [lastUpdated, conversations.length, totalUnreadCount]);

    const handleManualRefresh = () => {
        fetchConversations();
        addLog('🔄 Actualisation manuelle déclenchée');
    };

    const handleTestMarkAsRead = (conversationId: number) => {
        markAsRead(conversationId);
        addLog(`✅ Conversation ${conversationId} marquée comme lue`);
    };

    const handleTestAddMessage = () => {
        if (conversations.length > 0) {
            const testConversation = conversations[0];
            const fakeMessage = {
                id: Date.now(),
                content: 'Message de test généré',
                user: { id: 999, name: 'Utilisateur Test' },
                conversation_id: testConversation.id,
                created_at: new Date().toISOString()
            };

            addMessage(testConversation.id, fakeMessage, user.id);
            addLog(`📨 Message de test ajouté à la conversation ${testConversation.id}`);
        } else {
            addLog('❌ Aucune conversation disponible pour le test');
        }
    };

    const clearLogs = () => {
        setLogs([]);
    };

    const formatTime = (dateString: string) => {
        const date = new Date(dateString);
        return date.toLocaleTimeString();
    };

    return (
        <KwdDashboardLayout title="Test Messenger System">
            <Head title="Test Messenger System" />
            
            <div className="max-w-6xl mx-auto p-6">
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* État du système */}
                    <div className="bg-white rounded-lg shadow-lg p-6">
                        <h2 className="text-xl font-bold text-gray-900 mb-4 flex items-center">
                            <MessageSquare className="w-6 h-6 mr-2 text-orange-500" />
                            État du Messenger
                        </h2>

                        <div className="space-y-4">
                            {/* Statistiques */}
                            <div className="grid grid-cols-2 gap-4">
                                <div className="bg-blue-50 p-4 rounded-lg">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-blue-600">Conversations</span>
                                        <Badge variant="secondary">{conversations.length}</Badge>
                                    </div>
                                </div>
                                <div className="bg-red-50 p-4 rounded-lg">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-red-600">Non lus</span>
                                        <Badge variant="destructive">{totalUnreadCount}</Badge>
                                    </div>
                                </div>
                            </div>

                            {/* État */}
                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm text-gray-600">Chargement:</span>
                                    <span className={`text-sm font-medium ${loading ? 'text-yellow-600' : 'text-green-600'}`}>
                                        {loading ? '🔄 En cours...' : '✅ Terminé'}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm text-gray-600">Erreur:</span>
                                    <span className={`text-sm font-medium ${error ? 'text-red-600' : 'text-green-600'}`}>
                                        {error || '✅ Aucune'}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm text-gray-600">Dernière MAJ:</span>
                                    <span className="text-sm text-gray-500">
                                        {lastUpdated > 0 ? new Date(lastUpdated).toLocaleTimeString() : 'Jamais'}
                                    </span>
                                </div>
                            </div>

                            {/* Actions */}
                            <div className="space-y-2">
                                <button
                                    onClick={handleManualRefresh}
                                    disabled={loading}
                                    className="w-full flex items-center justify-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 transition-colors"
                                >
                                    <RefreshCw className={`w-4 h-4 mr-2 ${loading ? 'animate-spin' : ''}`} />
                                    Actualiser
                                </button>

                                <button
                                    onClick={handleTestAddMessage}
                                    className="w-full flex items-center justify-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors"
                                >
                                    <MessageSquare className="w-4 h-4 mr-2" />
                                    Simuler nouveau message
                                </button>

                                <div className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <span className="text-sm text-gray-600">Auto-refresh</span>
                                    <button
                                        onClick={() => setAutoRefresh(!autoRefresh)}
                                        className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors ${
                                            autoRefresh ? 'bg-green-500' : 'bg-gray-300'
                                        }`}
                                    >
                                        <span
                                            className={`inline-block h-4 w-4 transform rounded-full bg-white transition-transform ${
                                                autoRefresh ? 'translate-x-6' : 'translate-x-1'
                                            }`}
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Liste des conversations */}
                    <div className="bg-white rounded-lg shadow-lg p-6">
                        <h2 className="text-xl font-bold text-gray-900 mb-4 flex items-center">
                            <Users className="w-6 h-6 mr-2 text-blue-500" />
                            Conversations ({conversations.length})
                        </h2>

                        <div className="space-y-3 max-h-80 overflow-y-auto">
                            {conversations.length === 0 ? (
                                <div className="text-center py-8">
                                    <MessageSquare className="w-12 h-12 text-gray-300 mx-auto mb-3" />
                                    <p className="text-gray-500">Aucune conversation</p>
                                </div>
                            ) : (
                                conversations.map((conversation) => (
                                    <div key={conversation.id} className="border border-gray-200 rounded-lg p-3">
                                        <div className="flex items-center justify-between mb-2">
                                            <div className="flex items-center space-x-2">
                                                <span className="font-medium text-gray-900">
                                                    {conversation.name}
                                                </span>
                                                <Badge variant={conversation.type === 'group' ? 'default' : 'secondary'}>
                                                    {conversation.type === 'group' ? '👥' : '💬'}
                                                </Badge>
                                                {conversation.unread_count > 0 && (
                                                    <Badge variant="destructive">
                                                        {conversation.unread_count}
                                                    </Badge>
                                                )}
                                            </div>
                                            <button
                                                onClick={() => handleTestMarkAsRead(conversation.id)}
                                                className="text-xs text-blue-600 hover:text-blue-700"
                                            >
                                                Marquer lu
                                            </button>
                                        </div>
                                        
                                        {conversation.last_message && (
                                            <div className="text-sm text-gray-600">
                                                <span className="font-medium">
                                                    {conversation.last_message.is_own ? 'Vous' : conversation.last_message.user_name}:
                                                </span>
                                                <span className="ml-1">
                                                    {conversation.last_message.content.length > 50 
                                                        ? conversation.last_message.content.substring(0, 50) + '...'
                                                        : conversation.last_message.content
                                                    }
                                                </span>
                                                <div className="flex items-center mt-1 text-xs text-gray-500">
                                                    <Clock className="w-3 h-3 mr-1" />
                                                    {formatTime(conversation.last_message.created_at)}
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>

                {/* Logs */}
                <div className="mt-6 bg-white rounded-lg shadow-lg p-6">
                    <div className="flex items-center justify-between mb-4">
                        <h2 className="text-xl font-bold text-gray-900 flex items-center">
                            <Bell className="w-6 h-6 mr-2 text-green-500" />
                            Logs du système
                        </h2>
                        <button
                            onClick={clearLogs}
                            className="text-sm text-gray-600 hover:text-gray-700"
                        >
                            Effacer
                        </button>
                    </div>

                    <div className="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm max-h-64 overflow-y-auto">
                        {logs.length === 0 ? (
                            <p className="text-gray-500">Aucun log...</p>
                        ) : (
                            logs.map((log, index) => (
                                <div key={index} className="mb-1">
                                    {log}
                                </div>
                            ))
                        )}
                    </div>
                </div>

                {/* Instructions */}
                <div className="mt-6 p-4 bg-blue-50 rounded-lg">
                    <h3 className="font-semibold text-blue-900 mb-2">💡 Instructions de test</h3>
                    <ul className="text-sm text-blue-700 space-y-1">
                        <li>• L'icône Messenger apparaît dans la barre de navigation en haut à droite</li>
                        <li>• Le badge rouge montre le nombre total de messages non lus</li>
                        <li>• Cliquez sur l'icône pour ouvrir le dropdown avec les conversations</li>
                        <li>• Les conversations se mettent à jour en temps réel via Echo</li>
                        <li>• Un son est joué quand un nouveau message arrive (si dropdown fermé)</li>
                        <li>• Testez en envoyant des messages depuis un autre compte</li>
                    </ul>
                </div>
            </div>
        </KwdDashboardLayout>
    );
}
