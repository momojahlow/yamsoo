import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { KwdDashboardLayout } from '@/Layouts/modern';
import { Users, Plus, Settings, Trash2, Edit3, UserPlus, UserMinus, Crown, Shield, MoreVertical } from 'lucide-react';

interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
}

interface GroupParticipant extends User {
    pivot: {
        role: 'member' | 'admin' | 'owner';
        status: 'active' | 'invited' | 'pending' | 'banned';
        nickname?: string;
        joined_at: string;
        notifications_enabled: boolean;
    };
}

interface Group {
    id: number;
    name: string;
    description?: string;
    avatar?: string;
    type: 'group';
    visibility: 'public' | 'private' | 'invite_only';
    max_participants: number;
    participants_count: number;
    last_activity_at?: string;
    participants: GroupParticipant[];
    can_manage: boolean;
    user_role: 'member' | 'admin' | 'owner';
}

interface Props {
    groups: Group[];
    auth: {
        user: User;
    };
}

export default function GroupsIndex({ groups, auth }: Props) {
    const [selectedGroup, setSelectedGroup] = useState<Group | null>(null);
    const [showManageModal, setShowManageModal] = useState(false);
    const [editingName, setEditingName] = useState(false);
    const [newGroupName, setNewGroupName] = useState('');

    const getInitials = (name: string) => {
        return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
    };

    const getRoleIcon = (role: string) => {
        switch (role) {
            case 'owner': return <Crown className="w-4 h-4 text-yellow-500" />;
            case 'admin': return <Shield className="w-4 h-4 text-blue-500" />;
            default: return null;
        }
    };

    const getRoleLabel = (role: string) => {
        switch (role) {
            case 'owner': return 'Propriétaire';
            case 'admin': return 'Administrateur';
            case 'member': return 'Membre';
            default: return 'Membre';
        }
    };

    const getRoleBadgeColor = (role: string) => {
        switch (role) {
            case 'owner': return 'bg-yellow-100 text-yellow-800';
            case 'admin': return 'bg-blue-100 text-blue-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const handleDeleteGroup = (group: Group) => {
        if (confirm(`Êtes-vous sûr de vouloir supprimer le groupe "${group.name}" ? Cette action est irréversible.`)) {
            router.delete(`/groups/${group.id}`, {
                onSuccess: () => {
                    setSelectedGroup(null);
                    setShowManageModal(false);
                }
            });
        }
    };

    const handleLeaveGroup = (group: Group) => {
        if (group.user_role === 'owner') {
            alert('En tant que propriétaire, vous ne pouvez pas quitter le groupe. Vous devez le supprimer ou transférer la propriété à un autre membre.');
            return;
        }

        if (confirm(`Êtes-vous sûr de vouloir quitter le groupe "${group.name}" ?`)) {
            router.post(`/groups/${group.id}/leave-group`, {}, {
                onSuccess: () => {
                    setSelectedGroup(null);
                    setShowManageModal(false);
                }
            });
        }
    };

    const handleEditName = (group: Group) => {
        if (newGroupName.trim() && newGroupName !== group.name) {
            router.patch(`/groups/${group.id}`, {
                name: newGroupName.trim()
            }, {
                onSuccess: () => {
                    setEditingName(false);
                    setNewGroupName('');
                }
            });
        } else {
            setEditingName(false);
            setNewGroupName('');
        }
    };

    const handleRemoveParticipant = (group: Group, participant: GroupParticipant) => {
        if (confirm(`Retirer ${participant.name} du groupe "${group.name}" ?`)) {
            router.delete(`/groups/${group.id}/participants/${participant.id}`, {
                onSuccess: () => {
                    // Refresh group data
                    router.reload({ only: ['groups'] });
                }
            });
        }
    };

    const handlePromoteParticipant = (group: Group, participant: GroupParticipant) => {
        const newRole = participant.pivot.role === 'member' ? 'admin' : 'member';
        const action = newRole === 'admin' ? 'promouvoir' : 'rétrograder';

        if (confirm(`${action} ${participant.name} ${newRole === 'admin' ? 'administrateur' : 'membre'} ?`)) {
            router.patch(`/groups/${group.id}/participants/${participant.id}`, {
                role: newRole
            }, {
                onSuccess: () => {
                    router.reload({ only: ['groups'] });
                }
            });
        }
    };

    return (
        <KwdDashboardLayout>
            <Head title="Groupes" />

            {/* Header avec titre et bouton de création */}
            <div className="mb-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">👥 Mes Groupes</h1>
                        <p className="text-gray-600 mt-1">Gérez vos groupes de discussion</p>
                    </div>
                    <button
                        onClick={() => router.get('/groups/create')}
                        className="inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white text-sm font-medium rounded-lg hover:from-orange-600 hover:to-red-600 transition-all duration-200 transform hover:scale-105 shadow-lg"
                    >
                        <Plus className="w-4 h-4 mr-2" />
                        Créer un groupe
                    </button>
                </div>
            </div>

            {groups.length === 0 ? (
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-12 text-center">
                                <div className="w-20 h-20 bg-gradient-to-br from-orange-100 to-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <Users className="w-10 h-10 text-orange-500" />
                                </div>
                                <h3 className="text-lg font-medium text-gray-900 mb-2">Aucun groupe</h3>
                                <p className="text-gray-500 mb-6">Vous ne participez à aucun groupe pour le moment.</p>
                                <button
                                    onClick={() => router.get('/groups/create')}
                                    className="inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white text-sm font-medium rounded-lg hover:from-orange-600 hover:to-red-600 transition-all duration-200"
                                >
                                    <Plus className="w-4 h-4 mr-2" />
                                    Créer votre premier groupe
                                </button>
                            </div>
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {groups.map((group) => (
                                <div key={group.id} className="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
                                    {/* Header du groupe */}
                                    <div className="p-6 border-b border-gray-100">
                                        <div className="flex items-start justify-between">
                                            <div className="flex items-center space-x-3">
                                                {group.avatar ? (
                                                    <img
                                                        src={group.avatar}
                                                        alt={group.name}
                                                        className="w-12 h-12 rounded-full object-cover border-2 border-orange-200"
                                                    />
                                                ) : (
                                                    <div className="w-12 h-12 rounded-full bg-gradient-to-br from-orange-200 to-red-200 flex items-center justify-center border-2 border-orange-200">
                                                        <Users className="w-6 h-6 text-orange-600" />
                                                    </div>
                                                )}
                                                <div>
                                                    <h3 className="font-semibold text-gray-900 text-lg">
                                                        {group.name} ({group.participants_count})
                                                    </h3>
                                                    <div className="flex items-center space-x-2 text-sm">
                                                        <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getRoleBadgeColor(group.user_role)}`}>
                                                            {getRoleIcon(group.user_role)}
                                                            <span className="ml-1">{getRoleLabel(group.user_role)}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            {group.can_manage && (
                                                <button
                                                    onClick={() => {
                                                        setSelectedGroup(group);
                                                        setShowManageModal(true);
                                                        setNewGroupName(group.name);
                                                    }}
                                                    className="p-2 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors"
                                                    title="Gérer le groupe"
                                                >
                                                    <MoreVertical className="w-5 h-5" />
                                                </button>
                                            )}
                                        </div>

                                        {group.description && (
                                            <p className="mt-3 text-gray-600 text-sm">{group.description}</p>
                                        )}
                                    </div>

                                    {/* Actions */}
                                    <div className="p-4 bg-gray-50">
                                        <div className="flex items-center space-x-2">
                                            <button
                                                onClick={() => router.get(`/messagerie?conversation=${group.id}`)}
                                                className="flex-1 px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition-colors"
                                            >
                                                💬 Ouvrir
                                            </button>

                                            {group.can_manage && (
                                                <button
                                                    onClick={() => {
                                                        setSelectedGroup(group);
                                                        setShowManageModal(true);
                                                    }}
                                                    className="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition-colors"
                                                >
                                                    ⚙️ Gérer
                                                </button>
                                            )}

                                            {/* Bouton Quitter (sauf pour le propriétaire) */}
                                            {group.user_role !== 'owner' && (
                                                <button
                                                    onClick={() => handleLeaveGroup(group)}
                                                    className="px-4 py-2 bg-red-100 text-red-700 text-sm font-medium rounded-lg hover:bg-red-200 transition-colors"
                                                    title="Quitter le groupe"
                                                >
                                                    🚪 Quitter
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}

            {/* Modal de gestion du groupe */}
            {showManageModal && selectedGroup && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        {/* Header du modal */}
                        <div className="p-6 border-b border-gray-200">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center space-x-3">
                                    {selectedGroup.avatar ? (
                                        <img
                                            src={selectedGroup.avatar}
                                            alt={selectedGroup.name}
                                            className="w-10 h-10 rounded-full object-cover"
                                        />
                                    ) : (
                                        <div className="w-10 h-10 rounded-full bg-gradient-to-br from-orange-200 to-red-200 flex items-center justify-center">
                                            <Users className="w-5 h-5 text-orange-600" />
                                        </div>
                                    )}
                                    <div>
                                        {editingName ? (
                                            <div className="flex items-center space-x-2">
                                                <input
                                                    type="text"
                                                    value={newGroupName}
                                                    onChange={(e) => setNewGroupName(e.target.value)}
                                                    onKeyDown={(e) => {
                                                        if (e.key === 'Enter') handleEditName(selectedGroup);
                                                        if (e.key === 'Escape') {
                                                            setEditingName(false);
                                                            setNewGroupName(selectedGroup.name);
                                                        }
                                                    }}
                                                    className="text-lg font-semibold bg-gray-100 border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-orange-500"
                                                    autoFocus
                                                />
                                                <button
                                                    onClick={() => handleEditName(selectedGroup)}
                                                    className="text-green-600 hover:text-green-700"
                                                >
                                                    ✓
                                                </button>
                                                <button
                                                    onClick={() => {
                                                        setEditingName(false);
                                                        setNewGroupName(selectedGroup.name);
                                                    }}
                                                    className="text-red-600 hover:text-red-700"
                                                >
                                                    ✕
                                                </button>
                                            </div>
                                        ) : (
                                            <div className="flex items-center space-x-2">
                                                <h2 className="text-lg font-semibold text-gray-900">
                                                    {selectedGroup.name} ({selectedGroup.participants_count})
                                                </h2>
                                                {selectedGroup.user_role === 'owner' && (
                                                    <button
                                                        onClick={() => {
                                                            setEditingName(true);
                                                            setNewGroupName(selectedGroup.name);
                                                        }}
                                                        className="text-gray-400 hover:text-orange-600"
                                                        title="Modifier le nom"
                                                    >
                                                        <Edit3 className="w-4 h-4" />
                                                    </button>
                                                )}
                                            </div>
                                        )}
                                    </div>
                                </div>
                                <button
                                    onClick={() => {
                                        setShowManageModal(false);
                                        setSelectedGroup(null);
                                        setEditingName(false);
                                    }}
                                    className="text-gray-400 hover:text-gray-600"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>

                        {/* Contenu du modal */}
                        <div className="p-6">
                            {/* Description */}
                            {selectedGroup.description && (
                                <div className="mb-6">
                                    <h3 className="text-sm font-medium text-gray-700 mb-2">Description</h3>
                                    <p className="text-gray-600 bg-gray-50 rounded-lg p-3">{selectedGroup.description}</p>
                                </div>
                            )}

                            {/* Liste des participants */}
                            <div className="mb-6">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="text-sm font-medium text-gray-700">
                                        Membres
                                    </h3>
                                    {selectedGroup.can_manage && (
                                        <button
                                            onClick={() => router.get(`/groups/${selectedGroup.id}/invite`)}
                                            className="inline-flex items-center px-3 py-1.5 bg-orange-500 text-white text-xs font-medium rounded-md hover:bg-orange-600 transition-colors"
                                        >
                                            <UserPlus className="w-3 h-3 mr-1" />
                                            Inviter
                                        </button>
                                    )}
                                </div>

                                <div className="space-y-2 max-h-60 overflow-y-auto">
                                    {selectedGroup.participants.map((participant) => (
                                        <div key={participant.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div className="flex items-center space-x-3">
                                                {participant.avatar ? (
                                                    <img
                                                        src={participant.avatar}
                                                        alt={participant.name}
                                                        className="w-8 h-8 rounded-full object-cover"
                                                    />
                                                ) : (
                                                    <div className="w-8 h-8 rounded-full bg-gradient-to-br from-orange-100 to-red-100 flex items-center justify-center text-orange-600 font-medium text-xs">
                                                        {getInitials(participant.name)}
                                                    </div>
                                                )}
                                                <div>
                                                    <div className="flex items-center space-x-2">
                                                        <span className="font-medium text-gray-900">
                                                            {participant.pivot.nickname || participant.name}
                                                        </span>
                                                        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${getRoleBadgeColor(participant.pivot.role)}`}>
                                                            {getRoleIcon(participant.pivot.role)}
                                                            <span className="ml-1">{getRoleLabel(participant.pivot.role)}</span>
                                                        </span>
                                                    </div>
                                                    {participant.pivot.nickname && (
                                                        <span className="text-xs text-gray-500">({participant.name})</span>
                                                    )}
                                                </div>
                                            </div>

                                            {selectedGroup.can_manage && participant.id !== auth.user.id && participant.pivot.role !== 'owner' && (
                                                <div className="flex items-center space-x-1">
                                                    {participant.pivot.role === 'member' && (
                                                        <button
                                                            onClick={() => handlePromoteParticipant(selectedGroup, participant)}
                                                            className="p-1.5 text-blue-500 hover:text-blue-600 hover:bg-blue-50 rounded"
                                                            title="Promouvoir administrateur"
                                                        >
                                                            <Shield className="w-4 h-4" />
                                                        </button>
                                                    )}
                                                    {participant.pivot.role === 'admin' && selectedGroup.user_role === 'owner' && (
                                                        <button
                                                            onClick={() => handlePromoteParticipant(selectedGroup, participant)}
                                                            className="p-1.5 text-gray-500 hover:text-gray-600 hover:bg-gray-100 rounded"
                                                            title="Rétrograder membre"
                                                        >
                                                            <UserMinus className="w-4 h-4" />
                                                        </button>
                                                    )}
                                                    <button
                                                        onClick={() => handleRemoveParticipant(selectedGroup, participant)}
                                                        className="p-1.5 text-red-500 hover:text-red-600 hover:bg-red-50 rounded"
                                                        title="Retirer du groupe"
                                                    >
                                                        <Trash2 className="w-4 h-4" />
                                                    </button>
                                                </div>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* Actions du groupe */}
                            <div className="pt-6 border-t border-gray-200">
                                {selectedGroup.user_role === 'owner' ? (
                                    <>
                                        <h3 className="text-sm font-medium text-gray-700 mb-4">Actions du propriétaire</h3>
                                        <div className="flex space-x-3">
                                            <button
                                                onClick={() => router.get(`/groups/${selectedGroup.id}/settings`)}
                                                className="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors"
                                            >
                                                <Settings className="w-4 h-4 mr-2" />
                                                Paramètres
                                            </button>
                                            <button
                                                onClick={() => handleDeleteGroup(selectedGroup)}
                                                className="flex-1 inline-flex items-center justify-center px-4 py-2 bg-red-100 text-red-700 text-sm font-medium rounded-lg hover:bg-red-200 transition-colors"
                                            >
                                                <Trash2 className="w-4 h-4 mr-2" />
                                                Supprimer le groupe
                                            </button>
                                        </div>
                                        <div className="mt-3">
                                            <p className="text-xs text-gray-500">
                                                💡 En tant que propriétaire, vous ne pouvez pas quitter le groupe. Vous devez le supprimer ou transférer la propriété.
                                            </p>
                                        </div>
                                    </>
                                ) : (
                                    <>
                                        <h3 className="text-sm font-medium text-gray-700 mb-4">Actions</h3>
                                        <button
                                            onClick={() => handleLeaveGroup(selectedGroup)}
                                            className="w-full inline-flex items-center justify-center px-4 py-2 bg-red-100 text-red-700 text-sm font-medium rounded-lg hover:bg-red-200 transition-colors"
                                        >
                                            <Trash2 className="w-4 h-4 mr-2" />
                                            Quitter le groupe
                                        </button>
                                        <p className="text-xs text-gray-500 mt-2">
                                            ⚠️ Vous ne pourrez plus voir les messages de ce groupe après l'avoir quitté.
                                        </p>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </KwdDashboardLayout>
    );
}
