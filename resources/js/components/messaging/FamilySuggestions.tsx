import React, { useState, useEffect } from 'react';
import { Users, MessageCircle, Plus, Heart, Crown } from 'lucide-react';
import axios from 'axios';

interface FamilyMember {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    relationship: string;
    relationship_code: string;
    is_online: boolean;
    is_family: boolean;
}

interface FamilySuggestionsProps {
    isOpen: boolean;
    onClose: () => void;
    onConversationCreated: (conversationId: number) => void;
}

export default function FamilySuggestions({ isOpen, onClose, onConversationCreated }: FamilySuggestionsProps) {
    const [suggestions, setSuggestions] = useState<FamilyMember[]>([]);
    const [loading, setLoading] = useState(false);
    const [creating, setCreating] = useState<number | null>(null);
    const [creatingGroup, setCreatingGroup] = useState(false);
    const [canCreateGroup, setCanCreateGroup] = useState(false);
    const [groupName, setGroupName] = useState('');

    useEffect(() => {
        if (isOpen) {
            loadSuggestions();
        }
    }, [isOpen]);

    const loadSuggestions = async () => {
        setLoading(true);
        try {
            const response = await axios.get('/api/conversations/family-suggestions');
            setSuggestions(response.data.suggestions);
            setCanCreateGroup(response.data.can_create_family_group);
        } catch (error) {
            console.error('Erreur lors du chargement des suggestions:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleCreateConversation = async (userId: number) => {
        setCreating(userId);
        try {
            const response = await axios.post('/api/conversations', {
                user_id: userId,
                type: 'private'
            });
            onConversationCreated(response.data.conversation_id);
        } catch (error) {
            console.error('Erreur lors de la création de la conversation:', error);
        } finally {
            setCreating(null);
        }
    };

    const handleCreateFamilyGroup = async () => {
        setCreatingGroup(true);
        try {
            const response = await axios.post('/api/conversations/family-group', {
                name: groupName || undefined
            });
            onConversationCreated(response.data.conversation_id);
        } catch (error) {
            console.error('Erreur lors de la création du groupe familial:', error);
        } finally {
            setCreatingGroup(false);
        }
    };

    const getInitials = (name: string) => {
        return name
            .split(' ')
            .map(word => word.charAt(0))
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };

    const getRelationshipIcon = (relationshipCode: string) => {
        switch (relationshipCode) {
            case 'father':
            case 'mother':
                return <Crown className="w-4 h-4 text-yellow-500" />;
            case 'son':
            case 'daughter':
                return <Heart className="w-4 h-4 text-pink-500" />;
            case 'brother':
            case 'sister':
                return <Users className="w-4 h-4 text-blue-500" />;
            default:
                return <Heart className="w-4 h-4 text-purple-500" />;
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[80vh] flex flex-col">
                {/* Header */}
                <div className="flex items-center justify-between p-6 border-b border-gray-200">
                    <h2 className="text-lg font-semibold text-gray-900 flex items-center">
                        <Users className="w-5 h-5 mr-2 text-orange-500" />
                        Suggestions familiales
                    </h2>
                    <button
                        onClick={onClose}
                        className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        ×
                    </button>
                </div>

                {/* Contenu */}
                <div className="flex-1 overflow-y-auto">
                    {loading ? (
                        <div className="flex justify-center items-center p-8">
                            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"></div>
                        </div>
                    ) : (
                        <div className="p-6 space-y-6">
                            {/* Bouton groupe familial */}
                            {canCreateGroup && (
                                <div className="bg-gradient-to-r from-orange-50 to-red-50 p-4 rounded-lg border border-orange-200">
                                    <h3 className="font-medium text-gray-900 mb-2 flex items-center">
                                        <Users className="w-5 h-5 mr-2 text-orange-500" />
                                        Créer un groupe familial
                                    </h3>
                                    <p className="text-sm text-gray-600 mb-3">
                                        Rassemblez toute votre famille dans une conversation de groupe.
                                    </p>
                                    
                                    <div className="flex items-center space-x-3">
                                        <input
                                            type="text"
                                            value={groupName}
                                            onChange={(e) => setGroupName(e.target.value)}
                                            placeholder="Nom du groupe (optionnel)"
                                            className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                        />
                                        <button
                                            onClick={handleCreateFamilyGroup}
                                            disabled={creatingGroup}
                                            className="px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105 text-sm font-medium"
                                        >
                                            {creatingGroup ? (
                                                <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                            ) : (
                                                <Plus className="w-4 h-4" />
                                            )}
                                        </button>
                                    </div>
                                </div>
                            )}

                            {/* Liste des suggestions */}
                            {suggestions.length === 0 ? (
                                <div className="text-center py-8">
                                    <Users className="w-12 h-12 text-gray-300 mx-auto mb-4" />
                                    <h3 className="text-lg font-medium text-gray-900 mb-2">
                                        Aucune suggestion
                                    </h3>
                                    <p className="text-sm text-gray-500">
                                        Vous avez déjà des conversations avec tous vos membres de famille disponibles.
                                    </p>
                                </div>
                            ) : (
                                <div>
                                    <h3 className="font-medium text-gray-900 mb-4">
                                        Membres de famille disponibles
                                    </h3>
                                    <div className="space-y-3">
                                        {suggestions.map((member) => (
                                            <div
                                                key={member.id}
                                                className="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors border border-gray-100"
                                            >
                                                <div className="flex items-center flex-1">
                                                    {/* Avatar */}
                                                    <div className="relative flex-shrink-0">
                                                        {member.avatar ? (
                                                            <img
                                                                src={member.avatar}
                                                                alt={member.name}
                                                                className="w-12 h-12 rounded-full object-cover"
                                                            />
                                                        ) : (
                                                            <div className="w-12 h-12 rounded-full bg-gradient-to-br from-orange-100 to-red-100 flex items-center justify-center text-orange-600 font-medium text-sm">
                                                                {getInitials(member.name)}
                                                            </div>
                                                        )}
                                                        
                                                        {/* Indicateur en ligne */}
                                                        {member.is_online && (
                                                            <div className="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-green-500 border-2 border-white rounded-full"></div>
                                                        )}
                                                    </div>

                                                    {/* Informations */}
                                                    <div className="ml-3 flex-1 min-w-0">
                                                        <div className="flex items-center space-x-2">
                                                            <h3 className="font-medium text-gray-900 truncate">
                                                                {member.name}
                                                            </h3>
                                                            {getRelationshipIcon(member.relationship_code)}
                                                        </div>
                                                        <div className="flex items-center space-x-2 mt-1">
                                                            <p className="text-sm text-orange-600 font-medium">
                                                                {member.relationship}
                                                            </p>
                                                            {member.is_online && (
                                                                <span className="text-xs text-green-600">En ligne</span>
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>

                                                {/* Bouton de conversation */}
                                                <button
                                                    onClick={() => handleCreateConversation(member.id)}
                                                    disabled={creating === member.id}
                                                    className="ml-3 p-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105"
                                                >
                                                    {creating === member.id ? (
                                                        <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                                                    ) : (
                                                        <MessageCircle className="w-5 h-5" />
                                                    )}
                                                </button>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    )}
                </div>

                {/* Footer */}
                <div className="p-6 border-t border-gray-200 bg-gray-50">
                    <p className="text-xs text-gray-500 text-center">
                        Les suggestions sont basées sur vos relations familiales confirmées.
                    </p>
                </div>
            </div>
        </div>
    );
}
