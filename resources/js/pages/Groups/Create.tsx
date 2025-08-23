import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Users, Plus, X } from 'lucide-react';
import { KwdDashboardLayout } from '@/Layouts/modern';

interface Contact {
    id: number;
    name: string;
    avatar?: string;
    relation: string;
}

interface CreateGroupProps {
    contacts: Contact[];
}

export default function CreateGroup({ contacts }: CreateGroupProps) {
    const [selectedParticipants, setSelectedParticipants] = useState<Contact[]>([]);
    
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        description: '',
        participants: [] as number[],
    });

    const handleParticipantToggle = (contact: Contact) => {
        const isSelected = selectedParticipants.some(p => p.id === contact.id);
        
        if (isSelected) {
            const newSelected = selectedParticipants.filter(p => p.id !== contact.id);
            setSelectedParticipants(newSelected);
            setData('participants', newSelected.map(p => p.id));
        } else {
            const newSelected = [...selectedParticipants, contact];
            setSelectedParticipants(newSelected);
            setData('participants', newSelected.map(p => p.id));
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/groups');
    };

    const getInitials = (name: string) => {
        const nameParts = name.split(' ');
        return nameParts.length > 1
            ? `${nameParts[0][0]}${nameParts[1][0]}`.toUpperCase()
            : name.slice(0, 2).toUpperCase();
    };

    return (
        <KwdDashboardLayout title="Créer un groupe">
            <Head title="Créer un groupe" />
            
            <div className="min-h-screen bg-gray-50 p-6">
                <div className="max-w-4xl mx-auto">
                    {/* En-tête */}
                    <div className="flex items-center gap-4 mb-6">
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => window.history.back()}
                            className="p-2"
                        >
                            <ArrowLeft className="w-5 h-5" />
                        </Button>
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">Créer un groupe</h1>
                            <p className="text-gray-600">Créez un groupe de discussion avec vos proches</p>
                        </div>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Informations du groupe */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Users className="w-5 h-5" />
                                    Informations du groupe
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium mb-2">
                                        Nom du groupe *
                                    </label>
                                    <Input
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="Ex: Famille Martin, Amis du lycée..."
                                        className="w-full"
                                        required
                                    />
                                    {errors.name && (
                                        <p className="text-red-600 text-sm mt-1">{errors.name}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium mb-2">
                                        Description (optionnel)
                                    </label>
                                    <Textarea
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        placeholder="Décrivez brièvement le groupe..."
                                        className="w-full"
                                        rows={3}
                                    />
                                    {errors.description && (
                                        <p className="text-red-600 text-sm mt-1">{errors.description}</p>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Participants sélectionnés */}
                        {selectedParticipants.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>
                                        Participants sélectionnés ({selectedParticipants.length})
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex flex-wrap gap-2">
                                        {selectedParticipants.map(participant => (
                                            <Badge
                                                key={participant.id}
                                                variant="secondary"
                                                className="flex items-center gap-2 px-3 py-2"
                                            >
                                                <Avatar className="w-6 h-6">
                                                    <AvatarImage src={participant.avatar} />
                                                    <AvatarFallback className="text-xs">
                                                        {getInitials(participant.name)}
                                                    </AvatarFallback>
                                                </Avatar>
                                                <span>{participant.name}</span>
                                                <button
                                                    type="button"
                                                    onClick={() => handleParticipantToggle(participant)}
                                                    className="ml-1 hover:bg-gray-200 rounded-full p-1"
                                                >
                                                    <X className="w-3 h-3" />
                                                </button>
                                            </Badge>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Liste des contacts */}
                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    Ajouter des participants
                                </CardTitle>
                                <p className="text-sm text-gray-600">
                                    Sélectionnez les personnes que vous souhaitez ajouter au groupe
                                </p>
                            </CardHeader>
                            <CardContent>
                                {contacts.length === 0 ? (
                                    <div className="text-center py-8 text-gray-500">
                                        <Users className="w-12 h-12 mx-auto mb-4 text-gray-400" />
                                        <p>Aucun contact disponible</p>
                                        <p className="text-sm">Ajoutez des relations familiales pour créer des groupes</p>
                                    </div>
                                ) : (
                                    <div className="space-y-2">
                                        {contacts.map(contact => {
                                            const isSelected = selectedParticipants.some(p => p.id === contact.id);
                                            
                                            return (
                                                <div
                                                    key={contact.id}
                                                    onClick={() => handleParticipantToggle(contact)}
                                                    className={`flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-colors ${
                                                        isSelected 
                                                            ? 'bg-blue-50 border-2 border-blue-200' 
                                                            : 'hover:bg-gray-50 border-2 border-transparent'
                                                    }`}
                                                >
                                                    <Avatar className="w-10 h-10">
                                                        <AvatarImage src={contact.avatar} />
                                                        <AvatarFallback>
                                                            {getInitials(contact.name)}
                                                        </AvatarFallback>
                                                    </Avatar>
                                                    
                                                    <div className="flex-1">
                                                        <p className="font-medium">{contact.name}</p>
                                                        <p className="text-sm text-gray-500">{contact.relation}</p>
                                                    </div>
                                                    
                                                    {isSelected && (
                                                        <div className="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
                                                            <Plus className="w-4 h-4 text-white rotate-45" />
                                                        </div>
                                                    )}
                                                </div>
                                            );
                                        })}
                                    </div>
                                )}
                                
                                {errors.participants && (
                                    <p className="text-red-600 text-sm mt-2">{errors.participants}</p>
                                )}
                            </CardContent>
                        </Card>

                        {/* Boutons d'action */}
                        <div className="flex gap-4">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => window.history.back()}
                                className="flex-1"
                            >
                                Annuler
                            </Button>
                            <Button
                                type="submit"
                                disabled={processing || !data.name.trim() || selectedParticipants.length === 0}
                                className="flex-1 bg-blue-500 hover:bg-blue-600"
                            >
                                {processing ? 'Création...' : 'Créer le groupe'}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </KwdDashboardLayout>
    );
}
