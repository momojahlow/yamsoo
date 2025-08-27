import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import { Volume2, VolumeX, TestTube } from 'lucide-react';
import { useNotificationSound } from '@/hooks/useNotificationSound';

interface User {
    id: number;
    name: string;
    avatar?: string;
}

interface Conversation {
    id: number;
    name: string;
    type: 'private' | 'group';
}

interface NotificationSettingsProps {
    conversation: Conversation;
    user: User;
    notificationsEnabled: boolean;
    onClose?: () => void;
}

export default function NotificationSettings({ 
    conversation, 
    user, 
    notificationsEnabled: initialNotificationsEnabled, 
    onClose 
}: NotificationSettingsProps) {
    const [notificationsEnabled, setNotificationsEnabled] = useState(initialNotificationsEnabled);
    const [isUpdating, setIsUpdating] = useState(false);

    // Hook pour tester le son
    const { testSound } = useNotificationSound({
        enabled: true,
        volume: 0.7,
        soundUrl: '/notifications/alert-sound.mp3'
    });

    const handleToggleNotifications = async () => {
        setIsUpdating(true);

        try {
            router.patch(`/conversations/${conversation.id}/notifications`, {
                notifications_enabled: !notificationsEnabled
            }, {
                onSuccess: () => {
                    setNotificationsEnabled(!notificationsEnabled);
                    console.log('✅ Préférences de notification mises à jour');
                },
                onError: (errors) => {
                    console.error('❌ Erreur lors de la mise à jour des préférences:', errors);
                },
                onFinish: () => {
                    setIsUpdating(false);
                }
            });
        } catch (error) {
            console.error('❌ Erreur:', error);
            setIsUpdating(false);
        }
    };

    const handleTestSound = async () => {
        try {
            await testSound();
        } catch (error) {
            console.error('❌ Erreur lors du test du son:', error);
        }
    };

    return (
        <div className="bg-white rounded-lg shadow-lg border border-gray-200 p-6 max-w-md">
            {/* Header */}
            <div className="flex items-center justify-between mb-6">
                <h3 className="text-lg font-semibold text-gray-900">Notifications</h3>
                {onClose && (
                    <button
                        onClick={onClose}
                        className="text-gray-400 hover:text-gray-600"
                    >
                        ✕
                    </button>
                )}
            </div>

            {/* Informations sur la conversation */}
            <div className="mb-6 p-4 bg-gray-50 rounded-lg">
                <div className="flex items-center space-x-3">
                    <div className="w-10 h-10 rounded-full bg-gradient-to-br from-orange-200 to-red-200 flex items-center justify-center">
                        {conversation.type === 'group' ? '👥' : '💬'}
                    </div>
                    <div>
                        <h4 className="font-medium text-gray-900">{conversation.name}</h4>
                        <p className="text-sm text-gray-500">
                            {conversation.type === 'group' ? 'Groupe' : 'Conversation privée'}
                        </p>
                    </div>
                </div>
            </div>

            {/* Paramètres de notification */}
            <div className="space-y-4">
                {/* Toggle notifications sonores */}
                <div className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div className="flex items-center space-x-3">
                        {notificationsEnabled ? (
                            <Volume2 className="w-5 h-5 text-green-500" />
                        ) : (
                            <VolumeX className="w-5 h-5 text-gray-400" />
                        )}
                        <div>
                            <h4 className="font-medium text-gray-900">Notifications sonores</h4>
                            <p className="text-sm text-gray-500">
                                {notificationsEnabled 
                                    ? 'Son activé pour les nouveaux messages' 
                                    : 'Son désactivé pour cette conversation'
                                }
                            </p>
                        </div>
                    </div>
                    <button
                        onClick={handleToggleNotifications}
                        disabled={isUpdating}
                        className={`
                            relative inline-flex h-6 w-11 items-center rounded-full transition-colors
                            ${notificationsEnabled ? 'bg-green-500' : 'bg-gray-300'}
                            ${isUpdating ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}
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

                {/* Test du son */}
                <div className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div className="flex items-center space-x-3">
                        <TestTube className="w-5 h-5 text-blue-500" />
                        <div>
                            <h4 className="font-medium text-gray-900">Tester le son</h4>
                            <p className="text-sm text-gray-500">
                                Écouter le son de notification
                            </p>
                        </div>
                    </div>
                    <button
                        onClick={handleTestSound}
                        className="px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors"
                    >
                        🔊 Test
                    </button>
                </div>

                {/* Informations */}
                <div className="p-4 bg-blue-50 rounded-lg">
                    <p className="text-sm text-blue-700">
                        💡 <strong>Astuce :</strong> Les notifications sonores ne sont jouées que pour les messages reçus, 
                        pas pour vos propres messages. Le son peut être bloqué par votre navigateur si vous n'avez pas 
                        encore interagi avec la page.
                    </p>
                </div>
            </div>

            {/* Actions */}
            <div className="mt-6 flex justify-end">
                <button
                    onClick={onClose}
                    className="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors"
                >
                    Fermer
                </button>
            </div>
        </div>
    );
}
