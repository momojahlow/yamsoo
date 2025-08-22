import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Conversation {
    id: number;
    name: string;
    type: string;
    created_by: number;
    participants: Array<{
        id: number;
        name: string;
    }>;
    messages_count: number;
    last_message?: {
        content: string;
        user: string;
        created_at: string;
    };
}

interface Message {
    id: number;
    content: string;
    conversation_id: number;
    user: string;
    created_at: string;
}

interface TestMessagingProps {
    users: User[];
    conversations: Conversation[];
    recent_messages: Message[];
    error?: string;
}

export default function TestMessaging({ users, conversations, recent_messages, error }: TestMessagingProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        conversation_id: '',
        message: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/test-messaging/send', {
            onSuccess: () => {
                reset('message');
            }
        });
    };

    return (
        <>
            <Head title="Test Messagerie" />
            
            <div className="min-h-screen bg-gray-50 p-6">
                <div className="max-w-6xl mx-auto space-y-6">
                    <h1 className="text-3xl font-bold text-gray-900">Test du Système de Messagerie</h1>

                    {error && (
                        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <strong>Erreur:</strong> {error}
                        </div>
                    )}
                    
                    {/* Utilisateurs */}
                    <Card>
                        <CardHeader>
                            <CardTitle>👥 Utilisateurs ({users.length})</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                {users.map(user => (
                                    <div key={user.id} className="p-3 bg-blue-50 rounded-lg">
                                        <p className="font-medium">{user.name}</p>
                                        <p className="text-sm text-gray-600">ID: {user.id}</p>
                                        <p className="text-sm text-gray-600">{user.email}</p>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Conversations */}
                    <Card>
                        <CardHeader>
                            <CardTitle>💬 Conversations ({conversations.length})</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {conversations.map(conv => (
                                    <div key={conv.id} className="p-4 border rounded-lg bg-white">
                                        <div className="flex justify-between items-start mb-2">
                                            <div>
                                                <h3 className="font-medium">
                                                    {conv.name || `Conversation ${conv.type}`} (ID: {conv.id})
                                                </h3>
                                                <p className="text-sm text-gray-600">
                                                    Type: {conv.type} | Messages: {conv.messages_count}
                                                </p>
                                            </div>
                                            <span className="text-xs bg-gray-100 px-2 py-1 rounded">
                                                Créé par: {conv.created_by}
                                            </span>
                                        </div>
                                        
                                        <div className="mb-2">
                                            <p className="text-sm font-medium">Participants:</p>
                                            <div className="flex gap-2">
                                                {conv.participants.map(p => (
                                                    <span key={p.id} className="text-xs bg-green-100 px-2 py-1 rounded">
                                                        {p.name} ({p.id})
                                                    </span>
                                                ))}
                                            </div>
                                        </div>

                                        {conv.last_message && (
                                            <div className="text-sm bg-gray-50 p-2 rounded">
                                                <p><strong>{conv.last_message.user}:</strong> {conv.last_message.content}</p>
                                                <p className="text-xs text-gray-500">{conv.last_message.created_at}</p>
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Messages récents */}
                    <Card>
                        <CardHeader>
                            <CardTitle>📝 Messages récents ({recent_messages.length})</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                {recent_messages.map(msg => (
                                    <div key={msg.id} className="p-3 bg-yellow-50 rounded-lg">
                                        <div className="flex justify-between items-start">
                                            <div>
                                                <p className="font-medium">{msg.user}</p>
                                                <p className="text-sm">{msg.content}</p>
                                            </div>
                                            <div className="text-xs text-gray-500">
                                                <p>ID: {msg.id}</p>
                                                <p>Conv: {msg.conversation_id}</p>
                                                <p>{msg.created_at}</p>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Test d'envoi de message */}
                    <Card>
                        <CardHeader>
                            <CardTitle>🧪 Test d'envoi de message</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium mb-2">Conversation</label>
                                    <select
                                        value={data.conversation_id}
                                        onChange={(e) => setData('conversation_id', e.target.value)}
                                        className="w-full p-2 border rounded-lg"
                                        required
                                    >
                                        <option value="">Sélectionner une conversation</option>
                                        {conversations.map(conv => (
                                            <option key={conv.id} value={conv.id}>
                                                {conv.name || `Conversation ${conv.type}`} (ID: {conv.id})
                                            </option>
                                        ))}
                                    </select>
                                    {errors.conversation_id && (
                                        <p className="text-red-600 text-sm mt-1">{errors.conversation_id}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium mb-2">Message</label>
                                    <Input
                                        value={data.message}
                                        onChange={(e) => setData('message', e.target.value)}
                                        placeholder="Tapez votre message de test..."
                                        required
                                    />
                                    {errors.message && (
                                        <p className="text-red-600 text-sm mt-1">{errors.message}</p>
                                    )}
                                </div>

                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Envoi...' : 'Envoyer le message de test'}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>

                    {/* Liens utiles */}
                    <Card>
                        <CardHeader>
                            <CardTitle>🔗 Liens utiles</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                <a href="/messagerie" className="block text-blue-600 hover:underline">
                                    📱 Interface de messagerie principale
                                </a>
                                <a href="/famille" className="block text-blue-600 hover:underline">
                                    👨‍👩‍👧 Page famille
                                </a>
                                <a href="/networks" className="block text-blue-600 hover:underline">
                                    🌐 Page réseaux
                                </a>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
