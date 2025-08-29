import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Users, X, Plus, AlertCircle } from 'lucide-react';

interface FamilyMember {
    id: number;
    name: string;
    email: string;
    avatar?: string;
}

interface CreateFamilyGroupProps {
    isOpen: boolean;
    onClose: () => void;
    onGroupCreated: (group: any) => void;
}

export default function CreateFamilyGroup({ isOpen, onClose, onGroupCreated }: CreateFamilyGroupProps) {
    const [familyMembers, setFamilyMembers] = useState<FamilyMember[]>([]);
    const [selectedMembers, setSelectedMembers] = useState<FamilyMember[]>([]);
    const [groupName, setGroupName] = useState('');
    const [groupDescription, setGroupDescription] = useState('');
    const [loading, setLoading] = useState(false);
    const [creating, setCreating] = useState(false);
    const [error, setError] = useState('');

    useEffect(() => {
        if (isOpen) {
            loadFamilyMembers();
        }
    }, [isOpen]);

    const loadFamilyMembers = async () => {
        setLoading(true);
        setError('');
        try {
            const response = await axios.get('/api/conversations/family-members');
            setFamilyMembers(response.data.family_members);

            if (response.data.family_members.length === 0) {
                setError(response.data.message || 'Aucun membre de famille disponible');
            }
        } catch (error) {
            console.error('Erreur lors du chargement des membres de famille:', error);
            setError('Erreur lors du chargement des membres de famille');
        } finally {
            setLoading(false);
        }
    };

    const toggleMember = (member: FamilyMember) => {
        setSelectedMembers(prev => {
            const isSelected = prev.some(m => m.id === member.id);
            if (isSelected) {
                return prev.filter(m => m.id !== member.id);
            } else {
                return [...prev, member];
            }
        });
    };

    const createGroup = async (e: React.FormEvent) => {
        e.preventDefault();

        if (!groupName.trim()) {
            setError('Le nom du groupe est requis');
            return;
        }

        if (selectedMembers.length === 0) {
            setError('Sélectionnez au moins un membre de famille');
            return;
        }

        setCreating(true);
        setError('');

        try {
            const response = await fetch('/messenger/conversations/group', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    name: groupName,
                    description: groupDescription,
                    participant_ids: selectedMembers.map(m => m.id)
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            onGroupCreated(data.conversation);
            onClose();

            // Reset form
            setGroupName('');
            setGroupDescription('');
            setSelectedMembers([]);

        } catch (error: any) {
            console.error('Erreur lors de la création du groupe:', error);
            if (error.response?.data?.error) {
                setError(error.response.data.error);
            } else {
                setError('Erreur lors de la création du groupe');
            }
        } finally {
            setCreating(false);
        }
    };

    const getInitials = (name: string) => {
        return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-hidden">
                {/* Header */}
                <div className="flex items-center justify-between p-4 border-b border-gray-200">
                    <h2 className="text-lg font-semibold text-gray-900">Créer un groupe familial</h2>
                    <button
                        onClick={onClose}
                        className="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100"
                    >
                        <X className="w-5 h-5" />
                    </button>
                </div>

                {/* Content */}
                <div className="p-4 overflow-y-auto max-h-[calc(90vh-120px)]">
                    {error && (
                        <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg flex items-center space-x-2">
                            <AlertCircle className="w-5 h-5 text-red-500 flex-shrink-0" />
                            <p className="text-sm text-red-700">{error}</p>
                        </div>
                    )}

                    <form onSubmit={createGroup} className="space-y-4">
                        {/* Nom du groupe */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Nom du groupe *
                            </label>
                            <input
                                type="text"
                                value={groupName}
                                onChange={(e) => setGroupName(e.target.value)}
                                placeholder="Ex: Famille Martin"
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                required
                            />
                        </div>

                        {/* Description */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Description (optionnel)
                            </label>
                            <textarea
                                value={groupDescription}
                                onChange={(e) => setGroupDescription(e.target.value)}
                                placeholder="Description du groupe..."
                                rows={2}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none"
                            />
                        </div>

                        {/* Membres sélectionnés */}
                        {selectedMembers.length > 0 && (
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Membres sélectionnés ({selectedMembers.length})
                                </label>
                                <div className="flex flex-wrap gap-2">
                                    {selectedMembers.map(member => (
                                        <div
                                            key={member.id}
                                            className="flex items-center space-x-2 bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm"
                                        >
                                            <span>{member.name}</span>
                                            <button
                                                type="button"
                                                onClick={() => toggleMember(member)}
                                                className="hover:bg-orange-200 rounded-full p-1"
                                            >
                                                <X className="w-3 h-3" />
                                            </button>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Liste des membres de famille */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Sélectionner les membres de famille
                            </label>

                            {loading ? (
                                <div className="flex justify-center py-4">
                                    <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-orange-500"></div>
                                </div>
                            ) : familyMembers.length === 0 ? (
                                <div className="text-center py-6 text-gray-500">
                                    <Users className="w-12 h-12 mx-auto mb-3 text-gray-400" />
                                    <p className="font-medium">Aucun membre de famille</p>
                                    <p className="text-sm">Ajoutez des relations familiales pour créer des groupes</p>
                                </div>
                            ) : (
                                <div className="space-y-2 max-h-48 overflow-y-auto">
                                    {familyMembers.map(member => {
                                        const isSelected = selectedMembers.some(m => m.id === member.id);
                                        return (
                                            <div
                                                key={member.id}
                                                onClick={() => toggleMember(member)}
                                                className={`flex items-center space-x-3 p-3 rounded-lg cursor-pointer transition-colors ${
                                                    isSelected
                                                        ? 'bg-orange-50 border-2 border-orange-200'
                                                        : 'hover:bg-gray-50 border-2 border-transparent'
                                                }`}
                                            >
                                                <div className="relative">
                                                    {member.avatar ? (
                                                        <img
                                                            src={member.avatar}
                                                            alt={member.name}
                                                            className="w-10 h-10 rounded-full object-cover"
                                                        />
                                                    ) : (
                                                        <div className="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-medium">
                                                            {getInitials(member.name)}
                                                        </div>
                                                    )}
                                                    {isSelected && (
                                                        <div className="absolute -top-1 -right-1 w-5 h-5 bg-orange-500 rounded-full flex items-center justify-center">
                                                            <Plus className="w-3 h-3 text-white rotate-45" />
                                                        </div>
                                                    )}
                                                </div>

                                                <div className="flex-1">
                                                    <p className="font-medium text-gray-900">{member.name}</p>
                                                    <p className="text-sm text-gray-500">{member.email}</p>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            )}
                        </div>

                        {/* Boutons */}
                        <div className="flex space-x-3 pt-4">
                            <button
                                type="button"
                                onClick={onClose}
                                className="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                            >
                                Annuler
                            </button>
                            <button
                                type="submit"
                                disabled={creating || !groupName.trim() || selectedMembers.length === 0}
                                className="flex-1 px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                            >
                                {creating ? (
                                    <div className="flex items-center justify-center space-x-2">
                                        <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                        <span>Création...</span>
                                    </div>
                                ) : (
                                    'Créer le groupe'
                                )}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
