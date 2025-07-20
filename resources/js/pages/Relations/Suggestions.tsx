import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { Users, Plus, Search, Filter } from 'lucide-react';
import AuthenticatedLayout from '@/layouts/app-layout';
import axios from 'axios';

interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    mutual_connections?: number;
    location?: string;
    age?: number;
}

interface SuggestionsProps {
    user: any;
}

export default function Suggestions({ user }: SuggestionsProps) {
    const [suggestions, setSuggestions] = useState<User[]>([]);
    const [loading, setLoading] = useState(true);
    const [searchQuery, setSearchQuery] = useState('');
    const [connecting, setConnecting] = useState<number | null>(null);

    useEffect(() => {
        loadSuggestions();
    }, []);

    const loadSuggestions = async () => {
        setLoading(true);
        try {
            // Simuler des suggestions pour l'exemple
            // En production, cela viendrait d'une API
            const mockSuggestions: User[] = [
                {
                    id: 1,
                    name: "Sarah Benali",
                    email: "sarah.benali@example.com",
                    mutual_connections: 3,
                    location: "Casablanca, Maroc",
                    age: 28
                },
                {
                    id: 2,
                    name: "Karim Alaoui",
                    email: "karim.alaoui@example.com",
                    mutual_connections: 5,
                    location: "Rabat, Maroc",
                    age: 35
                },
                {
                    id: 3,
                    name: "Nadia Tazi",
                    email: "nadia.tazi@example.com",
                    mutual_connections: 2,
                    location: "Marrakech, Maroc",
                    age: 31
                }
            ];
            
            setSuggestions(mockSuggestions);
        } catch (error) {
            console.error('Erreur lors du chargement des suggestions:', error);
            setSuggestions([]);
        } finally {
            setLoading(false);
        }
    };

    const handleConnect = async (userId: number) => {
        setConnecting(userId);
        try {
            // Simuler l'envoi d'une demande de connexion
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            // Retirer la suggestion de la liste
            setSuggestions(prev => prev.filter(s => s.id !== userId));
        } catch (error) {
            console.error('Erreur lors de la connexion:', error);
        } finally {
            setConnecting(null);
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

    const filteredSuggestions = suggestions.filter(suggestion =>
        suggestion.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        suggestion.email.toLowerCase().includes(searchQuery.toLowerCase())
    );

    return (
        <AuthenticatedLayout user={user}>
            <Head title="Suggestions de Relations" />
            
            <div className="min-h-screen bg-gray-50">
                <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {/* Header */}
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900 mb-2">
                            Suggestions de Relations
                        </h1>
                        <p className="text-gray-600">
                            Gérez vos suggestions de connexions familiales
                        </p>
                    </div>

                    {/* Barre de recherche et filtres */}
                    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                        <div className="flex flex-col sm:flex-row gap-4">
                            <div className="flex-1 relative">
                                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                                <input
                                    type="text"
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    placeholder="Rechercher des personnes..."
                                    className="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                />
                            </div>
                            <button className="flex items-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <Filter className="w-5 h-5 mr-2 text-gray-500" />
                                Filtres
                            </button>
                        </div>
                    </div>

                    {/* Contenu principal */}
                    {loading ? (
                        <div className="flex justify-center items-center py-12">
                            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500"></div>
                        </div>
                    ) : filteredSuggestions.length === 0 ? (
                        /* État vide comme dans l'image */
                        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-12">
                            <div className="text-center max-w-md mx-auto">
                                <div className="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <svg className="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <h3 className="text-xl font-semibold text-gray-900 mb-3">
                                    Aucune suggestion
                                </h3>
                                <p className="text-gray-500 mb-6 leading-relaxed">
                                    Vous n'avez pas encore reçu de suggestions de relations familiales. 
                                    Explorez les réseaux pour découvrir de nouveaux utilisateurs.
                                </p>
                                <button
                                    onClick={() => window.location.href = '/family-relations'}
                                    className="inline-flex items-center px-6 py-3 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-all duration-200 transform hover:scale-105 font-medium"
                                >
                                    <Plus className="w-5 h-5 mr-2" />
                                    Explorer les Réseaux
                                </button>
                            </div>
                        </div>
                    ) : (
                        /* Liste des suggestions */
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {filteredSuggestions.map((suggestion) => (
                                <div key={suggestion.id} className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                                    <div className="flex items-center mb-4">
                                        {suggestion.avatar ? (
                                            <img
                                                src={suggestion.avatar}
                                                alt={suggestion.name}
                                                className="w-12 h-12 rounded-full object-cover"
                                            />
                                        ) : (
                                            <div className="w-12 h-12 rounded-full bg-gradient-to-br from-orange-100 to-red-100 flex items-center justify-center text-orange-600 font-medium">
                                                {getInitials(suggestion.name)}
                                            </div>
                                        )}
                                        <div className="ml-3 flex-1">
                                            <h3 className="font-semibold text-gray-900">{suggestion.name}</h3>
                                            <p className="text-sm text-gray-500">{suggestion.location}</p>
                                        </div>
                                    </div>

                                    <div className="space-y-2 mb-4">
                                        {suggestion.mutual_connections && (
                                            <p className="text-sm text-gray-600">
                                                <span className="font-medium">{suggestion.mutual_connections}</span> connexions mutuelles
                                            </p>
                                        )}
                                        {suggestion.age && (
                                            <p className="text-sm text-gray-600">
                                                {suggestion.age} ans
                                            </p>
                                        )}
                                    </div>

                                    <button
                                        onClick={() => handleConnect(suggestion.id)}
                                        disabled={connecting === suggestion.id}
                                        className="w-full flex items-center justify-center px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105 font-medium"
                                    >
                                        {connecting === suggestion.id ? (
                                            <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                                        ) : (
                                            <>
                                                <Users className="w-5 h-5 mr-2" />
                                                Se connecter
                                            </>
                                        )}
                                    </button>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
