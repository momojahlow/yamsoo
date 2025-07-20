import { Head } from '@inertiajs/react';
import AppSidebarLayout from '@/Layouts/app/app-sidebar-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Camera, Plus, Eye, Settings, Calendar, User } from 'lucide-react';
import { Link } from '@inertiajs/react';

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
    public: { icon: '🌍', label: 'Public', color: 'bg-green-100 text-green-800' },
    family: { icon: '👨‍👩‍👧‍👦', label: 'Famille', color: 'bg-blue-100 text-blue-800' },
    private: { icon: '🔒', label: 'Privé', color: 'bg-red-100 text-red-800' },
};

export default function PhotoAlbumsIndex({ albums, user, canCreateAlbum }: Props) {
    return (
        <AppSidebarLayout>
            <Head title="Albums Photo" />
            
            <div className="container mx-auto px-6 py-8">
                {/* En-tête */}
                <div className="flex justify-between items-center mb-8">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900">
                            Albums Photo
                            {user.name !== 'Vous' && (
                                <span className="text-lg font-normal text-gray-600 ml-2">
                                    de {user.name}
                                </span>
                            )}
                        </h1>
                        <p className="text-gray-600 mt-2">
                            Gérez et partagez vos souvenirs en famille
                        </p>
                    </div>
                    
                    {canCreateAlbum && (
                        <Link href="/photo-albums/create">
                            <Button className="bg-blue-600 hover:bg-blue-700">
                                <Plus className="h-4 w-4 mr-2" />
                                Créer un album
                            </Button>
                        </Link>
                    )}
                </div>

                {/* Liste des albums */}
                {albums.length === 0 ? (
                    <div className="text-center py-12">
                        <Camera className="h-16 w-16 text-gray-400 mx-auto mb-4" />
                        <h3 className="text-xl font-semibold text-gray-700 mb-2">
                            Aucun album photo
                        </h3>
                        <p className="text-gray-500 mb-6">
                            {canCreateAlbum 
                                ? "Créez votre premier album pour commencer à partager vos souvenirs !"
                                : `${user.name} n'a pas encore d'albums photo visibles.`
                            }
                        </p>
                        {canCreateAlbum && (
                            <Link href="/photo-albums/create">
                                <Button className="bg-blue-600 hover:bg-blue-700">
                                    <Plus className="h-4 w-4 mr-2" />
                                    Créer mon premier album
                                </Button>
                            </Link>
                        )}
                    </div>
                ) : (
                    <>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                            {albums.map((album) => (
                                <Card key={album.id} className="overflow-hidden hover:shadow-lg transition-shadow duration-200">
                                    {/* Photo de couverture */}
                                    <div className="h-48 bg-gray-200 relative">
                                        {album.cover_photo ? (
                                            <img 
                                                src={album.cover_photo} 
                                                alt={`Couverture de ${album.title}`}
                                                className="w-full h-full object-cover"
                                            />
                                        ) : (
                                            <div className="w-full h-full flex items-center justify-center text-gray-400">
                                                <div className="text-center">
                                                    <Camera className="h-12 w-12 mx-auto mb-2" />
                                                    <div className="text-sm">Aucune photo</div>
                                                </div>
                                            </div>
                                        )}
                                        
                                        {/* Badges */}
                                        <div className="absolute top-2 right-2 flex flex-col gap-1">
                                            <Badge className={privacyConfig[album.privacy].color}>
                                                {privacyConfig[album.privacy].icon} {privacyConfig[album.privacy].label}
                                            </Badge>
                                            {album.is_default && (
                                                <Badge className="bg-yellow-100 text-yellow-800">
                                                    ⭐ Défaut
                                                </Badge>
                                            )}
                                        </div>
                                    </div>

                                    <CardHeader>
                                        <CardTitle className="text-lg">{album.title}</CardTitle>
                                        {album.description && (
                                            <CardDescription className="line-clamp-2">
                                                {album.description}
                                            </CardDescription>
                                        )}
                                    </CardHeader>

                                    <CardContent>
                                        <div className="flex items-center justify-between text-sm text-gray-500 mb-4">
                                            <div className="flex items-center gap-1">
                                                <Camera className="h-4 w-4" />
                                                <span>{album.photos_count} photo{album.photos_count > 1 ? 's' : ''}</span>
                                            </div>
                                            <div className="flex items-center gap-1">
                                                <Calendar className="h-4 w-4" />
                                                <span>{new Date(album.created_at).toLocaleDateString('fr-FR')}</span>
                                            </div>
                                        </div>

                                        <div className="flex gap-2">
                                            <Link href={`/photo-albums/${album.id}`} className="flex-1">
                                                <Button variant="outline" className="w-full">
                                                    <Eye className="h-4 w-4 mr-2" />
                                                    Voir l'album
                                                </Button>
                                            </Link>
                                            
                                            {canCreateAlbum && (
                                                <Link href={`/photo-albums/${album.id}/photos/create`}>
                                                    <Button size="sm" className="bg-green-600 hover:bg-green-700">
                                                        <Plus className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                            )}
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>

                        {/* Statistiques */}
                        <Card className="bg-gray-50">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    📊 Statistiques
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                                    <div>
                                        <div className="text-2xl font-bold text-blue-600">{albums.length}</div>
                                        <div className="text-sm text-gray-600">Albums</div>
                                    </div>
                                    <div>
                                        <div className="text-2xl font-bold text-green-600">
                                            {albums.reduce((sum, album) => sum + album.photos_count, 0)}
                                        </div>
                                        <div className="text-sm text-gray-600">Photos</div>
                                    </div>
                                    <div>
                                        <div className="text-2xl font-bold text-purple-600">
                                            {albums.filter(album => album.privacy === 'family').length}
                                        </div>
                                        <div className="text-sm text-gray-600">Albums familiaux</div>
                                    </div>
                                    <div>
                                        <div className="text-2xl font-bold text-red-600">
                                            {albums.filter(album => album.privacy === 'private').length}
                                        </div>
                                        <div className="text-sm text-gray-600">Albums privés</div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </>
                )}
            </div>
        </AppSidebarLayout>
    );
}
