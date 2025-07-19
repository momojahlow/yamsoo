import React, { useState, useEffect, useRef } from 'react';
import { Search, X, Calendar, User, FileText, Image, Video, Music } from 'lucide-react';
import axios from 'axios';

interface SearchResult {
    id: number;
    content: string;
    type: 'text' | 'image' | 'file' | 'audio' | 'video';
    created_at: string;
    user: {
        id: number;
        name: string;
        avatar?: string;
    };
    conversation: {
        id: number;
        name: string;
    };
    file_name?: string;
}

interface MessageSearchProps {
    isOpen: boolean;
    onClose: () => void;
    onMessageSelect: (conversationId: number, messageId: number) => void;
}

export default function MessageSearch({ isOpen, onClose, onMessageSelect }: MessageSearchProps) {
    const [query, setQuery] = useState('');
    const [results, setResults] = useState<SearchResult[]>([]);
    const [loading, setLoading] = useState(false);
    const [filters, setFilters] = useState({
        type: 'all', // all, text, image, file, audio, video
        dateRange: 'all', // all, today, week, month
        user: 'all' // all, specific user
    });
    
    const searchInputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        if (isOpen && searchInputRef.current) {
            searchInputRef.current.focus();
        }
    }, [isOpen]);

    useEffect(() => {
        const searchMessages = async () => {
            if (query.length < 2) {
                setResults([]);
                return;
            }

            setLoading(true);
            try {
                const response = await axios.get('/api/messages/search', {
                    params: {
                        q: query,
                        type: filters.type !== 'all' ? filters.type : undefined,
                        date_range: filters.dateRange !== 'all' ? filters.dateRange : undefined,
                        user_id: filters.user !== 'all' ? filters.user : undefined
                    }
                });
                setResults(response.data.results);
            } catch (error) {
                console.error('Erreur lors de la recherche:', error);
                setResults([]);
            } finally {
                setLoading(false);
            }
        };

        const debounceTimer = setTimeout(searchMessages, 300);
        return () => clearTimeout(debounceTimer);
    }, [query, filters]);

    const getTypeIcon = (type: string) => {
        switch (type) {
            case 'image': return <Image className="w-4 h-4 text-blue-500" />;
            case 'video': return <Video className="w-4 h-4 text-purple-500" />;
            case 'audio': return <Music className="w-4 h-4 text-green-500" />;
            case 'file': return <FileText className="w-4 h-4 text-gray-500" />;
            default: return <FileText className="w-4 h-4 text-gray-500" />;
        }
    };

    const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        const now = new Date();
        const diffInDays = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60 * 24));

        if (diffInDays === 0) {
            return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        } else if (diffInDays === 1) {
            return 'Hier';
        } else if (diffInDays < 7) {
            return date.toLocaleDateString('fr-FR', { weekday: 'long' });
        } else {
            return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
        }
    };

    const highlightText = (text: string, query: string) => {
        if (!query) return text;
        
        const regex = new RegExp(`(${query})`, 'gi');
        const parts = text.split(regex);
        
        return parts.map((part, index) => 
            regex.test(part) ? (
                <mark key={index} className="bg-yellow-200 text-yellow-900 px-1 rounded">
                    {part}
                </mark>
            ) : part
        );
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black/50 flex items-start justify-center z-50 p-4 pt-20">
            <div className="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[80vh] flex flex-col">
                {/* Header */}
                <div className="flex items-center justify-between p-6 border-b border-gray-200">
                    <h2 className="text-lg font-semibold text-gray-900 flex items-center">
                        <Search className="w-5 h-5 mr-2 text-orange-500" />
                        Rechercher dans les messages
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
                    <div className="relative mb-4">
                        <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                        <input
                            ref={searchInputRef}
                            type="text"
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder="Rechercher des messages, fichiers, ou personnes..."
                            className="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        />
                    </div>

                    {/* Filtres */}
                    <div className="flex flex-wrap gap-3">
                        <select
                            value={filters.type}
                            onChange={(e) => setFilters(prev => ({ ...prev, type: e.target.value }))}
                            className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        >
                            <option value="all">Tous les types</option>
                            <option value="text">Messages texte</option>
                            <option value="image">Images</option>
                            <option value="video">Vidéos</option>
                            <option value="audio">Audio</option>
                            <option value="file">Fichiers</option>
                        </select>

                        <select
                            value={filters.dateRange}
                            onChange={(e) => setFilters(prev => ({ ...prev, dateRange: e.target.value }))}
                            className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        >
                            <option value="all">Toutes les dates</option>
                            <option value="today">Aujourd'hui</option>
                            <option value="week">Cette semaine</option>
                            <option value="month">Ce mois</option>
                        </select>
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
                            <p className="text-lg font-medium mb-2">Rechercher des messages</p>
                            <p className="text-sm">
                                Tapez au moins 2 caractères pour commencer la recherche.
                            </p>
                        </div>
                    ) : results.length === 0 ? (
                        <div className="p-8 text-center text-gray-500">
                            <Search className="w-12 h-12 text-gray-300 mx-auto mb-4" />
                            <p className="text-lg font-medium mb-2">Aucun résultat</p>
                            <p className="text-sm">
                                Aucun message trouvé pour "{query}".
                            </p>
                        </div>
                    ) : (
                        <div className="p-4 space-y-3">
                            {results.map((result) => (
                                <div
                                    key={result.id}
                                    onClick={() => onMessageSelect(result.conversation.id, result.id)}
                                    className="p-4 hover:bg-gray-50 rounded-lg cursor-pointer transition-colors border border-gray-100"
                                >
                                    <div className="flex items-start space-x-3">
                                        <div className="flex-shrink-0">
                                            {getTypeIcon(result.type)}
                                        </div>
                                        
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center justify-between mb-1">
                                                <div className="flex items-center space-x-2">
                                                    <span className="font-medium text-gray-900">
                                                        {result.user.name}
                                                    </span>
                                                    <span className="text-sm text-gray-500">
                                                        dans {result.conversation.name}
                                                    </span>
                                                </div>
                                                <span className="text-xs text-gray-400">
                                                    {formatDate(result.created_at)}
                                                </span>
                                            </div>
                                            
                                            <div className="text-sm text-gray-700">
                                                {result.type === 'text' ? (
                                                    <p>{highlightText(result.content, query)}</p>
                                                ) : (
                                                    <p className="italic">
                                                        {result.file_name && highlightText(result.file_name, query)}
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* Footer */}
                <div className="p-4 border-t border-gray-200 bg-gray-50">
                    <p className="text-xs text-gray-500 text-center">
                        {results.length > 0 && `${results.length} résultat${results.length > 1 ? 's' : ''} trouvé${results.length > 1 ? 's' : ''}`}
                    </p>
                </div>
            </div>
        </div>
    );
}
