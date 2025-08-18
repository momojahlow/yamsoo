import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { KwdDashboardLayout } from '@/layouts/modern';
import { useTranslation } from '@/hooks/useTranslation';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { 
  Camera, Plus, Eye, Settings, Calendar, User, Search, Filter, Grid3X3, 
  List, Heart, Share2, Download, MoreVertical, Edit, Trash2, Lock,
  Globe, Users, Image, Upload, FolderPlus, Star, Clock, MapPin, Play
} from 'lucide-react';

interface PhotoAlbum {
    id: number;
    title: string;
    description?: string;
    cover_photo?: string;
    privacy: 'public' | 'family' | 'private';
    is_default: boolean;
    photos_count: number;
    created_at: string;
    updated_at: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface Props {
    albums: PhotoAlbum[];
    user: User;
    canCreateAlbum: boolean;
}

const privacyConfig = {
    public: { icon: Globe, label: 'Public', color: 'bg-green-100 text-green-800 border-green-200' },
    family: { icon: Users, label: 'Famille', color: 'bg-orange-100 text-orange-800 border-orange-200' },
    private: { icon: Lock, label: 'Privé', color: 'bg-red-100 text-red-800 border-red-200' },
};

export default function ModernPhotoAlbumsIndex({ albums, user, canCreateAlbum }: Props) {
    const { t } = useTranslation();
    const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
    const [searchQuery, setSearchQuery] = useState('');
    const [sortBy, setSortBy] = useState('recent');
    const [filterBy, setFilterBy] = useState('all');
    const [selectedAlbums, setSelectedAlbums] = useState<number[]>([]);
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);

    // Filtrage et tri des albums
    const filteredAndSortedAlbums = albums
        .filter(album => {
            const matchesSearch = album.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
                                album.description?.toLowerCase().includes(searchQuery.toLowerCase());
            const matchesFilter = filterBy === 'all' || album.privacy === filterBy;
            return matchesSearch && matchesFilter;
        })
        .sort((a, b) => {
            switch (sortBy) {
                case 'recent':
                    return new Date(b.updated_at).getTime() - new Date(a.updated_at).getTime();
                case 'oldest':
                    return new Date(a.created_at).getTime() - new Date(b.created_at).getTime();
                case 'name':
                    return a.title.localeCompare(b.title);
                case 'photos':
                    return b.photos_count - a.photos_count;
                default:
                    return 0;
            }
        });

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    };

    const AlbumCard = ({ album }: { album: PhotoAlbum }) => {
        const privacy = privacyConfig[album.privacy];
        const PrivacyIcon = privacy.icon;

        return (
            <Card className="group overflow-hidden hover:shadow-xl transition-all duration-300 border-0 shadow-md bg-white/80 backdrop-blur-sm">
                {/* Image de couverture */}
                <div className="relative h-48 bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden">
                    {album.cover_photo ? (
                        <img
                            src={album.cover_photo}
                            alt={`Couverture de ${album.title}`}
                            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        />
                    ) : (
                        <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-100 to-red-100">
                            <Camera className="w-12 h-12 text-orange-400" />
                        </div>
                    )}
                    
                    {/* Overlay avec actions */}
                    <div className="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                        <div className="flex gap-2">
                            <Button size="sm" variant="secondary" className="bg-white/90 hover:bg-white">
                                <Eye className="w-4 h-4" />
                            </Button>
                            <Button size="sm" variant="secondary" className="bg-white/90 hover:bg-white">
                                <Play className="w-4 h-4" />
                            </Button>
                        </div>
                    </div>

                    {/* Badge de confidentialité */}
                    <div className="absolute top-3 left-3">
                        <Badge className={`${privacy.color} border`}>
                            <PrivacyIcon className="w-3 h-3 mr-1" />
                            {privacy.label}
                        </Badge>
                    </div>

                    {/* Badge album par défaut */}
                    {album.is_default && (
                        <div className="absolute top-3 right-3">
                            <Badge className="bg-yellow-100 text-yellow-800 border-yellow-200">
                                <Star className="w-3 h-3 mr-1" />
                                Défaut
                            </Badge>
                        </div>
                    )}

                    {/* Nombre de photos */}
                    <div className="absolute bottom-3 right-3">
                        <Badge variant="secondary" className="bg-black/70 text-white border-0">
                            <Image className="w-3 h-3 mr-1" />
                            {album.photos_count}
                        </Badge>
                    </div>
                </div>

                {/* Contenu de la carte */}
                <CardContent className="p-4">
                    <div className="flex items-start justify-between mb-2">
                        <h3 className="font-semibold text-lg text-gray-900 line-clamp-1 group-hover:text-orange-600 transition-colors">
                            {album.title}
                        </h3>
                        
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost" size="sm" className="h-8 w-8 p-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <MoreVertical className="w-4 h-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem>
                                    <Eye className="w-4 h-4 mr-2" />
                                    Voir l'album
                                </DropdownMenuItem>
                                {canCreateAlbum && (
                                    <>
                                        <DropdownMenuItem>
                                            <Edit className="w-4 h-4 mr-2" />
                                            Modifier
                                        </DropdownMenuItem>
                                        <DropdownMenuItem>
                                            <Upload className="w-4 h-4 mr-2" />
                                            Ajouter photos
                                        </DropdownMenuItem>
                                        <DropdownMenuItem>
                                            <Share2 className="w-4 h-4 mr-2" />
                                            Partager
                                        </DropdownMenuItem>
                                        <DropdownMenuItem className="text-red-600">
                                            <Trash2 className="w-4 h-4 mr-2" />
                                            Supprimer
                                        </DropdownMenuItem>
                                    </>
                                )}
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    {album.description && (
                        <p className="text-sm text-gray-600 mb-3 line-clamp-2">
                            {album.description}
                        </p>
                    )}

                    <div className="flex items-center justify-between text-xs text-gray-500">
                        <div className="flex items-center gap-1">
                            <Clock className="w-3 h-3" />
                            {formatDate(album.updated_at)}
                        </div>
                        
                        <div className="flex items-center gap-3">
                            <Button variant="ghost" size="sm" className="h-6 p-1 hover:text-red-500">
                                <Heart className="w-3 h-3" />
                            </Button>
                            <Button variant="ghost" size="sm" className="h-6 p-1 hover:text-blue-500">
                                <Share2 className="w-3 h-3" />
                            </Button>
                        </div>
                    </div>

                    {/* Actions principales */}
                    <div className="flex gap-2 mt-4">
                        <Link href={`/photo-albums/${album.id}`} className="flex-1">
                            <Button variant="outline" className="w-full h-9 text-sm border-gray-200 hover:border-orange-300 hover:bg-orange-50">
                                <Eye className="w-4 h-4 mr-2" />
                                Voir l'album
                            </Button>
                        </Link>

                        {canCreateAlbum && (
                            <Link href={`/photo-albums/${album.id}/photos/create`}>
                                <Button size="sm" className="h-9 w-9 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 p-0">
                                    <Plus className="w-4 h-4" />
                                </Button>
                            </Link>
                        )}
                    </div>
                </CardContent>
            </Card>
        );
    };

    return (
        <KwdDashboardLayout title="Albums Photo">
            <Head title="Albums Photo" />
            
            <div className="space-y-6">
                {/* Header moderne avec gradient */}
                <div className="bg-gradient-to-br from-orange-50 via-red-50 to-pink-50 rounded-2xl p-6 md:p-8 border border-orange-100 shadow-sm">
                    <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div>
                            <div className="flex items-center gap-3 mb-3">
                                <div className="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl flex items-center justify-center shadow-lg">
                                    <Camera className="w-6 h-6 text-white" />
                                </div>
                                <div>
                                    <h1 className="text-3xl md:text-4xl font-bold text-gray-900">
                                        Albums Photo
                                    </h1>
                                    {user.id !== canCreateAlbum && (
                                        <p className="text-lg text-gray-600">de {user.name}</p>
                                    )}
                                </div>
                            </div>
                            <p className="text-gray-600 max-w-2xl leading-relaxed">
                                Organisez, partagez et revivez vos plus beaux souvenirs en famille avec notre système d'albums moderne
                            </p>
                        </div>

                        {canCreateAlbum && (
                            <div className="flex flex-col sm:flex-row gap-3">
                                <Link href="/photo-albums/create">
                                    <Button className="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium shadow-lg hover:shadow-xl transition-all duration-200">
                                        <FolderPlus className="w-4 h-4 mr-2" />
                                        Créer un album
                                    </Button>
                                </Link>
                                
                                <Button variant="outline" className="border-orange-200 hover:bg-orange-50">
                                    <Upload className="w-4 h-4 mr-2" />
                                    Upload rapide
                                </Button>
                            </div>
                        )}
                    </div>
                </div>

                {/* Barre d'outils moderne */}
                <div className="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                    <div className="flex flex-col lg:flex-row lg:items-center gap-4">
                        {/* Recherche */}
                        <div className="relative flex-1 max-w-md">
                            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                            <Input
                                placeholder="Rechercher dans vos albums..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="pl-10 border-gray-200 focus:border-orange-300 focus:ring-orange-200"
                            />
                        </div>

                        {/* Filtres et tri */}
                        <div className="flex items-center gap-3">
                            <Select value={filterBy} onValueChange={setFilterBy}>
                                <SelectTrigger className="w-32">
                                    <Filter className="w-4 h-4 mr-2" />
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Tous</SelectItem>
                                    <SelectItem value="public">Public</SelectItem>
                                    <SelectItem value="family">Famille</SelectItem>
                                    <SelectItem value="private">Privé</SelectItem>
                                </SelectContent>
                            </Select>

                            <Select value={sortBy} onValueChange={setSortBy}>
                                <SelectTrigger className="w-36">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="recent">Plus récent</SelectItem>
                                    <SelectItem value="oldest">Plus ancien</SelectItem>
                                    <SelectItem value="name">Nom A-Z</SelectItem>
                                    <SelectItem value="photos">Nb. photos</SelectItem>
                                </SelectContent>
                            </Select>

                            {/* Toggle vue */}
                            <div className="flex border border-gray-200 rounded-lg p-1">
                                <Button
                                    variant={viewMode === 'grid' ? 'default' : 'ghost'}
                                    size="sm"
                                    onClick={() => setViewMode('grid')}
                                    className="h-8 w-8 p-0"
                                >
                                    <Grid3X3 className="w-4 h-4" />
                                </Button>
                                <Button
                                    variant={viewMode === 'list' ? 'default' : 'ghost'}
                                    size="sm"
                                    onClick={() => setViewMode('list')}
                                    className="h-8 w-8 p-0"
                                >
                                    <List className="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    </div>

                    {/* Statistiques */}
                    <div className="flex items-center gap-6 mt-4 pt-4 border-t border-gray-100">
                        <div className="flex items-center gap-2 text-sm text-gray-600">
                            <Image className="w-4 h-4" />
                            <span>{filteredAndSortedAlbums.length} album{filteredAndSortedAlbums.length > 1 ? 's' : ''}</span>
                        </div>
                        <div className="flex items-center gap-2 text-sm text-gray-600">
                            <Camera className="w-4 h-4" />
                            <span>{albums.reduce((total, album) => total + album.photos_count, 0)} photos</span>
                        </div>
                        {selectedAlbums.length > 0 && (
                            <div className="flex items-center gap-2 text-sm text-orange-600">
                                <span>{selectedAlbums.length} sélectionné{selectedAlbums.length > 1 ? 's' : ''}</span>
                            </div>
                        )}
                    </div>
                </div>

                {/* Grille d'albums */}
                {filteredAndSortedAlbums.length === 0 ? (
                    <div className="flex items-center justify-center min-h-[400px]">
                        <div className="text-center max-w-md mx-auto">
                            <div className="w-20 h-20 bg-gradient-to-br from-orange-500 to-red-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                                <Camera className="w-10 h-10 text-white" />
                            </div>
                            <h3 className="text-2xl font-bold text-gray-900 mb-4">
                                {searchQuery ? 'Aucun album trouvé' : 'Aucun album photo'}
                            </h3>
                            <p className="text-gray-600 mb-6">
                                {searchQuery 
                                    ? 'Essayez de modifier vos critères de recherche'
                                    : 'Commencez par créer votre premier album pour organiser vos souvenirs'
                                }
                            </p>
                            {canCreateAlbum && !searchQuery && (
                                <Link href="/photo-albums/create">
                                    <Button className="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white">
                                        <FolderPlus className="w-4 h-4 mr-2" />
                                        Créer mon premier album
                                    </Button>
                                </Link>
                            )}
                        </div>
                    </div>
                ) : (
                    <div className={`grid gap-6 ${
                        viewMode === 'grid' 
                            ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4' 
                            : 'grid-cols-1'
                    }`}>
                        {filteredAndSortedAlbums.map((album) => (
                            <AlbumCard key={album.id} album={album} />
                        ))}
                    </div>
                )}
            </div>
        </KwdDashboardLayout>
    );
}
