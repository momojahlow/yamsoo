import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import { 
    Settings, 
    UserPlus, 
    UserMinus, 
    Crown, 
    Shield, 
    Trash2, 
    LogOut,
    MoreVertical,
    Users
} from 'lucide-react';

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

interface Conversation {
    id: number;
    name: string;
    description?: string;
    type: 'group';
    participants: GroupParticipant[];
    user_role: 'member' | 'admin' | 'owner';
    can_manage: boolean;
}

interface GroupActionsProps {
    conversation: Conversation;
    currentUser: User;
    onClose?: () => void;
}

export default function GroupActions({ conversation, currentUser, onClose }: GroupActionsProps) {
    const [showConfirmDialog, setShowConfirmDialog] = useState<string | null>(null);
    const [selectedParticipant, setSelectedParticipant] = useState<GroupParticipant | null>(null);

    const handleLeaveGroup = () => {
        if (conversation.user_role === 'owner') {
            alert('En tant que propriétaire, vous ne pouvez pas quitter le groupe. Vous devez le supprimer ou transférer la propriété.');
            return;
        }

        router.post(`/groups/${conversation.id}/leave-group`, {}, {
            onSuccess: () => {
                onClose?.();
            }
        });
    };

    const handleDeleteGroup = () => {
        router.delete(`/groups/${conversation.id}`, {
            onSuccess: () => {
                onClose?.();
            }
        });
    };

    const handleRemoveParticipant = (participant: GroupParticipant) => {
        router.delete(`/groups/${conversation.id}/participants/${participant.id}`, {
            onSuccess: () => {
                setSelectedParticipant(null);
                setShowConfirmDialog(null);
            }
        });
    };

    const handlePromoteParticipant = (participant: GroupParticipant) => {
        const newRole = participant.pivot.role === 'member' ? 'admin' : 'member';
        
        router.patch(`/groups/${conversation.id}/participants/${participant.id}`, {
            role: newRole
        }, {
            onSuccess: () => {
                setSelectedParticipant(null);
                setShowConfirmDialog(null);
            }
        });
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

    const getInitials = (name: string) => {
        return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
    };

    return (
        <div className="bg-white rounded-lg shadow-lg border border-gray-200 p-4 max-w-md">
            {/* Header */}
            <div className="flex items-center justify-between mb-4">
                <div className="flex items-center space-x-3">
                    <div className="w-10 h-10 rounded-full bg-gradient-to-br from-orange-200 to-red-200 flex items-center justify-center">
                        <Users className="w-5 h-5 text-orange-600" />
                    </div>
                    <div>
                        <h3 className="font-semibold text-gray-900">{conversation.name}</h3>
                        <p className="text-sm text-gray-500">{conversation.participants.length} membres</p>
                    </div>
                </div>
                {onClose && (
                    <button
                        onClick={onClose}
                        className="text-gray-400 hover:text-gray-600"
                    >
                        ✕
                    </button>
                )}
            </div>

            {/* Liste des participants */}
            <div className="mb-4">
                <h4 className="text-sm font-medium text-gray-700 mb-2">Membres</h4>
                <div className="space-y-2 max-h-40 overflow-y-auto">
                    {conversation.participants.map((participant) => (
                        <div key={participant.id} className="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
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
                                        <span className="font-medium text-gray-900 text-sm">
                                            {participant.pivot.nickname || participant.name}
                                        </span>
                                        {getRoleIcon(participant.pivot.role)}
                                    </div>
                                    <span className="text-xs text-gray-500">{getRoleLabel(participant.pivot.role)}</span>
                                </div>
                            </div>
                            
                            {/* Actions sur les participants */}
                            {conversation.can_manage && participant.id !== currentUser.id && participant.pivot.role !== 'owner' && (
                                <div className="flex items-center space-x-1">
                                    {/* Promouvoir/Rétrograder */}
                                    {conversation.user_role === 'owner' && (
                                        <button
                                            onClick={() => {
                                                setSelectedParticipant(participant);
                                                setShowConfirmDialog('promote');
                                            }}
                                            className="p-1.5 text-blue-500 hover:text-blue-600 hover:bg-blue-50 rounded"
                                            title={participant.pivot.role === 'member' ? 'Promouvoir administrateur' : 'Rétrograder membre'}
                                        >
                                            {participant.pivot.role === 'member' ? (
                                                <Shield className="w-4 h-4" />
                                            ) : (
                                                <UserMinus className="w-4 h-4" />
                                            )}
                                        </button>
                                    )}
                                    
                                    {/* Retirer du groupe */}
                                    <button
                                        onClick={() => {
                                            setSelectedParticipant(participant);
                                            setShowConfirmDialog('remove');
                                        }}
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

            {/* Actions principales */}
            <div className="space-y-2">
                {/* Actions pour tous les membres */}
                <button
                    onClick={() => router.get(`/messagerie?selectedGroupId=${conversation.id}`)}
                    className="w-full flex items-center justify-center px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition-colors"
                >
                    💬 Ouvrir la conversation
                </button>

                {/* Actions pour les admins/propriétaires */}
                {conversation.can_manage && (
                    <button
                        onClick={() => router.get(`/groups/${conversation.id}/invite`)}
                        className="w-full flex items-center justify-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors"
                    >
                        <UserPlus className="w-4 h-4 mr-2" />
                        Inviter des membres
                    </button>
                )}

                {/* Actions selon le rôle */}
                {conversation.user_role === 'owner' ? (
                    <button
                        onClick={() => setShowConfirmDialog('delete')}
                        className="w-full flex items-center justify-center px-4 py-2 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition-colors"
                    >
                        <Trash2 className="w-4 h-4 mr-2" />
                        Supprimer le groupe
                    </button>
                ) : (
                    <button
                        onClick={() => setShowConfirmDialog('leave')}
                        className="w-full flex items-center justify-center px-4 py-2 bg-red-100 text-red-700 text-sm font-medium rounded-lg hover:bg-red-200 transition-colors"
                    >
                        <LogOut className="w-4 h-4 mr-2" />
                        Quitter le groupe
                    </button>
                )}
            </div>

            {/* Dialogs de confirmation */}
            {showConfirmDialog && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg p-6 max-w-sm mx-4">
                        {showConfirmDialog === 'leave' && (
                            <>
                                <h3 className="text-lg font-semibold mb-2">Quitter le groupe</h3>
                                <p className="text-gray-600 mb-4">
                                    Êtes-vous sûr de vouloir quitter "{conversation.name}" ? 
                                    Vous ne pourrez plus voir les messages de ce groupe.
                                </p>
                                <div className="flex space-x-3">
                                    <button
                                        onClick={() => setShowConfirmDialog(null)}
                                        className="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300"
                                    >
                                        Annuler
                                    </button>
                                    <button
                                        onClick={() => {
                                            handleLeaveGroup();
                                            setShowConfirmDialog(null);
                                        }}
                                        className="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                                    >
                                        Quitter
                                    </button>
                                </div>
                            </>
                        )}

                        {showConfirmDialog === 'delete' && (
                            <>
                                <h3 className="text-lg font-semibold mb-2">Supprimer le groupe</h3>
                                <p className="text-gray-600 mb-4">
                                    Êtes-vous sûr de vouloir supprimer "{conversation.name}" ? 
                                    Cette action est irréversible.
                                </p>
                                <div className="flex space-x-3">
                                    <button
                                        onClick={() => setShowConfirmDialog(null)}
                                        className="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300"
                                    >
                                        Annuler
                                    </button>
                                    <button
                                        onClick={() => {
                                            handleDeleteGroup();
                                            setShowConfirmDialog(null);
                                        }}
                                        className="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                                    >
                                        Supprimer
                                    </button>
                                </div>
                            </>
                        )}

                        {showConfirmDialog === 'remove' && selectedParticipant && (
                            <>
                                <h3 className="text-lg font-semibold mb-2">Retirer du groupe</h3>
                                <p className="text-gray-600 mb-4">
                                    Êtes-vous sûr de vouloir retirer {selectedParticipant.name} du groupe ?
                                </p>
                                <div className="flex space-x-3">
                                    <button
                                        onClick={() => {
                                            setShowConfirmDialog(null);
                                            setSelectedParticipant(null);
                                        }}
                                        className="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300"
                                    >
                                        Annuler
                                    </button>
                                    <button
                                        onClick={() => handleRemoveParticipant(selectedParticipant)}
                                        className="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                                    >
                                        Retirer
                                    </button>
                                </div>
                            </>
                        )}

                        {showConfirmDialog === 'promote' && selectedParticipant && (
                            <>
                                <h3 className="text-lg font-semibold mb-2">
                                    {selectedParticipant.pivot.role === 'member' ? 'Promouvoir' : 'Rétrograder'}
                                </h3>
                                <p className="text-gray-600 mb-4">
                                    {selectedParticipant.pivot.role === 'member' 
                                        ? `Promouvoir ${selectedParticipant.name} administrateur ?`
                                        : `Rétrograder ${selectedParticipant.name} membre ?`
                                    }
                                </p>
                                <div className="flex space-x-3">
                                    <button
                                        onClick={() => {
                                            setShowConfirmDialog(null);
                                            setSelectedParticipant(null);
                                        }}
                                        className="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300"
                                    >
                                        Annuler
                                    </button>
                                    <button
                                        onClick={() => handlePromoteParticipant(selectedParticipant)}
                                        className="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                                    >
                                        {selectedParticipant.pivot.role === 'member' ? 'Promouvoir' : 'Rétrograder'}
                                    </button>
                                </div>
                            </>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}
