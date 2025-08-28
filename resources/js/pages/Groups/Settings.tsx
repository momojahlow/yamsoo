import React, { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { KwdDashboardLayout } from '@/Layouts/modern';
import { Settings, ArrowLeft, Save, Trash2, Users, Shield, Bell, Eye, EyeOff } from 'lucide-react';

interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    role: string;
    status: string;
    joined_at: string;
    notifications_enabled: boolean;
}

interface Group {
    id: number;
    name: string;
    description?: string;
    type: string;
    visibility: string;
    max_participants: number;
    created_at: string;
    updated_at: string;
    participants_count: number;
    user_role: string;
    can_manage: boolean;
    can_delete: boolean;
}

interface Props {
    group: Group;
    participants: User[];
}

export default function GroupSettings({ group, participants }: Props) {
    const [activeTab, setActiveTab] = useState<'general' | 'members' | 'permissions'>('general');
    const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);

    const { data, setData, patch, processing, errors } = useForm({
        name: group.name,
        description: group.description || '',
        visibility: group.visibility,
        max_participants: group.max_participants
    });

    const handleSave = () => {
        patch(`/groups/${group.id}`, {
            onSuccess: () => {
                alert('Paramètres mis à jour avec succès !');
            },
            onError: (errors) => {
                console.error('Erreur:', errors);
                alert('Erreur lors de la mise à jour des paramètres.');
            }
        });
    };

    const handleDeleteGroup = () => {
        if (!showDeleteConfirm) {
            setShowDeleteConfirm(true);
            return;
        }

        router.delete(`/groups/${group.id}`, {
            onSuccess: () => {
                router.get('/groups');
            },
            onError: (errors) => {
                console.error('Erreur:', errors);
                alert('Erreur lors de la suppression du groupe.');
            }
        });
    };

    const getRoleColor = (role: string) => {
        switch (role) {
            case 'owner': return 'bg-purple-100 text-purple-800';
            case 'admin': return 'bg-blue-100 text-blue-800';
            case 'moderator': return 'bg-green-100 text-green-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const getRoleLabel = (role: string) => {
        switch (role) {
            case 'owner': return '👑 Propriétaire';
            case 'admin': return '🛡️ Administrateur';
            case 'moderator': return '⚖️ Modérateur';
            default: return '👤 Membre';
        }
    };

    const getInitials = (name: string) => {
        return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
    };

    return (
        <KwdDashboardLayout>
            <Head title={`Paramètres - ${group.name}`} />

            <div className="max-w-6xl mx-auto">
                {/* Header */}
                <div className="mb-6">
                    <div className="flex items-center space-x-4 mb-4">
                        <button
                            onClick={() => router.get('/groups')}
                            className="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            <ArrowLeft className="w-5 h-5" />
                        </button>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900 flex items-center">
                                <Settings className="w-6 h-6 mr-2" />
                                Paramètres du groupe
                            </h1>
                            <p className="text-gray-600">{group.name} • {group.participants_count} membres</p>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    {/* Navigation */}
                    <div className="lg:col-span-1">
                        <nav className="space-y-1">
                            <button
                                onClick={() => setActiveTab('general')}
                                className={`w-full text-left px-3 py-2 rounded-lg font-medium transition-colors ${
                                    activeTab === 'general'
                                        ? 'bg-orange-100 text-orange-700'
                                        : 'text-gray-600 hover:bg-gray-100'
                                }`}
                            >
                                ⚙️ Général
                            </button>
                            <button
                                onClick={() => setActiveTab('members')}
                                className={`w-full text-left px-3 py-2 rounded-lg font-medium transition-colors ${
                                    activeTab === 'members'
                                        ? 'bg-orange-100 text-orange-700'
                                        : 'text-gray-600 hover:bg-gray-100'
                                }`}
                            >
                                👥 Membres ({participants.length})
                            </button>
                            <button
                                onClick={() => setActiveTab('permissions')}
                                className={`w-full text-left px-3 py-2 rounded-lg font-medium transition-colors ${
                                    activeTab === 'permissions'
                                        ? 'bg-orange-100 text-orange-700'
                                        : 'text-gray-600 hover:bg-gray-100'
                                }`}
                            >
                                🔒 Permissions
                            </button>
                        </nav>
                    </div>

                    {/* Contenu */}
                    <div className="lg:col-span-3">
                        {activeTab === 'general' && (
                            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h2 className="text-lg font-semibold text-gray-900 mb-4">Informations générales</h2>
                                
                                <div className="space-y-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Nom du groupe
                                        </label>
                                        <input
                                            type="text"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                            disabled={!group.can_manage}
                                        />
                                        {errors.name && <p className="text-red-600 text-sm mt-1">{errors.name}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Description
                                        </label>
                                        <textarea
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            rows={3}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                            placeholder="Description du groupe..."
                                            disabled={!group.can_manage}
                                        />
                                        {errors.description && <p className="text-red-600 text-sm mt-1">{errors.description}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Visibilité
                                        </label>
                                        <select
                                            value={data.visibility}
                                            onChange={(e) => setData('visibility', e.target.value)}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                            disabled={!group.can_manage}
                                        >
                                            <option value="private">🔒 Privé</option>
                                            <option value="public">🌍 Public</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            Nombre maximum de participants
                                        </label>
                                        <input
                                            type="number"
                                            min="2"
                                            max="1000"
                                            value={data.max_participants}
                                            onChange={(e) => setData('max_participants', parseInt(e.target.value))}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                            disabled={!group.can_manage}
                                        />
                                    </div>

                                    {group.can_manage && (
                                        <div className="flex items-center space-x-3 pt-4">
                                            <button
                                                onClick={handleSave}
                                                disabled={processing}
                                                className="px-4 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 disabled:opacity-50 transition-colors"
                                            >
                                                <Save className="w-4 h-4 mr-2 inline" />
                                                {processing ? 'Enregistrement...' : 'Enregistrer'}
                                            </button>

                                            {group.can_delete && (
                                                <button
                                                    onClick={handleDeleteGroup}
                                                    className={`px-4 py-2 font-medium rounded-lg transition-colors ${
                                                        showDeleteConfirm
                                                            ? 'bg-red-500 text-white hover:bg-red-600'
                                                            : 'bg-red-100 text-red-700 hover:bg-red-200'
                                                    }`}
                                                >
                                                    <Trash2 className="w-4 h-4 mr-2 inline" />
                                                    {showDeleteConfirm ? 'Confirmer la suppression' : 'Supprimer le groupe'}
                                                </button>
                                            )}
                                        </div>
                                    )}
                                </div>
                            </div>
                        )}

                        {activeTab === 'members' && (
                            <div className="bg-white rounded-lg shadow-sm border border-gray-200">
                                <div className="p-6 border-b border-gray-200">
                                    <div className="flex items-center justify-between">
                                        <h2 className="text-lg font-semibold text-gray-900">
                                            Membres du groupe ({participants.length})
                                        </h2>
                                        {group.can_manage && (
                                            <button
                                                onClick={() => router.get(`/groups/${group.id}/invite`)}
                                                className="px-4 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 transition-colors"
                                            >
                                                <Users className="w-4 h-4 mr-2 inline" />
                                                Inviter
                                            </button>
                                        )}
                                    </div>
                                </div>

                                <div className="divide-y divide-gray-200">
                                    {participants.map((participant) => (
                                        <div key={participant.id} className="p-4">
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center space-x-3">
                                                    {participant.avatar ? (
                                                        <img
                                                            src={participant.avatar}
                                                            alt={participant.name}
                                                            className="w-10 h-10 rounded-full object-cover"
                                                        />
                                                    ) : (
                                                        <div className="w-10 h-10 rounded-full bg-gradient-to-br from-orange-100 to-red-100 flex items-center justify-center text-orange-600 font-medium">
                                                            {getInitials(participant.name)}
                                                        </div>
                                                    )}
                                                    <div>
                                                        <h4 className="font-medium text-gray-900">{participant.name}</h4>
                                                        <p className="text-sm text-gray-500">{participant.email}</p>
                                                        <p className="text-xs text-gray-400">
                                                            Rejoint le {new Date(participant.joined_at).toLocaleDateString()}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="flex items-center space-x-2">
                                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${getRoleColor(participant.role)}`}>
                                                        {getRoleLabel(participant.role)}
                                                    </span>
                                                    
                                                    {participant.notifications_enabled ? (
                                                        <Bell className="w-4 h-4 text-green-500" title="Notifications activées" />
                                                    ) : (
                                                        <Bell className="w-4 h-4 text-gray-400" title="Notifications désactivées" />
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        {activeTab === 'permissions' && (
                            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h2 className="text-lg font-semibold text-gray-900 mb-4">Permissions et sécurité</h2>
                                
                                <div className="space-y-4">
                                    <div className="p-4 bg-blue-50 rounded-lg">
                                        <h3 className="font-medium text-blue-900 mb-2">🔒 Votre rôle</h3>
                                        <p className="text-blue-700">
                                            Vous êtes <strong>{getRoleLabel(group.user_role)}</strong> de ce groupe.
                                        </p>
                                    </div>

                                    <div className="space-y-3">
                                        <h3 className="font-medium text-gray-900">Permissions par rôle</h3>
                                        
                                        <div className="space-y-2 text-sm">
                                            <div className="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                                                <span className="font-medium text-purple-900">👑 Propriétaire</span>
                                                <span className="text-purple-700">Tous les droits</span>
                                            </div>
                                            <div className="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                                <span className="font-medium text-blue-900">🛡️ Administrateur</span>
                                                <span className="text-blue-700">Gérer membres, paramètres</span>
                                            </div>
                                            <div className="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                                <span className="font-medium text-green-900">⚖️ Modérateur</span>
                                                <span className="text-green-700">Modérer messages</span>
                                            </div>
                                            <div className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <span className="font-medium text-gray-900">👤 Membre</span>
                                                <span className="text-gray-700">Participer aux discussions</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </KwdDashboardLayout>
    );
}
