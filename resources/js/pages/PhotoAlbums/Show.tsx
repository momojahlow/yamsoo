import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { KwdDashboardLayout } from '@/layouts/modern';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogTrigger } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { 
  ArrowLeft, Plus, Search, Filter, Grid3X3, List, MoreVertical, 
  Edit, Trash2, Share2, Download, Heart, Calendar, User, 
  Camera, Image, Play, Pause, ZoomIn, ZoomOut, RotateCw,
  Globe, Users, Lock, Star, Upload, Eye, MapPin
} from 'lucide-react';

interface Photo {
    id: number;
    title?: string;
    description?: string;
    file_path: string;
    thumbnail_path?: string;
    width: number;
    height: number;
    file_size: number;
    taken_at: string;
    created_at: string;
}

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
    user: {
        id: number;
        name: string;
    };
}

interface Props {
    album: PhotoAlbum;
    photos: Photo[];
    canEdit: boolean;
}

const privacyConfig = {
    public: { icon: Globe, label: 'Public', color: 'bg-green-100 text-green-800' },
    family: { icon: Users, label: 'Famille', color: 'bg-orange-100 text-orange-800' },
    private: { icon: Lock, label: 'Privé', color: 'bg-red-100 text-red-800' },
};

export default function ShowPhotoAlbum({ album, photos, canEdit }: Props) {
    const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
    const [searchQuery, setSearchQuery] = useState('');
    const [sortBy, setSortBy] = useState('recent');
    const [selectedPhoto, setSelectedPhoto] = useState<Photo | null>(null);
    const [isSlideshow, setIsSlideshow] = useState(false);

    const privacy = privacyConfig[album.privacy];
    const PrivacyIcon = privacy.icon;

    // Filtrage et tri des photos
    const filteredAndSortedPhotos = photos
        .filter(photo => {
            if (!searchQuery) return true;
            return photo.title?.toLowerCase().includes(searchQuery.toLowerCase()) ||
                   photo.description?.toLowerCase().includes(searchQuery.toLowerCase());
        })
        .sort((a, b) => {
            switch (sortBy) {
                case 'recent':
                    return new Date(b.taken_at).getTime() - new Date(a.taken_at).getTime();
                case 'oldest':
                    return new Date(a.taken_at).getTime() - new Date(b.taken_at).getTime();
                case 'name':
                    return (a.title || '').localeCompare(b.title || '');
                default:
                    return 0;
            }
        });

    const formatFileSize = (bytes: number) => {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    };

    const PhotoCard = ({ photo }: { photo: Photo }) => (
        <Card className="group overflow-hidden hover:shadow-lg transition-all duration-300 cursor-pointer">
            <div 
                className="relative aspect-square bg-gray-100 overflow-hidden"
                onClick={() => setSelectedPhoto(photo)}
            >
                <img
                    src={photo.thumbnail_path || photo.file_path}
                    alt={photo.title || 'Photo'}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                />
                
                {/* Overlay avec actions */}
                <div className="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                    <div className="flex gap-2">
                        <Button size="sm" variant="secondary" className="bg-white/90 hover:bg-white">
                            <ZoomIn className="w-4 h-4" />
                        </Button>
                        <Button size="sm" variant="secondary" className="bg-white/90 hover:bg-white">
                            <Download className="w-4 h-4" />
                        </Button>
                    </div>
                </div>

                {/* Informations sur l'image */}
                <div className="absolute bottom-2 left-2 right-2">
                    <div className="bg-black/70 text-white text-xs px-2 py-1 rounded backdrop-blur-sm">
                        {photo.width} × {photo.height} • {formatFileSize(photo.file_size)}
                    </div>
                </div>
            </div>

            {photo.title && (
                <CardContent className="p-3">
                    <h3 className="font-medium text-sm text-gray-900 line-clamp-1">
                        {photo.title}
                    </h3>
                    <p className="text-xs text-gray-500 mt-1">
                        {formatDate(photo.taken_at)}
                    </p>
                </CardContent>
            )}
        </Card>
    );

    return (
        <KwdDashboardLayout title={album.title}>
            <Head title={`Album: ${album.title}`} />
            
            <div className="space-y-6">
                {/* Header de l'album */}
                <div className="bg-gradient-to-br from-orange-50 via-red-50 to-pink-50 rounded-2xl p-6 md:p-8 border border-orange-100 shadow-sm">
                    <div className="flex items-start gap-4 mb-6">
                        <Button variant="outline" asChild>
                            <Link href="/photo-albums" className="flex items-center gap-2">
                                <ArrowLeft className="w-4 h-4" />
                                Albums
                            </Link>
                        </Button>
                    </div>

                    <div className="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                        <div className="flex-1">
                            <div className="flex items-center gap-3 mb-4">
                                <div className="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl flex items-center justify-center shadow-lg">
                                    <Camera className="w-6 h-6 text-white" />
                                </div>
                                <div>
                                    <h1 className="text-3xl md:text-4xl font-bold text-gray-900">
                                        {album.title}
                                    </h1>
                                    <div className="flex items-center gap-3 mt-2">
                                        <Badge className={privacy.color}>
                                            <PrivacyIcon className="w-3 h-3 mr-1" />
                                            {privacy.label}
                                        </Badge>
                                        
                                        {album.is_default && (
                                            <Badge className="bg-yellow-100 text-yellow-800">
                                                <Star className="w-3 h-3 mr-1" />
                                                Défaut
                                            </Badge>
                                        )}
                                        
                                        <div className="flex items-center gap-1 text-sm text-gray-600">
                                            <User className="w-4 h-4" />
                                            {album.user.name}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {album.description && (
                                <p className="text-gray-600 max-w-2xl leading-relaxed mb-4">
                                    {album.description}
                                </p>
                            )}

                            <div className="flex items-center gap-6 text-sm text-gray-600">
                                <div className="flex items-center gap-1">
                                    <Image className="w-4 h-4" />
                                    {album.photos_count} photo{album.photos_count > 1 ? 's' : ''}
                                </div>
                                <div className="flex items-center gap-1">
                                    <Calendar className="w-4 h-4" />
                                    Créé le {formatDate(album.created_at)}
                                </div>
                            </div>
                        </div>

                        {/* Actions */}
                        <div className="flex flex-col sm:flex-row gap-3">
                            {canEdit && (
                                <>
                                    <Link href={`/photo-albums/${album.id}/photos/create`}>
                                        <Button className="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700">
                                            <Upload className="w-4 h-4 mr-2" />
                                            Ajouter photos
                                        </Button>
                                    </Link>
                                    
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button variant="outline">
                                                <MoreVertical className="w-4 h-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem>
                                                <Edit className="w-4 h-4 mr-2" />
                                                Modifier l'album
                                            </DropdownMenuItem>
                                            <DropdownMenuItem>
                                                <Share2 className="w-4 h-4 mr-2" />
                                                Partager
                                            </DropdownMenuItem>
                                            <DropdownMenuItem>
                                                <Download className="w-4 h-4 mr-2" />
                                                Télécharger tout
                                            </DropdownMenuItem>
                                            <DropdownMenuItem className="text-red-600">
                                                <Trash2 className="w-4 h-4 mr-2" />
                                                Supprimer l'album
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </>
                            )}
                            
                            <Button variant="outline">
                                <Share2 className="w-4 h-4 mr-2" />
                                Partager
                            </Button>
                        </div>
                    </div>
                </div>

                {/* Barre d'outils pour les photos */}
                <div className="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                    <div className="flex flex-col lg:flex-row lg:items-center gap-4">
                        {/* Recherche */}
                        <div className="relative flex-1 max-w-md">
                            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                            <Input
                                placeholder="Rechercher dans les photos..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="pl-10"
                            />
                        </div>

                        {/* Tri et vue */}
                        <div className="flex items-center gap-3">
                            <Select value={sortBy} onValueChange={setSortBy}>
                                <SelectTrigger className="w-36">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="recent">Plus récent</SelectItem>
                                    <SelectItem value="oldest">Plus ancien</SelectItem>
                                    <SelectItem value="name">Nom A-Z</SelectItem>
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

                            <Button
                                variant="outline"
                                onClick={() => setIsSlideshow(!isSlideshow)}
                                className="flex items-center gap-2"
                            >
                                {isSlideshow ? <Pause className="w-4 h-4" /> : <Play className="w-4 h-4" />}
                                Diaporama
                            </Button>
                        </div>
                    </div>

                    {/* Statistiques */}
                    <div className="flex items-center gap-6 mt-4 pt-4 border-t border-gray-100">
                        <div className="flex items-center gap-2 text-sm text-gray-600">
                            <Image className="w-4 h-4" />
                            <span>{filteredAndSortedPhotos.length} photo{filteredAndSortedPhotos.length > 1 ? 's' : ''}</span>
                        </div>
                        <div className="flex items-center gap-2 text-sm text-gray-600">
                            <Calendar className="w-4 h-4" />
                            <span>Dernière mise à jour: {formatDate(album.updated_at)}</span>
                        </div>
                    </div>
                </div>

                {/* Grille de photos */}
                {filteredAndSortedPhotos.length === 0 ? (
                    <div className="flex items-center justify-center min-h-[400px]">
                        <div className="text-center max-w-md mx-auto">
                            <div className="w-20 h-20 bg-gradient-to-br from-orange-500 to-red-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                                <Image className="w-10 h-10 text-white" />
                            </div>
                            <h3 className="text-2xl font-bold text-gray-900 mb-4">
                                {searchQuery ? 'Aucune photo trouvée' : 'Aucune photo dans cet album'}
                            </h3>
                            <p className="text-gray-600 mb-6">
                                {searchQuery 
                                    ? 'Essayez de modifier votre recherche'
                                    : 'Commencez par ajouter des photos à cet album'
                                }
                            </p>
                            {canEdit && !searchQuery && (
                                <Link href={`/photo-albums/${album.id}/photos/create`}>
                                    <Button className="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white">
                                        <Upload className="w-4 h-4 mr-2" />
                                        Ajouter des photos
                                    </Button>
                                </Link>
                            )}
                        </div>
                    </div>
                ) : (
                    <div className={`grid gap-4 ${
                        viewMode === 'grid' 
                            ? 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6' 
                            : 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3'
                    }`}>
                        {filteredAndSortedPhotos.map((photo) => (
                            <PhotoCard key={photo.id} photo={photo} />
                        ))}
                    </div>
                )}

                {/* Modal de visualisation de photo */}
                {selectedPhoto && (
                    <Dialog open={!!selectedPhoto} onOpenChange={() => setSelectedPhoto(null)}>
                        <DialogContent className="max-w-4xl max-h-[90vh] p-0">
                            <div className="relative">
                                <img
                                    src={selectedPhoto.file_path}
                                    alt={selectedPhoto.title || 'Photo'}
                                    className="w-full h-auto max-h-[80vh] object-contain"
                                />
                                
                                {/* Informations de la photo */}
                                <div className="p-6">
                                    <h3 className="text-xl font-bold text-gray-900 mb-2">
                                        {selectedPhoto.title || 'Sans titre'}
                                    </h3>
                                    {selectedPhoto.description && (
                                        <p className="text-gray-600 mb-4">{selectedPhoto.description}</p>
                                    )}
                                    <div className="grid grid-cols-2 gap-4 text-sm text-gray-600">
                                        <div>Dimensions: {selectedPhoto.width} × {selectedPhoto.height}</div>
                                        <div>Taille: {formatFileSize(selectedPhoto.file_size)}</div>
                                        <div>Prise le: {formatDate(selectedPhoto.taken_at)}</div>
                                        <div>Ajoutée le: {formatDate(selectedPhoto.created_at)}</div>
                                    </div>
                                </div>
                            </div>
                        </DialogContent>
                    </Dialog>
                )}
            </div>
        </KwdDashboardLayout>
    );
}
