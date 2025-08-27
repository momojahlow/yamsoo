import React, { useState, useEffect } from 'react';
import { Search, X, MessageCircle } from 'lucide-react';
import { router } from '@inertiajs/react';

interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    is_online: boolean;
    relationship?: string;
    relationship_code?: string;
    is_family?: boolean;
}

interface UserSearchProps {
    onClose: () => void;
    onConversationCreated: (conversationId: number) => void;
}

export default function UserSearch({ onClose, onConversationCreated }: UserSearchProps) {
    const [query, setQuery] = useState('');
    const [creating, setCreating] = useState<number | null>(null);

    // État local pour les utilisateurs
    const [users, setUsers] = useState<User[]>([]);
    const [loading, setLoading] = useState(false);

    // Fonction de recherche simplifiée
    const searchUsers = async (searchQuery: string) => {
        if (!searchQuery.trim()) {
            setUsers([]);
            return;
        }

        setLoading(true);
        try {
            // Simuler une recherche - en réalité, cela devrait être une API
            // Pour l'instant, on redirige directement vers la messagerie
            setUsers([]);
        } catch (error) {
            console.error('Erreur lors de la recherche:', error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        const debounceTimer = setTimeout(() => {
            searchUsers(query);
        }, 300);

        return () => clearTimeout(debounceTimer);
    }, [query]);

    const handleCreateConversation = (userId: number) => {
        setCreating(userId);
        // Rediriger directement vers la messagerie avec l'utilisateur sélectionné
        router.visit(`/messagerie?selectedContactId=${userId}`);
    };

    const getInitials = (name: string) => {
        return name
            .split(' ')
            .map(word => word.charAt(0))
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };

    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[80vh] flex flex-col">
                {/* Header */}
                <div className="flex items-center justify-between p-6 border-b border-gray-200">
                    <h2 className="text-lg font-semibold text-gray-900">
                        Nouvelle conversation
                    </h2>
                    <button
                        onClick={onClose}
                        className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <X className="w-5 h-5" />
                    </button>
                </div>

                {/* Barre de recherche */}
                <div className="p-6 border-b border-gray-200">
                    <div className="relative">
                        <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                        <input
                            type="text"
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder="Rechercher un membre de la famille..."
                            className="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            autoFocus
                        />
                    </div>
                </div>

                {/* Résultats */}
                <div className="flex-1 overflow-y-auto">
                    {loading ? (
                        <div className="flex justify-center items-center p-8">
                            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"></div>
                        </div>
                    ) : query.length < 2 ? (
                        <div className="p-8 text-center text-gray-500">
                            <Search className="w-12 h-12 text-gray-300 mx-auto mb-4" />
                            <p className="text-lg font-medium mb-2">Rechercher des membres</p>
                            <p className="text-sm">
                                Tapez au moins 2 caractères pour rechercher des membres de votre famille.
                            </p>
                        </div>
                    ) : users.length === 0 ? (
                        <div className="p-8 text-center text-gray-500">
                            <Search className="w-12 h-12 text-gray-300 mx-auto mb-4" />
                            <p className="text-lg font-medium mb-2">Aucun résultat</p>
                            <p className="text-sm">
                                Aucun membre trouvé pour "{query}".
                            </p>
                        </div>
                    ) : (
                        <div className="p-4 space-y-2">
                            {users.map((user) => (
                                <div
                                    key={user.id}
                                    className="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors"
                                >
                                    <div className="flex items-center flex-1">
                                        {/* Avatar */}
                                        <div className="relative flex-shrink-0">
                                            {user.avatar ? (
                                                <img
                                                    src={user.avatar}
                                                    alt={user.name}
                                                    className="w-12 h-12 rounded-full object-cover"
                                                />
                                            ) : (
                                                <div className="w-12 h-12 rounded-full bg-gradient-to-br from-orange-100 to-red-100 flex items-center justify-center text-orange-600 font-medium text-sm">
                                                    {getInitials(user.name)}
                                                </div>
                                            )}

                                            {/* Indicateur en ligne */}
                                            {user.is_online && (
                                                <div className="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-green-500 border-2 border-white rounded-full"></div>
                                            )}
                                        </div>

                                        {/* Informations utilisateur */}
                                        <div className="ml-3 flex-1 min-w-0">
                                            <div className="flex items-center space-x-2">
                                                <h3 className="font-medium text-gray-900 truncate">
                                                    {user.name}
                                                </h3>
                                                {user.is_family && (
                                                    <span className="px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded-full font-medium">
                                                        Famille
                                                    </span>
                                                )}
                                            </div>
                                            <div className="flex items-center space-x-2 mt-1">
                                                {user.relationship && (
                                                    <p className="text-sm text-orange-600 font-medium">
                                                        {user.relationship}
                                                    </p>
                                                )}
                                                <p className="text-sm text-gray-500 truncate">
                                                    {user.email}
                                                </p>
                                            </div>
                                            {user.is_online && (
                                                <p className="text-xs text-green-600">En ligne</p>
                                            )}
                                        </div>
                                    </div>

                                    {/* Bouton de conversation */}
                                    <button
                                        onClick={() => handleCreateConversation(user.id)}
                                        disabled={creating === user.id}
                                        className="ml-3 p-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105"
                                    >
                                        {creating === user.id ? (
                                            <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                                        ) : (
                                            <MessageCircle className="w-5 h-5" />
                                        )}
                                    </button>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* Footer */}
                <div className="p-6 border-t border-gray-200 bg-gray-50">
                    <p className="text-xs text-gray-500 text-center">
                        Vous pouvez uniquement envoyer des messages aux membres de votre famille.
                    </p>
                </div>
            </div>
        </div>
    );
}
