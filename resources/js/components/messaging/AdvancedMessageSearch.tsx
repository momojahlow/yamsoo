import React, { useState, useEffect } from 'react';
import { Search, Filter, Calendar, User, FileText, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface SearchFilters {
  query: string;
  sender?: string;
  dateFrom?: string;
  dateTo?: string;
  fileType?: 'all' | 'image' | 'video' | 'audio' | 'file';
  conversationId?: number;
}

interface SearchResult {
  id: number;
  content: string;
  sender_name: string;
  conversation_name: string;
  created_at: string;
  file_type?: string;
  file_name?: string;
  conversation_id: number;
}

interface AdvancedMessageSearchProps {
  isOpen: boolean;
  onClose: () => void;
  onResultClick: (conversationId: number, messageId: number) => void;
}

export function AdvancedMessageSearch({ isOpen, onClose, onResultClick }: AdvancedMessageSearchProps) {
  const [filters, setFilters] = useState<SearchFilters>({
    query: '',
    fileType: 'all'
  });
  const [results, setResults] = useState<SearchResult[]>([]);
  const [loading, setLoading] = useState(false);
  const [totalResults, setTotalResults] = useState(0);

  const handleSearch = async () => {
    if (!filters.query.trim()) return;

    setLoading(true);
    try {
      const params = new URLSearchParams();
      params.append('q', filters.query);
      
      if (filters.sender) params.append('sender', filters.sender);
      if (filters.dateFrom) params.append('date_from', filters.dateFrom);
      if (filters.dateTo) params.append('date_to', filters.dateTo);
      if (filters.fileType && filters.fileType !== 'all') params.append('file_type', filters.fileType);
      if (filters.conversationId) params.append('conversation_id', filters.conversationId.toString());

      const response = await fetch(`/api/messages/search?${params.toString()}`);
      const data = await response.json();

      if (response.ok) {
        setResults(data.results || []);
        setTotalResults(data.total || 0);
      } else {
        console.error('Erreur de recherche:', data.error);
        setResults([]);
        setTotalResults(0);
      }
    } catch (error) {
      console.error('Erreur lors de la recherche:', error);
      setResults([]);
      setTotalResults(0);
    } finally {
      setLoading(false);
    }
  };

  const clearFilters = () => {
    setFilters({
      query: '',
      fileType: 'all'
    });
    setResults([]);
    setTotalResults(0);
  };

  const highlightText = (text: string, query: string) => {
    if (!query.trim()) return text;
    
    const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    const parts = text.split(regex);
    
    return parts.map((part, index) => 
      regex.test(part) ? (
        <mark key={index} className="bg-yellow-200 px-1 rounded">
          {part}
        </mark>
      ) : part
    );
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const getFileIcon = (fileType?: string) => {
    switch (fileType) {
      case 'image': return '🖼️';
      case 'video': return '🎥';
      case 'audio': return '🎵';
      case 'file': return '📄';
      default: return '💬';
    }
  };

  useEffect(() => {
    if (filters.query.trim()) {
      const debounceTimer = setTimeout(handleSearch, 500);
      return () => clearTimeout(debounceTimer);
    } else {
      setResults([]);
      setTotalResults(0);
    }
  }, [filters]);

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
      <Card className="w-full max-w-4xl max-h-[90vh] overflow-hidden">
        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-4">
          <CardTitle className="flex items-center gap-2">
            <Search className="h-5 w-5" />
            Recherche avancée dans les messages
          </CardTitle>
          <Button variant="ghost" size="sm" onClick={onClose}>
            <X className="h-4 w-4" />
          </Button>
        </CardHeader>
        
        <CardContent className="space-y-4">
          {/* Barre de recherche principale */}
          <div className="flex gap-2">
            <div className="flex-1 relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
              <Input
                placeholder="Rechercher dans les messages..."
                value={filters.query}
                onChange={(e) => setFilters(prev => ({ ...prev, query: e.target.value }))}
                className="pl-10"
              />
            </div>
            <Button onClick={clearFilters} variant="outline" size="sm">
              Effacer
            </Button>
          </div>

          {/* Filtres avancés */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label className="text-sm font-medium mb-1 block">Expéditeur</label>
              <Input
                placeholder="Nom de l'expéditeur"
                value={filters.sender || ''}
                onChange={(e) => setFilters(prev => ({ ...prev, sender: e.target.value }))}
              />
            </div>
            
            <div>
              <label className="text-sm font-medium mb-1 block">Date de début</label>
              <Input
                type="date"
                value={filters.dateFrom || ''}
                onChange={(e) => setFilters(prev => ({ ...prev, dateFrom: e.target.value }))}
              />
            </div>
            
            <div>
              <label className="text-sm font-medium mb-1 block">Date de fin</label>
              <Input
                type="date"
                value={filters.dateTo || ''}
                onChange={(e) => setFilters(prev => ({ ...prev, dateTo: e.target.value }))}
              />
            </div>
            
            <div>
              <label className="text-sm font-medium mb-1 block">Type de fichier</label>
              <Select value={filters.fileType} onValueChange={(value) => setFilters(prev => ({ ...prev, fileType: value as any }))}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Tous</SelectItem>
                  <SelectItem value="image">Images</SelectItem>
                  <SelectItem value="video">Vidéos</SelectItem>
                  <SelectItem value="audio">Audio</SelectItem>
                  <SelectItem value="file">Fichiers</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          {/* Résultats */}
          <div className="border-t pt-4">
            {loading ? (
              <div className="text-center py-8">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p className="mt-2 text-sm text-gray-600">Recherche en cours...</p>
              </div>
            ) : (
              <>
                {totalResults > 0 && (
                  <div className="mb-4">
                    <Badge variant="secondary">
                      {totalResults} résultat{totalResults > 1 ? 's' : ''} trouvé{totalResults > 1 ? 's' : ''}
                    </Badge>
                  </div>
                )}
                
                <div className="space-y-3 max-h-96 overflow-y-auto">
                  {results.map((result) => (
                    <div
                      key={result.id}
                      className="p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
                      onClick={() => onResultClick(result.conversation_id, result.id)}
                    >
                      <div className="flex items-start justify-between">
                        <div className="flex-1">
                          <div className="flex items-center gap-2 mb-1">
                            <span className="text-lg">{getFileIcon(result.file_type)}</span>
                            <span className="font-medium text-sm">{result.sender_name}</span>
                            <span className="text-xs text-gray-500">dans {result.conversation_name}</span>
                          </div>
                          
                          <p className="text-sm text-gray-700 mb-2">
                            {highlightText(result.content, filters.query)}
                          </p>
                          
                          {result.file_name && (
                            <div className="flex items-center gap-1 text-xs text-blue-600">
                              <FileText className="h-3 w-3" />
                              {result.file_name}
                            </div>
                          )}
                        </div>
                        
                        <div className="text-xs text-gray-500 ml-4">
                          {formatDate(result.created_at)}
                        </div>
                      </div>
                    </div>
                  ))}
                  
                  {results.length === 0 && filters.query.trim() && !loading && (
                    <div className="text-center py-8 text-gray-500">
                      <Search className="h-12 w-12 mx-auto mb-4 text-gray-300" />
                      <p>Aucun résultat trouvé pour "{filters.query}"</p>
                      <p className="text-sm mt-1">Essayez avec d'autres mots-clés</p>
                    </div>
                  )}
                </div>
              </>
            )}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
