import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { KwdDashboardLayout } from '@/layouts/modern';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { 
  Camera, ArrowLeft, Save, Upload, Globe, Users, Lock, 
  Star, Image, FolderPlus, AlertCircle, CheckCircle
} from 'lucide-react';

export default function CreatePhotoAlbum() {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        description: '',
        privacy: 'family',
        is_default: false,
        cover_photo: null as File | null,
    });

    const [dragActive, setDragActive] = useState(false);
    const [previewUrl, setPreviewUrl] = useState<string | null>(null);

    const privacyOptions = [
        {
            value: 'public',
            label: 'Public',
            description: 'Visible par tous les utilisateurs',
            icon: Globe,
            color: 'text-green-600'
        },
        {
            value: 'family',
            label: 'Famille',
            description: 'Visible par votre famille uniquement',
            icon: Users,
            color: 'text-orange-600'
        },
        {
            value: 'private',
            label: 'Privé',
            description: 'Visible par vous uniquement',
            icon: Lock,
            color: 'text-red-600'
        }
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('photo-albums.store'));
    };

    const handleDrag = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        if (e.type === "dragenter" || e.type === "dragover") {
            setDragActive(true);
        } else if (e.type === "dragleave") {
            setDragActive(false);
        }
    };

    const handleDrop = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(false);

        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            handleFile(e.dataTransfer.files[0]);
        }
    };

    const handleFileInput = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
            handleFile(e.target.files[0]);
        }
    };

    const handleFile = (file: File) => {
        if (file.type.startsWith('image/')) {
            setData('cover_photo', file);
            const url = URL.createObjectURL(file);
            setPreviewUrl(url);
        }
    };

    const selectedPrivacy = privacyOptions.find(option => option.value === data.privacy);

    return (
        <KwdDashboardLayout title="Créer un album">
            <Head title="Créer un album photo" />
            
            <div className="max-w-4xl mx-auto space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Button variant="outline" asChild>
                        <a href="/photo-albums" className="flex items-center gap-2">
                            <ArrowLeft className="w-4 h-4" />
                            Retour aux albums
                        </a>
                    </Button>
                    
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900">Créer un nouvel album</h1>
                        <p className="text-gray-600">Organisez vos photos en créant un album personnalisé</p>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Formulaire principal */}
                        <div className="lg:col-span-2 space-y-6">
                            {/* Informations de base */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <FolderPlus className="w-5 h-5" />
                                        Informations de l'album
                                    </CardTitle>
                                    <CardDescription>
                                        Définissez le titre et la description de votre album
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div>
                                        <Label htmlFor="title">Titre de l'album *</Label>
                                        <Input
                                            id="title"
                                            value={data.title}
                                            onChange={(e) => setData('title', e.target.value)}
                                            placeholder="Ex: Vacances d'été 2024"
                                            className={errors.title ? 'border-red-300' : ''}
                                        />
                                        {errors.title && (
                                            <p className="text-sm text-red-600 mt-1 flex items-center gap-1">
                                                <AlertCircle className="w-4 h-4" />
                                                {errors.title}
                                            </p>
                                        )}
                                    </div>

                                    <div>
                                        <Label htmlFor="description">Description (optionnel)</Label>
                                        <Textarea
                                            id="description"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            placeholder="Décrivez votre album, les moments capturés..."
                                            rows={3}
                                            className={errors.description ? 'border-red-300' : ''}
                                        />
                                        {errors.description && (
                                            <p className="text-sm text-red-600 mt-1 flex items-center gap-1">
                                                <AlertCircle className="w-4 h-4" />
                                                {errors.description}
                                            </p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Photo de couverture */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Image className="w-5 h-5" />
                                        Photo de couverture
                                    </CardTitle>
                                    <CardDescription>
                                        Choisissez une image qui représente votre album (optionnel)
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div
                                        className={`border-2 border-dashed rounded-lg p-6 text-center transition-colors ${
                                            dragActive 
                                                ? 'border-orange-400 bg-orange-50' 
                                                : 'border-gray-300 hover:border-gray-400'
                                        }`}
                                        onDragEnter={handleDrag}
                                        onDragLeave={handleDrag}
                                        onDragOver={handleDrag}
                                        onDrop={handleDrop}
                                    >
                                        {previewUrl ? (
                                            <div className="space-y-4">
                                                <img
                                                    src={previewUrl}
                                                    alt="Aperçu"
                                                    className="w-32 h-32 object-cover rounded-lg mx-auto"
                                                />
                                                <div>
                                                    <p className="text-sm text-gray-600 mb-2">Photo sélectionnée</p>
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => {
                                                            setData('cover_photo', null);
                                                            setPreviewUrl(null);
                                                        }}
                                                    >
                                                        Changer la photo
                                                    </Button>
                                                </div>
                                            </div>
                                        ) : (
                                            <div className="space-y-4">
                                                <Upload className="w-12 h-12 text-gray-400 mx-auto" />
                                                <div>
                                                    <p className="text-lg font-medium text-gray-900">
                                                        Glissez une image ici
                                                    </p>
                                                    <p className="text-sm text-gray-600">
                                                        ou cliquez pour sélectionner un fichier
                                                    </p>
                                                </div>
                                                <input
                                                    type="file"
                                                    accept="image/*"
                                                    onChange={handleFileInput}
                                                    className="hidden"
                                                    id="cover-upload"
                                                />
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    onClick={() => document.getElementById('cover-upload')?.click()}
                                                >
                                                    Sélectionner une image
                                                </Button>
                                            </div>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Paramètres */}
                        <div className="space-y-6">
                            {/* Confidentialité */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Lock className="w-5 h-5" />
                                        Confidentialité
                                    </CardTitle>
                                    <CardDescription>
                                        Choisissez qui peut voir cet album
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    {privacyOptions.map((option) => {
                                        const Icon = option.icon;
                                        const isSelected = data.privacy === option.value;
                                        
                                        return (
                                            <div
                                                key={option.value}
                                                className={`p-3 rounded-lg border cursor-pointer transition-all ${
                                                    isSelected 
                                                        ? 'border-orange-300 bg-orange-50' 
                                                        : 'border-gray-200 hover:border-gray-300'
                                                }`}
                                                onClick={() => setData('privacy', option.value as any)}
                                            >
                                                <div className="flex items-start gap-3">
                                                    <Icon className={`w-5 h-5 mt-0.5 ${option.color}`} />
                                                    <div className="flex-1">
                                                        <div className="flex items-center gap-2">
                                                            <span className="font-medium text-gray-900">
                                                                {option.label}
                                                            </span>
                                                            {isSelected && (
                                                                <CheckCircle className="w-4 h-4 text-orange-600" />
                                                            )}
                                                        </div>
                                                        <p className="text-sm text-gray-600">
                                                            {option.description}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </CardContent>
                            </Card>

                            {/* Options avancées */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Star className="w-5 h-5" />
                                        Options
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <Label htmlFor="is_default" className="font-medium">
                                                Album par défaut
                                            </Label>
                                            <p className="text-sm text-gray-600">
                                                Les nouvelles photos seront ajoutées ici automatiquement
                                            </p>
                                        </div>
                                        <Switch
                                            id="is_default"
                                            checked={data.is_default}
                                            onCheckedChange={(checked) => setData('is_default', checked)}
                                        />
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Aperçu */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Aperçu</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        <div className="flex items-center gap-2">
                                            <span className="text-sm text-gray-600">Titre:</span>
                                            <span className="font-medium">
                                                {data.title || 'Sans titre'}
                                            </span>
                                        </div>
                                        
                                        <div className="flex items-center gap-2">
                                            <span className="text-sm text-gray-600">Confidentialité:</span>
                                            {selectedPrivacy && (
                                                <Badge className="bg-gray-100 text-gray-800">
                                                    <selectedPrivacy.icon className="w-3 h-3 mr-1" />
                                                    {selectedPrivacy.label}
                                                </Badge>
                                            )}
                                        </div>
                                        
                                        {data.is_default && (
                                            <div className="flex items-center gap-2">
                                                <Badge className="bg-yellow-100 text-yellow-800">
                                                    <Star className="w-3 h-3 mr-1" />
                                                    Album par défaut
                                                </Badge>
                                            </div>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    {/* Actions */}
                    <div className="flex items-center justify-between pt-6 border-t">
                        <Button variant="outline" asChild>
                            <a href="/photo-albums">Annuler</a>
                        </Button>
                        
                        <Button 
                            type="submit" 
                            disabled={processing || !data.title.trim()}
                            className="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600"
                        >
                            {processing ? (
                                <>
                                    <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2" />
                                    Création...
                                </>
                            ) : (
                                <>
                                    <Save className="w-4 h-4 mr-2" />
                                    Créer l'album
                                </>
                            )}
                        </Button>
                    </div>
                </form>
            </div>
        </KwdDashboardLayout>
    );
}
