import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { KwdDashboardLayout } from '@/Layouts/modern';
import { useTranslation } from '@/hooks/useTranslation';

interface Conversation {
    id: number;
    name: string;
    other_user_id: number;
    last_message?: string;
    last_message_time?: string;
}

interface Message {
    id: number;
    content: string;
    user_id: number;
    user_name: string;
    created_at: string;
    is_mine: boolean;
}

interface SimpleMessagingProps {
    conversations: Conversation[];
    selectedConversation?: Conversation | null;
    messages: Message[];
    user: {
        id: number;
        name: string;
    };
}

export default function SimpleMessaging({ conversations, selectedConversation, messages, user }: SimpleMessagingProps) {
    const { t } = useTranslation();
    const { data, setData, post, processing, reset } = useForm({
        conversation_id: selectedConversation?.id || '',
        message: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!data.message.trim()) return;

        post('/simple-messaging/send', {
            onSuccess: () => {
                reset('message');
            }
        });
    };

    return (
        <KwdDashboardLayout title={t('messaging')}>
            <Head title="Messagerie Simple" />

            <div className="h-full">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div className="flex h-[calc(100vh-200px)] bg-white rounded-lg shadow-lg overflow-hidden">
                        {/* Liste des conversations */}
                        <div className="w-1/3 bg-white border-r border-gray-200">
                            <div className="p-4 border-b border-gray-200">
                                <h1 className="text-xl font-semibold">Messages</h1>
                            </div>

                            <div className="overflow-y-auto">
                                {conversations.length === 0 ? (
                                    <div className="p-4 text-gray-500 text-center">
                                        Aucune conversation
                                    </div>
                                ) : (
                                    conversations.map(conv => (
                                        <a
                                            key={conv.id}
                                            href={`/simple-messaging?selectedContactId=${conv.other_user_id}`}
                                            className={`block p-4 border-b border-gray-100 hover:bg-gray-50 ${
                                                selectedConversation?.id === conv.id ? 'bg-orange-50 border-l-4 border-l-orange-500' : ''
                                            }`}
                                        >
                                            <div className="font-medium">{conv.name}</div>
                                            {conv.last_message && (
                                                <div className="text-sm text-gray-500 truncate">
                                                    {conv.last_message}
                                                </div>
                                            )}
                                            {conv.last_message_time && (
                                                <div className="text-xs text-gray-400">
                                                    {conv.last_message_time}
                                                </div>
                                            )}
                                        </a>
                                    ))
                                )}
                            </div>
                        </div>

                        {/* Zone de chat */}
                        <div className="flex-1 flex flex-col">
                            {selectedConversation ? (
                                <>
                                    {/* En-tête */}
                                    <div className="p-4 border-b border-gray-200 bg-white">
                                        <h2 className="text-lg font-semibold">{selectedConversation.name}</h2>
                                    </div>

                                    {/* Messages */}
                                    <div className="flex-1 overflow-y-auto p-4 space-y-4">
                                        {messages.length === 0 ? (
                                            <div className="text-center text-gray-500">
                                                Aucun message. Commencez la conversation !
                                            </div>
                                        ) : (
                                            messages.map(message => (
                                                <div
                                                    key={message.id}
                                                    className={`flex ${message.is_mine ? 'justify-end' : 'justify-start'}`}
                                                >
                                                    <div
                                                        className={`max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
                                                            message.is_mine
                                                                ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white'
                                                                : 'bg-gray-200 text-gray-900'
                                                        }`}
                                                    >
                                                        {!message.is_mine && (
                                                            <div className="text-xs font-medium mb-1">
                                                                {message.user_name}
                                                            </div>
                                                        )}
                                                        <div>{message.content}</div>
                                                        <div className={`text-xs mt-1 ${
                                                            message.is_mine ? 'text-orange-100' : 'text-gray-500'
                                                        }`}>
                                                            {message.created_at}
                                                        </div>
                                                    </div>
                                                </div>
                                            ))
                                        )}
                                    </div>

                                    {/* Formulaire d'envoi */}
                                    <div className="p-4 border-t border-gray-200 bg-white">
                                        <form onSubmit={handleSubmit} className="flex gap-2">
                                            <Input
                                                value={data.message}
                                                onChange={(e) => setData('message', e.target.value)}
                                                placeholder="Tapez votre message..."
                                                className="flex-1"
                                                disabled={processing}
                                            />
                                            <Button
                                                type="submit"
                                                disabled={processing || !data.message.trim()}
                                                className="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600"
                                            >
                                                {processing ? 'Envoi...' : 'Envoyer'}
                                            </Button>
                                        </form>
                                    </div>
                                </>
                            ) : (
                                <div className="flex-1 flex items-center justify-center text-gray-500">
                                    <div className="text-center">
                                        <h3 className="text-lg font-medium mb-2">Sélectionnez une conversation</h3>
                                        <p>Choisissez une conversation dans la liste pour commencer à discuter</p>
                                        <div className="mt-4">
                                            <a href="/famille" className="text-orange-500 hover:underline">
                                                Aller à la page famille pour envoyer un message
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </KwdDashboardLayout>
    );
}
