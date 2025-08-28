import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { KwdDashboardLayout } from '@/Layouts/modern';
import { Users, UserPlus, Search, ArrowLeft, Check, X } from 'lucide-react';

interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
}

interface Group {
    id: number;
    name: string;
    description?: string;
    participants_count: number;
    can_manage: boolean;
}

interface Props {
    group: Group;
    familyMembers: User[];
    allUsers: User[];
    currentParticipants: User[];
}

export default function GroupInvite({ group, familyMembers, allUsers, currentParticipants }: Props) {
    const [selectedUsers, setSelectedUsers] = useState<number[]>([]);
    const [searchTerm, setSearchTerm] = useState('');
    const [activeTab, setActiveTab] = useState<'family' | 'all'>('family');
    const [isSubmitting, setIsSubmitting] = useState(false);

    const getInitials = (name: string) => {
        return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
    };

    const filteredUsers = (activeTab === 'family' ? familyMembers : allUsers).filter(user =>
        user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        user.email.toLowerCase().includes(searchTerm.toLowerCase())
    );

    const toggleUserSelection = (userId: number) => {
        setSelectedUsers(prev => 
            prev.includes(userId) 
                ? prev.filter(id => id !== userId)
                : [...prev, userId]
        );
    };

    const handleInvite = async () => {
        if (selectedUsers.length === 0) {
            alert('Veuillez sélectionner au moins un utilisateur à inviter.');
            return;
        }

        setIsSubmitting(true);

        try {
            router.post(`/groups/${group.id}/add-participant`, {
                user_ids: selectedUsers
            }, {
                onSuccess: () => {
                    router.get('/groups');
                },
                onError: (errors) => {
                    console.error('Erreur lors de l\'invitation:', errors);
                    alert('Erreur lors de l\'invitation des utilisateurs.');
                },
                onFinish: () => {
                    setIsSubmitting(false);
                }
            });
        } catch (error) {
            console.error('Erreur:', error);
            setIsSubmitting(false);
        }
    };

    return (
        <KwdDashboardLayout>
            <Head title={`Inviter des membres - ${group.name}`} />

            <div className="max-w-4xl mx-auto">
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
                            <h1 className="text-2xl font-bold text-gray-900">Inviter des membres</h1>
                            <p className="text-gray-600">Groupe: {group.name} ({group.participants_count} membres)</p>
                        </div>
                    </div>

                    {/* Barre de recherche */}
                    <div className="relative">
                        <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                        <input
                            type="text"
                            placeholder="Rechercher par nom ou email..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        />
                    </div>
                </div>

                {/* Onglets */}
                <div className="mb-6">
                    <div className="border-b border-gray-200">
                        <nav className="-mb-px flex space-x-8">
                            <button
                                onClick={() => setActiveTab('family')}
                                className={`py-2 px-1 border-b-2 font-medium text-sm ${
                                    activeTab === 'family'
                                        ? 'border-orange-500 text-orange-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                            >
                                👨‍👩‍👧‍👦 Famille ({familyMembers.length})
                            </button>
                            <button
                                onClick={() => setActiveTab('all')}
                                className={`py-2 px-1 border-b-2 font-medium text-sm ${
                                    activeTab === 'all'
                                        ? 'border-orange-500 text-orange-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                }`}
                            >
                                👥 Tous les utilisateurs ({allUsers.length})
                            </button>
                        </nav>
                    </div>
                </div>

                {/* Liste des utilisateurs */}
                <div className="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div className="p-4 border-b border-gray-200">
                        <div className="flex items-center justify-between">
                            <h3 className="font-medium text-gray-900">
                                {selectedUsers.length > 0 
                                    ? `${selectedUsers.length} utilisateur${selectedUsers.length > 1 ? 's' : ''} sélectionné${selectedUsers.length > 1 ? 's' : ''}`
                                    : 'Sélectionnez des utilisateurs à inviter'
                                }
                            </h3>
                            {selectedUsers.length > 0 && (
                                <button
                                    onClick={() => setSelectedUsers([])}
                                    className="text-sm text-gray-500 hover:text-gray-700"
                                >
                                    Tout désélectionner
                                </button>
                            )}
                        </div>
                    </div>

                    <div className="max-h-96 overflow-y-auto">
                        {filteredUsers.length === 0 ? (
                            <div className="p-8 text-center">
                                <Users className="w-12 h-12 text-gray-300 mx-auto mb-3" />
                                <p className="text-gray-500">
                                    {searchTerm 
                                        ? 'Aucun utilisateur trouvé pour cette recherche'
                                        : `Aucun ${activeTab === 'family' ? 'membre de famille' : 'utilisateur'} disponible`
                                    }
                                </p>
                            </div>
                        ) : (
                            <div className="divide-y divide-gray-200">
                                {filteredUsers.map((user) => (
                                    <div key={user.id} className="p-4 hover:bg-gray-50 transition-colors">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center space-x-3">
                                                {user.avatar ? (
                                                    <img
                                                        src={user.avatar}
                                                        alt={user.name}
                                                        className="w-10 h-10 rounded-full object-cover"
                                                    />
                                                ) : (
                                                    <div className="w-10 h-10 rounded-full bg-gradient-to-br from-orange-100 to-red-100 flex items-center justify-center text-orange-600 font-medium">
                                                        {getInitials(user.name)}
                                                    </div>
                                                )}
                                                <div>
                                                    <h4 className="font-medium text-gray-900">{user.name}</h4>
                                                    <p className="text-sm text-gray-500">{user.email}</p>
                                                </div>
                                            </div>

                                            <button
                                                onClick={() => toggleUserSelection(user.id)}
                                                className={`p-2 rounded-lg transition-colors ${
                                                    selectedUsers.includes(user.id)
                                                        ? 'bg-orange-100 text-orange-600 hover:bg-orange-200'
                                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                                }`}
                                            >
                                                {selectedUsers.includes(user.id) ? (
                                                    <Check className="w-5 h-5" />
                                                ) : (
                                                    <UserPlus className="w-5 h-5" />
                                                )}
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                {/* Actions */}
                <div className="mt-6 flex items-center justify-between">
                    <button
                        onClick={() => router.get('/groups')}
                        className="px-4 py-2 text-gray-600 hover:text-gray-700 font-medium"
                    >
                        Annuler
                    </button>

                    <button
                        onClick={handleInvite}
                        disabled={selectedUsers.length === 0 || isSubmitting}
                        className="px-6 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {isSubmitting ? (
                            <>
                                <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white inline-block mr-2"></div>
                                Invitation en cours...
                            </>
                        ) : (
                            <>
                                <UserPlus className="w-4 h-4 mr-2 inline" />
                                Inviter {selectedUsers.length > 0 ? `(${selectedUsers.length})` : ''}
                            </>
                        )}
                    </button>
                </div>

                {/* Info */}
                <div className="mt-4 p-4 bg-blue-50 rounded-lg">
                    <p className="text-sm text-blue-700">
                        💡 Les utilisateurs invités recevront une notification et pourront rejoindre le groupe immédiatement.
                    </p>
                </div>
            </div>
        </KwdDashboardLayout>
    );
}
