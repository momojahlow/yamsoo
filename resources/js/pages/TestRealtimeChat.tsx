import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { useConversationChannel } from '@/hooks/useReverb';
import { useNotificationSound } from '@/hooks/useNotificationSound';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Message {
    id: number;
    content: string;
    type: 'text' | 'image' | 'file' | 'audio' | 'video';
    created_at: string;
    user: User;
}

interface TestRealtimeChatProps {
    user: User;
    otherUser: User;
    conversation: {
        id: number;
        type: string;
    };
    messages: Message[];
}

export default function TestRealtimeChat({ user, otherUser, conversation, messages }: TestRealtimeChatProps) {
    const [realtimeMessages, setRealtimeMessages] = useState<Message[]>(messages);
    const [newMessage, setNewMessage] = useState('');
    const [isConnected, setIsConnected] = useState(false);
    const [logs, setLogs] = useState<string[]>([]);
    
    const { playNotification, playMessageSent } = useNotificationSound();
    
    const addLog = (message: string) => {
        const timestamp = new Date().toLocaleTimeString();
        setLogs(prev => [...prev, `[${timestamp}] ${message}`]);
    };
    
    const handleNewMessage = (message: Message) => {
        addLog(`📨 Message reçu de ${message.user.name}: "${message.content}"`);
        setRealtimeMessages(prev => {
            const exists = prev.some(m => m.id === message.id);
            if (exists) {
                addLog('⚠️ Message déjà présent, ignoré');
                return prev;
            }
            return [...prev, message];
        });
        
        if (message.user.id !== user.id) {
            playNotification();
        }
    };
    
    useConversationChannel(conversation.id, handleNewMessage);
    
    useEffect(() => {
        addLog(`🔊 Écoute de la conversation ${conversation.id}`);
        setIsConnected(true);
        
        return () => {
            addLog('👋 Déconnexion du channel');
        };
    }, [conversation.id]);
    
    const sendMessage = async () => {
        if (!newMessage.trim()) return;
        
        addLog(`📤 Envoi du message: "${newMessage}"`);
        
        try {
            const response = await fetch('/messagerie/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    conversation_id: conversation.id,
                    message: newMessage,
                }),
            });
            
            if (response.ok) {
                addLog('✅ Message envoyé avec succès');
                setNewMessage('');
                playMessageSent();
            } else {
                addLog('❌ Erreur lors de l\'envoi');
            }
        } catch (error) {
            addLog(`❌ Erreur: ${error}`);
        }
    };
    
    const clearLogs = () => {
        setLogs([]);
    };
    
    return (
        <>
            <Head title="Test Chat Temps Réel" />
            
            <div className="min-h-screen bg-gray-100 p-4">
                <div className="max-w-6xl mx-auto">
                    <div className="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h1 className="text-2xl font-bold mb-4">🧪 Test Chat Temps Réel</h1>
                        
                        <div className="grid grid-cols-2 gap-4 mb-4">
                            <div className="bg-blue-50 p-4 rounded">
                                <h3 className="font-semibold text-blue-800">Utilisateur connecté</h3>
                                <p className="text-blue-600">{user.name} ({user.email})</p>
                            </div>
                            <div className="bg-green-50 p-4 rounded">
                                <h3 className="font-semibold text-green-800">Conversation avec</h3>
                                <p className="text-green-600">{otherUser.name} ({otherUser.email})</p>
                            </div>
                        </div>
                        
                        <div className="flex items-center gap-4 mb-4">
                            <div className={`flex items-center gap-2 ${isConnected ? 'text-green-600' : 'text-red-600'}`}>
                                <div className={`w-3 h-3 rounded-full ${isConnected ? 'bg-green-500' : 'bg-red-500'}`}></div>
                                <span>{isConnected ? 'Connecté au temps réel' : 'Déconnecté'}</span>
                            </div>
                            <div className="text-gray-600">
                                Conversation ID: {conversation.id}
                            </div>
                        </div>
                    </div>
                    
                    <div className="grid grid-cols-2 gap-6">
                        {/* Zone de chat */}
                        <div className="bg-white rounded-lg shadow-lg">
                            <div className="p-4 border-b">
                                <h2 className="text-lg font-semibold">💬 Messages</h2>
                            </div>
                            
                            <div className="h-96 overflow-y-auto p-4 space-y-3">
                                {realtimeMessages.map((message) => (
                                    <div
                                        key={message.id}
                                        className={`flex ${message.user.id === user.id ? 'justify-end' : 'justify-start'}`}
                                    >
                                        <div
                                            className={`max-w-xs px-4 py-2 rounded-lg ${
                                                message.user.id === user.id
                                                    ? 'bg-blue-500 text-white'
                                                    : 'bg-gray-200 text-gray-800'
                                            }`}
                                        >
                                            <div className="text-xs opacity-75 mb-1">
                                                {message.user.name} - {new Date(message.created_at).toLocaleTimeString()}
                                            </div>
                                            <div>{message.content}</div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            
                            <div className="p-4 border-t">
                                <div className="flex gap-2">
                                    <input
                                        type="text"
                                        value={newMessage}
                                        onChange={(e) => setNewMessage(e.target.value)}
                                        onKeyPress={(e) => e.key === 'Enter' && sendMessage()}
                                        placeholder="Tapez votre message..."
                                        className="flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                    <button
                                        onClick={sendMessage}
                                        disabled={!newMessage.trim()}
                                        className="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50"
                                    >
                                        Envoyer
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        {/* Logs de debug */}
                        <div className="bg-white rounded-lg shadow-lg">
                            <div className="p-4 border-b flex justify-between items-center">
                                <h2 className="text-lg font-semibold">🔍 Logs de Debug</h2>
                                <button
                                    onClick={clearLogs}
                                    className="px-3 py-1 text-sm bg-gray-500 text-white rounded hover:bg-gray-600"
                                >
                                    Effacer
                                </button>
                            </div>
                            
                            <div className="h-96 overflow-y-auto p-4">
                                <div className="font-mono text-sm space-y-1">
                                    {logs.map((log, index) => (
                                        <div key={index} className="text-gray-700">
                                            {log}
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div className="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <h3 className="font-semibold text-yellow-800 mb-2">📋 Instructions de test</h3>
                        <ol className="list-decimal list-inside text-yellow-700 space-y-1">
                            <li>Ouvrez cette page dans 2 onglets différents</li>
                            <li>Connectez-vous avec user1@test.com dans le premier onglet</li>
                            <li>Connectez-vous avec user2@test.com dans le second onglet</li>
                            <li>Envoyez des messages dans chaque onglet</li>
                            <li>Vérifiez que les messages apparaissent instantanément dans l'autre onglet</li>
                            <li>Observez les logs pour voir les événements temps réel</li>
                        </ol>
                    </div>
                </div>
            </div>
        </>
    );
}
