import React, { useState } from 'react';
import { Settings, Bell, BellOff, Volume2, VolumeX, Moon, Sun, Palette, Download } from 'lucide-react';

interface MessageSettingsProps {
    isOpen: boolean;
    onClose: () => void;
}

interface SettingsState {
    notifications: {
        enabled: boolean;
        sound: boolean;
        desktop: boolean;
        preview: boolean;
    };
    appearance: {
        theme: 'light' | 'dark' | 'auto';
        fontSize: 'small' | 'medium' | 'large';
        bubbleStyle: 'rounded' | 'square';
    };
    privacy: {
        readReceipts: boolean;
        onlineStatus: boolean;
        typing: boolean;
    };
    storage: {
        autoDownload: boolean;
        maxFileSize: number;
        deleteAfter: number;
    };
}

export default function MessageSettings({ isOpen, onClose }: MessageSettingsProps) {
    const [settings, setSettings] = useState<SettingsState>({
        notifications: {
            enabled: true,
            sound: true,
            desktop: true,
            preview: true
        },
        appearance: {
            theme: 'light',
            fontSize: 'medium',
            bubbleStyle: 'rounded'
        },
        privacy: {
            readReceipts: true,
            onlineStatus: true,
            typing: true
        },
        storage: {
            autoDownload: true,
            maxFileSize: 10,
            deleteAfter: 30
        }
    });

    const updateSetting = (category: keyof SettingsState, key: string, value: any) => {
        setSettings(prev => ({
            ...prev,
            [category]: {
                ...prev[category],
                [key]: value
            }
        }));
    };

    const saveSettings = async () => {
        try {
            // Sauvegarder les paramètres
            localStorage.setItem('messageSettings', JSON.stringify(settings));
            
            // Appliquer les paramètres
            if (settings.notifications.enabled && settings.notifications.desktop) {
                await Notification.requestPermission();
            }
            
            onClose();
        } catch (error) {
            console.error('Erreur lors de la sauvegarde:', error);
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
                {/* Header */}
                <div className="flex items-center justify-between p-6 border-b border-gray-200">
                    <h2 className="text-lg font-semibold text-gray-900 flex items-center">
                        <Settings className="w-5 h-5 mr-2 text-orange-500" />
                        Paramètres de messagerie
                    </h2>
                    <button
                        onClick={onClose}
                        className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        ×
                    </button>
                </div>

                {/* Contenu */}
                <div className="overflow-y-auto max-h-[calc(90vh-140px)]">
                    {/* Notifications */}
                    <div className="p-6 border-b border-gray-200">
                        <h3 className="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <Bell className="w-5 h-5 mr-2 text-blue-500" />
                            Notifications
                        </h3>
                        
                        <div className="space-y-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <label className="text-sm font-medium text-gray-700">Activer les notifications</label>
                                    <p className="text-xs text-gray-500">Recevoir des notifications pour les nouveaux messages</p>
                                </div>
                                <input
                                    type="checkbox"
                                    checked={settings.notifications.enabled}
                                    onChange={(e) => updateSetting('notifications', 'enabled', e.target.checked)}
                                    className="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500"
                                />
                            </div>

                            <div className="flex items-center justify-between">
                                <div>
                                    <label className="text-sm font-medium text-gray-700">Son des notifications</label>
                                    <p className="text-xs text-gray-500">Jouer un son lors de la réception de messages</p>
                                </div>
                                <input
                                    type="checkbox"
                                    checked={settings.notifications.sound}
                                    onChange={(e) => updateSetting('notifications', 'sound', e.target.checked)}
                                    disabled={!settings.notifications.enabled}
                                    className="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500 disabled:opacity-50"
                                />
                            </div>

                            <div className="flex items-center justify-between">
                                <div>
                                    <label className="text-sm font-medium text-gray-700">Notifications bureau</label>
                                    <p className="text-xs text-gray-500">Afficher les notifications sur le bureau</p>
                                </div>
                                <input
                                    type="checkbox"
                                    checked={settings.notifications.desktop}
                                    onChange={(e) => updateSetting('notifications', 'desktop', e.target.checked)}
                                    disabled={!settings.notifications.enabled}
                                    className="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500 disabled:opacity-50"
                                />
                            </div>

                            <div className="flex items-center justify-between">
                                <div>
                                    <label className="text-sm font-medium text-gray-700">Aperçu du message</label>
                                    <p className="text-xs text-gray-500">Afficher le contenu du message dans la notification</p>
                                </div>
                                <input
                                    type="checkbox"
                                    checked={settings.notifications.preview}
                                    onChange={(e) => updateSetting('notifications', 'preview', e.target.checked)}
                                    disabled={!settings.notifications.enabled}
                                    className="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500 disabled:opacity-50"
                                />
                            </div>
                        </div>
                    </div>

                    {/* Apparence */}
                    <div className="p-6 border-b border-gray-200">
                        <h3 className="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <Palette className="w-5 h-5 mr-2 text-purple-500" />
                            Apparence
                        </h3>
                        
                        <div className="space-y-4">
                            <div>
                                <label className="text-sm font-medium text-gray-700 block mb-2">Thème</label>
                                <select
                                    value={settings.appearance.theme}
                                    onChange={(e) => updateSetting('appearance', 'theme', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                >
                                    <option value="light">Clair</option>
                                    <option value="dark">Sombre</option>
                                    <option value="auto">Automatique</option>
                                </select>
                            </div>

                            <div>
                                <label className="text-sm font-medium text-gray-700 block mb-2">Taille de police</label>
                                <select
                                    value={settings.appearance.fontSize}
                                    onChange={(e) => updateSetting('appearance', 'fontSize', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                >
                                    <option value="small">Petite</option>
                                    <option value="medium">Moyenne</option>
                                    <option value="large">Grande</option>
                                </select>
                            </div>

                            <div>
                                <label className="text-sm font-medium text-gray-700 block mb-2">Style des bulles</label>
                                <select
                                    value={settings.appearance.bubbleStyle}
                                    onChange={(e) => updateSetting('appearance', 'bubbleStyle', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                >
                                    <option value="rounded">Arrondies</option>
                                    <option value="square">Carrées</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {/* Confidentialité */}
                    <div className="p-6 border-b border-gray-200">
                        <h3 className="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <Moon className="w-5 h-5 mr-2 text-indigo-500" />
                            Confidentialité
                        </h3>
                        
                        <div className="space-y-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <label className="text-sm font-medium text-gray-700">Accusés de lecture</label>
                                    <p className="text-xs text-gray-500">Permettre aux autres de voir quand vous avez lu leurs messages</p>
                                </div>
                                <input
                                    type="checkbox"
                                    checked={settings.privacy.readReceipts}
                                    onChange={(e) => updateSetting('privacy', 'readReceipts', e.target.checked)}
                                    className="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500"
                                />
                            </div>

                            <div className="flex items-center justify-between">
                                <div>
                                    <label className="text-sm font-medium text-gray-700">Statut en ligne</label>
                                    <p className="text-xs text-gray-500">Afficher votre statut en ligne aux autres</p>
                                </div>
                                <input
                                    type="checkbox"
                                    checked={settings.privacy.onlineStatus}
                                    onChange={(e) => updateSetting('privacy', 'onlineStatus', e.target.checked)}
                                    className="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500"
                                />
                            </div>

                            <div className="flex items-center justify-between">
                                <div>
                                    <label className="text-sm font-medium text-gray-700">Indicateur de frappe</label>
                                    <p className="text-xs text-gray-500">Montrer quand vous êtes en train d'écrire</p>
                                </div>
                                <input
                                    type="checkbox"
                                    checked={settings.privacy.typing}
                                    onChange={(e) => updateSetting('privacy', 'typing', e.target.checked)}
                                    className="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500"
                                />
                            </div>
                        </div>
                    </div>

                    {/* Stockage */}
                    <div className="p-6">
                        <h3 className="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <Download className="w-5 h-5 mr-2 text-green-500" />
                            Stockage
                        </h3>
                        
                        <div className="space-y-4">
                            <div className="flex items-center justify-between">
                                <div>
                                    <label className="text-sm font-medium text-gray-700">Téléchargement automatique</label>
                                    <p className="text-xs text-gray-500">Télécharger automatiquement les fichiers reçus</p>
                                </div>
                                <input
                                    type="checkbox"
                                    checked={settings.storage.autoDownload}
                                    onChange={(e) => updateSetting('storage', 'autoDownload', e.target.checked)}
                                    className="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500"
                                />
                            </div>

                            <div>
                                <label className="text-sm font-medium text-gray-700 block mb-2">
                                    Taille max des fichiers (MB)
                                </label>
                                <input
                                    type="number"
                                    min="1"
                                    max="100"
                                    value={settings.storage.maxFileSize}
                                    onChange={(e) => updateSetting('storage', 'maxFileSize', parseInt(e.target.value))}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                {/* Footer */}
                <div className="flex items-center justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50">
                    <button
                        onClick={onClose}
                        className="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Annuler
                    </button>
                    <button
                        onClick={saveSettings}
                        className="px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 transition-all duration-200 transform hover:scale-105"
                    >
                        Sauvegarder
                    </button>
                </div>
            </div>
        </div>
    );
}
