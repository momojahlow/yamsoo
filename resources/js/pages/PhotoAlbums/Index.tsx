import { Head } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
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
    family: { icon: '👨‍👩‍👧‍👦', label: 'Famille', color: 'bg-orange-100 text-orange-800' },
    private: { icon: '🔒', label: 'Privé', color: 'bg-red-100 text-red-800' },
};

export default function PhotoAlbumsIndex({ albums, user, canCreateAlbum }: Props) {
    return (
        <AppSidebarLayout>
            <Head title="Albums Photo" />

            <div className="min-h-screen bg-gradient-to-br from-orange-50 via-white to-red-50">
                <div className="w-full max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-6 md:py-8 lg:py-12">
                    {/* En-tête responsive */}
                    <div className="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 sm:gap-6 mb-6 sm:mb-8 md:mb-12">
                        <div className="text-center sm:text-left">
                            <h1 className="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent leading-tight">
                                Albums Photo
                                {user.name !== 'Vous' && (
                                    <span className="block sm:inline text-sm sm:text-base md:text-lg font-normal text-gray-600 sm:ml-2 mt-1 sm:mt-0">
                                        de {user.name}
                                    </span>
                                )}
                            </h1>
                            <p className="text-gray-600 mt-2 sm:mt-3 text-xs sm:text-sm md:text-base max-w-2xl mx-auto sm:mx-0 leading-relaxed">
                                Gérez et partagez vos souvenirs en famille
                            </p>
                        </div>

                        {canCreateAlbum && (
                            <Link href="/photo-albums/create" className="w-full sm:w-auto">
                                <Button className="w-full sm:w-auto h-10 sm:h-11 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium transition-all duration-200 shadow-lg hover:shadow-xl text-sm">
                                    <Plus className="w-4 h-4 mr-2" />
                                    Créer un album
                                </Button>
                            </Link>
                        )}
                    </div>

                    {/* Liste des albums responsive */}
                    {albums.length === 0 ? (
                        <div className="flex items-center justify-center min-h-[60vh] px-4">
                            <div className="text-center max-w-md mx-auto">
                                <div className="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-orange-500 to-red-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                                    <Camera className="h-8 w-8 sm:h-10 sm:w-10 text-white" />
                                </div>
                                <h3 className="text-lg sm:text-xl md:text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent mb-4">
                                    Aucun album photo
                                </h3>
                                <p className="text-gray-600 mb-6 text-sm sm:text-base leading-relaxed">
                                    {canCreateAlbum
                                        ? "Créez votre premier album pour commencer à partager vos souvenirs !"
                                        : `${user.name} n'a pas encore d'albums photo visibles.`
                                    }
                                </p>
                                {canCreateAlbum && (
                                    <Link href="/photo-albums/create">
                                        <Button className="w-full sm:w-auto h-11 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium transition-all duration-200 shadow-lg hover:shadow-xl">
                                            <Plus className="h-4 w-4 mr-2" />
                                            Créer mon premier album
                                        </Button>
                                    </Link>
                                )}
                            </div>
                        </div>
                    ) : (
                        <>
                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 md:gap-6 mb-8">
                            {albums.map((album) => (
                                <Card key={album.id} className="group overflow-hidden hover:shadow-xl transition-all duration-300 border-0 shadow-sm bg-gradient-to-br from-white to-gray-50/50 h-full">
                                    {/* Photo de couverture responsive */}
                                    <div className="h-32 sm:h-40 md:h-48 bg-gradient-to-br from-gray-200 to-gray-300 relative overflow-hidden">
                                        {album.cover_photo ? (
                                            <img
                                                src={album.cover_photo}
                                                alt={`Couverture de ${album.title}`}
                                                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                            />
                                        ) : (
                                            <div className="w-full h-full flex items-center justify-center text-gray-400 bg-gradient-to-br from-orange-50 to-red-50">
                                                <div className="text-center">
                                                    <div className="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-full flex items-center justify-center mx-auto mb-2">
                                                        <Camera className="h-4 w-4 sm:h-5 sm:w-5 md:h-6 md:w-6 text-white" />
                                                    </div>
                                                    <div className="text-xs sm:text-sm text-gray-600">Aucune photo</div>
                                                </div>
                                            </div>
                                        )}

                                        {/* Badges responsive */}
                                        <div className="absolute top-1 sm:top-2 right-1 sm:right-2 flex flex-col gap-1">
                                            <Badge className={`${privacyConfig[album.privacy].color} text-xs px-2 py-0.5`}>
                                                <span className="hidden sm:inline">{privacyConfig[album.privacy].icon} {privacyConfig[album.privacy].label}</span>
                                                <span className="sm:hidden">{privacyConfig[album.privacy].icon}</span>
                                            </Badge>
                                            {album.is_default && (
                                                <Badge className="bg-yellow-100 text-yellow-800 text-xs px-2 py-0.5">
                                                    <span className="hidden sm:inline">⭐ Défaut</span>
                                                    <span className="sm:hidden">⭐</span>
                                                </Badge>
                                            )}
                                        </div>
                                    </div>

                                    <CardHeader className="p-3 sm:p-4 md:p-6 pb-2 sm:pb-3">
                                        <CardTitle className="text-sm sm:text-base md:text-lg font-semibold text-gray-900 truncate">{album.title}</CardTitle>
                                        {album.description && (
                                            <CardDescription className="line-clamp-2 text-xs sm:text-sm text-gray-600 leading-tight">
                                                {album.description}
                                            </CardDescription>
                                        )}
                                    </CardHeader>

                                    <CardContent className="p-3 sm:p-4 md:p-6 pt-0 flex flex-col h-full">
                                        <div className="flex items-center justify-between text-xs sm:text-sm text-gray-500 mb-3 sm:mb-4">
                                            <div className="flex items-center gap-1">
                                                <Camera className="h-3 w-3 sm:h-4 sm:w-4" />
                                                <span className="truncate">{album.photos_count} photo{album.photos_count > 1 ? 's' : ''}</span>
                                            </div>
                                            <div className="flex items-center gap-1">
                                                <Calendar className="h-3 w-3 sm:h-4 sm:w-4" />
                                                <span className="hidden sm:inline">{new Date(album.created_at).toLocaleDateString('fr-FR')}</span>
                                                <span className="sm:hidden">{new Date(album.created_at).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' })}</span>
                                            </div>
                                        </div>

                                        <div className="flex gap-2 mt-auto">
                                            <Link href={`/photo-albums/${album.id}`} className="flex-1">
                                                <Button variant="outline" className="w-full h-8 sm:h-9 md:h-10 text-xs sm:text-sm border-gray-200 hover:border-gray-300 hover:bg-gray-50">
                                                    <Eye className="h-3 w-3 sm:h-4 sm:w-4 mr-1 sm:mr-2" />
                                                    <span className="hidden sm:inline">Voir l'album</span>
                                                    <span className="sm:hidden">Voir</span>
                                                </Button>
                                            </Link>

                                            {canCreateAlbum && (
                                                <Link href={`/photo-albums/${album.id}/photos/create`}>
                                                    <Button size="sm" className="h-8 sm:h-9 md:h-10 w-8 sm:w-9 md:w-10 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 p-0">
                                                        <Plus className="h-3 w-3 sm:h-4 sm:w-4" />
                                                    </Button>
                                                </Link>
                                            )}
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>

                            {/* Statistiques responsive */}
                            <Card className="border-0 shadow-sm bg-gradient-to-br from-white to-gray-50/50">
                                <CardHeader className="p-4 sm:p-6">
                                    <CardTitle className="flex items-center gap-2 text-base sm:text-lg md:text-xl font-semibold text-gray-900">
                                        📊 Statistiques
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="p-4 sm:p-6 pt-0">
                                    <div className="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
                                        <div className="text-center p-3 sm:p-4 bg-orange-50 rounded-xl">
                                            <div className="text-xl sm:text-2xl md:text-3xl font-bold text-orange-600">{albums.length}</div>
                                            <div className="text-xs sm:text-sm text-gray-600 mt-1">Albums</div>
                                        </div>
                                        <div className="text-center p-3 sm:p-4 bg-green-50 rounded-xl">
                                            <div className="text-xl sm:text-2xl md:text-3xl font-bold text-green-600">
                                                {albums.reduce((sum, album) => sum + album.photos_count, 0)}
                                            </div>
                                            <div className="text-xs sm:text-sm text-gray-600 mt-1">Photos</div>
                                        </div>
                                        <div className="text-center p-3 sm:p-4 bg-purple-50 rounded-xl">
                                            <div className="text-xl sm:text-2xl md:text-3xl font-bold text-purple-600">
                                                {albums.filter(album => album.privacy === 'family').length}
                                            </div>
                                            <div className="text-xs sm:text-sm text-gray-600 mt-1">
                                                <span className="hidden sm:inline">Albums familiaux</span>
                                                <span className="sm:hidden">Familiaux</span>
                                            </div>
                                        </div>
                                        <div className="text-center p-3 sm:p-4 bg-red-50 rounded-xl">
                                            <div className="text-xl sm:text-2xl md:text-3xl font-bold text-red-600">
                                                {albums.filter(album => album.privacy === 'private').length}
                                            </div>
                                            <div className="text-xs sm:text-sm text-gray-600 mt-1">
                                                <span className="hidden sm:inline">Albums privés</span>
                                                <span className="sm:hidden">Privés</span>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </>
                    )}
                </div>
            </div>
        </AppSidebarLayout>
    );
}
