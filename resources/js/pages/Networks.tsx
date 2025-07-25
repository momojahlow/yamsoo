import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { EmptyProfilesState } from '@/components/networks/EmptyProfilesState';
import { AddFamilyRelation } from '@/components/networks/AddFamilyRelation';
import YamsooButton from '@/components/YamsooButton';
import {
  Search,
  Users,
  UserPlus,
  Heart,
  Clock,
  CheckCircle,
  XCircle,
  MessageSquare,
  Globe
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { useToast } from '@/hooks/use-toast';
import { router } from '@inertiajs/react';

interface User {
  id: number;
  name: string;
  email: string;
  profile?: {
    avatar_url?: string;
    bio?: string;
    location?: string;
    gender?: string;
  };
}

interface Connection {
  id: number;
  user_id: number;
  connected_user_id: number;
  status: string;
  created_at: string;
  user: User;
  connected_user: User;
}

interface RelationshipType {
  id: number;
  code: string; // toujours string, valeur par défaut '' si absente
  name: string; // toujours string, valeur par défaut '' si absente
  name_fr: string;
  gender: string; // toujours string, valeur par défaut '' si absente
  requires_mother_name: boolean;
}

interface ExistingRelation {
  related_user_name: string;
  related_user_email: string;
  relationship_name: string;
  created_at: string;
}

interface PendingRequest {
  id: number;
  requester_name: string;
  requester_email: string;
  relationship_name: string;
  message?: string;
  mother_name?: string;
  created_at: string;
}

interface SentRequest {
  id: number;
  target_user_id: number;
  target_user_email: string;
  relationship_name: string;
  created_at: string;
}

interface Props {
  users: User[];
  connections: Connection[];
  relationshipTypes: RelationshipType[];
  existingRelations: ExistingRelation[];
  pendingRequests: PendingRequest[];
  sentRequests: SentRequest[];
  search?: string;
  familyMemberIds?: number[];
}

export default function Networks({
  users,
  connections,
  relationshipTypes,
  existingRelations,
  pendingRequests,
  sentRequests,
  search = '',
  familyMemberIds = [],
}: Props) {
  const [searchTerm, setSearchTerm] = useState(search);
  const [showAddRelation, setShowAddRelation] = useState(false);
  const { toast } = useToast();
  const safeUsers = users || [];
  const safeConnections = connections || [];
  const safeExistingRelations = existingRelations || [];
  const safePendingRequests = pendingRequests || [];
  const safeSentRequests = sentRequests || [];
  const safeRelationshipTypes = relationshipTypes || [];

  const filteredUsers = safeUsers.filter(user =>
    user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    user.email.toLowerCase().includes(searchTerm.toLowerCase())
  );

  // State for each card
  const [selectedRelations, setSelectedRelations] = useState<{ [userId: number]: string }>({});
  const [isSubmitting, setIsSubmitting] = useState<{ [userId: number]: boolean }>({});

  const handleSelectChange = (userId: number, value: string) => {
    setSelectedRelations((prev) => ({ ...prev, [userId]: value }));
  };

  // Scroll vers une section par id
  const scrollToSection = (sectionId: string) => {
    const el = document.getElementById(sectionId);
    if (el) el.scrollIntoView({ behavior: 'smooth' });
  };

  const handleSendRelation = (userId: number) => {
    const relationTypeId = selectedRelations[userId];
    if (!relationTypeId) return;
    setIsSubmitting((prev) => ({ ...prev, [userId]: true }));

    const user = users.find(u => u.id === userId);
    if (!user) return;

    router.post('/family-relations', {
      email: user.email,
      relationship_type_id: relationTypeId,
      message: '', // Peut être remplacé par un champ message si besoin
      mother_name: '', // Peut être remplacé par un champ si besoin
    }, {
      onSuccess: () => {
        setIsSubmitting((prev) => ({ ...prev, [userId]: false }));
        toast({ title: 'Invitation envoyée !', description: 'Votre demande de relation a bien été envoyée.' });
        router.reload({ only: ['pendingRequests', 'existingRelations', 'connections'] });
      },
      onError: () => {
        setIsSubmitting((prev) => ({ ...prev, [userId]: false }));
        toast({ title: 'Erreur', description: 'Impossible d\'envoyer la demande.' });
      }
    });
  };

  const handleAcceptRequest = (requestId: number) => {
    router.post(`/family-relations/${requestId}/accept`, {}, {
      onSuccess: () => {
        toast({ title: 'Demande acceptée !', description: 'La relation a été acceptée avec succès.' });
        router.reload({ only: ['pendingRequests', 'existingRelations', 'connections'] });
      },
      onError: () => {
        toast({ title: 'Erreur', description: 'Impossible d\'accepter la demande.' });
      }
    });
  };

  const handleRejectRequest = (requestId: number) => {
    router.post(`/family-relations/${requestId}/reject`, {}, {
      onSuccess: () => {
        toast({ title: 'Demande rejetée', description: 'La demande a été rejetée.' });
        router.reload({ only: ['pendingRequests', 'existingRelations', 'connections'] });
      },
      onError: () => {
        toast({ title: 'Erreur', description: 'Impossible de rejeter la demande.' });
      }
    });
  };

  const handleStartConversation = (userId: number) => {
    // Redirect to messages page with the user ID to start a conversation
    router.visit(`/messages?user=${userId}`);
  };

  if (safeUsers.length === 0) {
    return (
      <AppLayout>
        <div className="min-h-screen flex w-full bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
          <main className="flex-1 p-6 md:p-8 md:ml-16 pb-20 md:pb-8">
            <Head title="Réseaux" />
            <EmptyProfilesState />
          </main>
        </div>
      </AppLayout>
    );
  }

  return (
    <AppLayout>
      <div className="min-h-screen flex w-full bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
        <main className="flex-1 p-6 md:p-8 md:ml-16 pb-20 md:pb-8">
          <Head title="Réseaux" />

          <div className="max-w-7xl mx-auto">
            {/* Header moderne */}
            <div className="mb-12">
              <div className="flex items-center justify-between mb-8">
                <div>
                  <div className="flex items-center mb-4">
                    <div className="w-12 h-12 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                      <Users className="w-6 h-6 text-white" />
                    </div>
                    <div>
                      <h1 className="text-4xl font-bold text-gray-900 dark:text-white">
                        Réseaux
                      </h1>
                      <p className="text-gray-600 dark:text-gray-400 mt-1">
                        Découvrez et connectez-vous avec votre famille élargie
                      </p>
                    </div>
                  </div>
                </div>
                <Button
                  onClick={() => {
                    console.log('Bouton cliqué, showAddRelation:', !showAddRelation);
                    setShowAddRelation(true);
                  }}
                  className="hidden md:flex bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 shadow-lg"
                >
                  <UserPlus className="w-4 h-4 mr-2" />
                  Ajouter une relation
                </Button>
              </div>

              {/* Stats modernes */}
              <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <Card className="border-0 shadow-lg bg-gradient-to-br from-blue-500 to-blue-600 text-white cursor-pointer hover:scale-105 transition-transform" onClick={() => scrollToSection('relations-section')}>
                  <CardContent className="p-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-blue-100 text-sm font-medium">Relations</p>
                        <p className="text-3xl font-bold">{safeExistingRelations.length}</p>
                      </div>
                      <Heart className="w-8 h-8 text-blue-200" />
                    </div>
                  </CardContent>
                </Card>

                <Card className="border-0 shadow-lg bg-gradient-to-br from-green-500 to-green-600 text-white cursor-pointer hover:scale-105 transition-transform" onClick={() => scrollToSection('connections-section')}>
                  <CardContent className="p-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-green-100 text-sm font-medium">Connectés</p>
                        <p className="text-3xl font-bold">{safeConnections.length}</p>
                      </div>
                      <CheckCircle className="w-8 h-8 text-green-200" />
                    </div>
                  </CardContent>
                </Card>

                <Card className="border-0 shadow-lg bg-gradient-to-br from-orange-500 to-orange-600 text-white cursor-pointer hover:scale-105 transition-transform" onClick={() => scrollToSection('pending-section')}>
                  <CardContent className="p-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-orange-100 text-sm font-medium">Reçues</p>
                        <p className="text-3xl font-bold">{safePendingRequests.length}</p>
                      </div>
                      <Clock className="w-8 h-8 text-orange-200" />
                    </div>
                  </CardContent>
                </Card>

                <Card className="border-0 shadow-lg bg-gradient-to-br from-blue-500 to-blue-600 text-white cursor-pointer hover:scale-105 transition-transform" onClick={() => scrollToSection('sent-section')}>
                  <CardContent className="p-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-blue-100 text-sm font-medium">Envoyées</p>
                        <p className="text-3xl font-bold">{safeSentRequests.length}</p>
                      </div>
                      <Clock className="w-8 h-8 text-blue-200" />
                    </div>
                  </CardContent>
                </Card>

                <Card className="border-0 shadow-lg bg-gradient-to-br from-purple-500 to-purple-600 text-white cursor-pointer hover:scale-105 transition-transform" onClick={() => scrollToSection('discover-section')}>
                  <CardContent className="p-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-purple-100 text-sm font-medium">Découvertes</p>
                        <p className="text-3xl font-bold">{safeUsers.length}</p>
                      </div>
                      <Globe className="w-8 h-8 text-purple-200" />
                    </div>
                  </CardContent>
                </Card>
              </div>
            </div>

            {/* Section Relations Familiales */}
            <div className="mb-12">
              <div className="flex items-center justify-between mb-8">
                <div className="flex items-center">
                  <div className="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                    <Heart className="w-5 h-5 text-white" />
                  </div>
                  <div>
                    <h2 className="text-2xl font-bold text-gray-900 dark:text-white">Relations Familiales</h2>
                    <p className="text-gray-600 dark:text-gray-400">Vos liens familiaux établis</p>
                  </div>
                </div>
                <Button onClick={() => setShowAddRelation(true)} className="md:hidden">
                  <UserPlus className="w-4 h-4 mr-2" />
                  Ajouter
                </Button>
              </div>

              {/* Relations existantes */}
              {safeExistingRelations.length > 0 && (
                <div className="mb-8" id="relations-section">
                  <div className="flex items-center mb-6">
                    <div className="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-3">
                      <CheckCircle className="w-4 h-4 text-green-600" />
                    </div>
                    <h3 className="text-xl font-semibold text-gray-900 dark:text-white">Mes relations</h3>
                  </div>
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {safeExistingRelations.map((relation, index) => (
                      <Card key={index} className="border-0 shadow-sm hover:shadow-md transition-all duration-200 bg-white dark:bg-gray-800 h-fit">
                        <CardContent className="p-6">
                          <div className="flex items-start space-x-4">
                            <div className="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                              <span className="text-white font-semibold text-sm">
                                {relation.related_user_name.charAt(0).toUpperCase()}
                              </span>
                            </div>
                            <div className="flex-1 min-w-0">
                              <h3 className="font-semibold text-gray-900 dark:text-white truncate">
                                {relation.related_user_name}
                              </h3>
                              <p className="text-sm text-gray-600 dark:text-gray-400 truncate">
                                {relation.related_user_email}
                              </p>
                              <Badge className="bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 mt-2 inline-block">
                                {relation.relationship_name}
                              </Badge>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    ))}
                  </div>
                </div>
              )}

                            {/* Demandes en attente */}
              {safePendingRequests.length > 0 && (
                <div className="mb-8" id="pending-section">
                  <div className="flex items-center mb-6">
                    <div className="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mr-3">
                      <Clock className="w-4 h-4 text-orange-600" />
                    </div>
                    <h3 className="text-xl font-semibold text-gray-900 dark:text-white">Demandes reçues</h3>
                  </div>
                  <div className="space-y-4">
                    {safePendingRequests.map((request) => (
                      <Card key={request.id} className="border-0 shadow-lg bg-white dark:bg-gray-800">
                        <CardContent className="p-6">
                          <div className="flex items-center justify-between">
                            <div className="flex-1">
                              <div className="flex items-center mb-3">
                                <div className="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-full flex items-center justify-center mr-4">
                                  <span className="text-white font-semibold">
                                    {request.requester_name.charAt(0).toUpperCase()}
                                  </span>
                                </div>
                                <div>
                                  <p className="font-semibold text-gray-900 dark:text-white">{request.requester_name}</p>
                                  <p className="text-sm text-gray-600 dark:text-gray-400">{request.requester_email}</p>
                                </div>
                              </div>
                              <div className="ml-16">
                                <div className="text-sm mb-2">
                                  Vous a ajouté en tant que <Badge variant="outline" className="ml-1">{request.relationship_name}</Badge>
                                </div>
                                {request.message && (
                                  <p className="text-sm text-gray-600 dark:text-gray-400 italic mb-2">"{request.message}"</p>
                                )}
                                {request.mother_name && (
                                  <p className="text-sm text-gray-500 dark:text-gray-500">Nom de la mère : {request.mother_name}</p>
                                )}
                              </div>
                            </div>
                            <div className="flex gap-3 ml-6">
                              <Button
                                size="sm"
                                variant="outline"
                                className="text-red-600 hover:text-red-700 border-red-200 hover:border-red-300"
                                onClick={() => handleRejectRequest(request.id)}
                              >
                                <XCircle className="w-4 h-4 mr-1" />
                                Rejeter
                              </Button>
                              <Button
                                size="sm"
                                className="bg-green-600 hover:bg-green-700 shadow-md"
                                onClick={() => handleAcceptRequest(request.id)}
                              >
                                <CheckCircle className="w-4 h-4 mr-1" />
                                Accepter
                              </Button>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    ))}
                  </div>
                </div>
              )}

              {/* Demandes envoyées */}
              {safeSentRequests.length > 0 && (
                <div className="mb-8" id="sent-section">
                  <div className="flex items-center mb-6">
                    <div className="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-3">
                      <Clock className="w-4 h-4 text-blue-600" />
                    </div>
                    <h3 className="text-xl font-semibold text-gray-900 dark:text-white">Demandes envoyées</h3>
                  </div>
                  <div className="space-y-4">
                    {safeSentRequests.map((request) => (
                      <Card key={request.id} className="border-0 shadow-lg bg-white dark:bg-gray-800">
                        <CardContent className="p-6">
                          <div className="flex items-center justify-between">
                            <div className="flex-1">
                              <div className="flex items-center mb-3">
                                <div className="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mr-4">
                                  <span className="text-white font-semibold">
                                    {request.target_user_email.charAt(0).toUpperCase()}
                                  </span>
                                </div>
                                <div>
                                  <p className="font-semibold text-gray-900 dark:text-white">{request.target_user_email}</p>
                                  <p className="text-sm text-gray-600 dark:text-gray-400">En attente de réponse</p>
                                </div>
                              </div>
                              <div className="ml-16">
                                <div className="text-sm mb-2">
                                  Demande de relation en tant que <Badge variant="outline" className="ml-1">{request.relationship_name}</Badge>
                                </div>
                                <p className="text-xs text-gray-500 dark:text-gray-500">
                                  Envoyée le {new Date(request.created_at).toLocaleDateString('fr-FR')}
                                </p>
                              </div>
                            </div>
                            <div className="flex items-center ml-6">
                              <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                En attente
                              </Badge>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    ))}
                  </div>
                </div>
              )}
            </div>

            {/* Section Découverte */}
            <div className="mb-12" id="discover-section">
              <div className="flex items-center mb-8">
                <div className="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                  <Users className="w-5 h-5 text-white" />
                </div>
                <div>
                  <h2 className="text-2xl font-bold text-gray-900 dark:text-white">Découvrir des utilisateurs</h2>
                  <p className="text-gray-600 dark:text-gray-400">Trouvez et connectez-vous avec de nouveaux membres</p>
                </div>
              </div>

              {/* Barre de recherche moderne */}
              <div className="relative mb-8">
                <div className="max-w-md">
                  <Search className="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                  <Input
                    type="text"
                    placeholder="Rechercher des utilisateurs..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="pl-12 h-12 border-2 border-gray-200 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm"
                  />
                </div>
              </div>

              {/* Liste des utilisateurs moderne */}
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="connections-section">
                {filteredUsers.map((user) => {
                  const selectedRelation = selectedRelations[user.id] || "";
                  const submitting = isSubmitting[user.id] || false;

                  // Vérifier si une invitation a déjà été envoyée ou si la personne est déjà en famille
                  const isAlreadyFamily = familyMemberIds.includes(user.id);
                  const isExistingRelation = safeExistingRelations.some(rel => rel.related_user_email === user.email);
                  const isPending = safePendingRequests.some(req => req.requester_email === user.email);
                  const hasSentRequest = safeSentRequests.some(req => req.target_user_email === user.email);
                  const disableButton = isAlreadyFamily || isExistingRelation || isPending || hasSentRequest;

                  return (
                    <Card key={user.id} className="rounded-2xl shadow-md border border-gray-100 p-4 flex flex-col items-center relative">
                      {/* Top buttons - Yamsoo on left, Message on right */}
                      <div className="absolute top-4 left-4 right-4 flex justify-between z-10">
                        <YamsooButton
                          targetUserId={user.id}
                          targetUserName={user.name}
                          variant="outline"
                          size="sm"
                        />
                        <Button
                          variant="outline"
                          size="icon"
                          className="flex items-center justify-center h-8 w-8"
                          onClick={() => handleStartConversation(user.id)}
                          title="Démarrer une conversation"
                        >
                          <MessageSquare className="h-4 w-4" />
                        </Button>
                      </div>

                      <div className="flex flex-col items-center w-full mt-6">
                        <div className="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-3 overflow-hidden">
                          {user.profile?.avatar_url ? (
                            <img
                              src={user.profile.avatar_url}
                              alt={user.name}
                              className="w-14 h-14 object-cover"
                            />
                          ) : (
                            <span className="text-xl font-bold text-gray-500">
                              {user.name.split(' ').map(n => n[0]).join('').toUpperCase()}
                            </span>
                          )}
                        </div>
                        <div className="font-bold text-lg text-brown-800 mb-1 text-center">{user.name}</div>
                        <div className="text-sm text-gray-500 mb-3 text-center">{user.email}</div>
                      </div>
                      <div className="w-full mt-2">
                        <label className="block text-sm font-semibold mb-1">Ajoutez en tant que</label>
                        <Select value={selectedRelation} onValueChange={(value) => handleSelectChange(user.id, value)}>
                          <SelectTrigger className="w-full">
                            <SelectValue placeholder="Sélectionner une relation familiale" />
                          </SelectTrigger>
                          <SelectContent>
                            <div className="py-1.5 pl-2 text-xs font-semibold text-muted-foreground">Famille proche</div>
                            {safeRelationshipTypes.map((type) => (
                              <SelectItem key={type.id} value={type.id.toString()}>{type.name_fr}</SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                      <div className="w-full mt-4">
                        <Button
                          className="w-full bg-orange-200 hover:bg-orange-300 text-brown-800 font-semibold"
                          disabled={!selectedRelation || disableButton || submitting}
                          onClick={() => handleSendRelation(user.id)}
                        >
                          {isAlreadyFamily || isExistingRelation
                            ? "Déjà en famille"
                            : hasSentRequest
                              ? "Demande en cours"
                              : isPending
                                ? "Invitation reçue"
                                : "Demander une relation"}
                        </Button>
                      </div>
                    </Card>
                  );
                })}
              </div>

              {/* État vide pour la recherche */}
              {filteredUsers.length === 0 && searchTerm && (
                <div className="text-center py-12">
                  <div className="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Search className="w-8 h-8 text-gray-400" />
                  </div>
                  <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    Aucun utilisateur trouvé
                  </h3>
                  <p className="text-gray-600 dark:text-gray-400">
                    Essayez avec un autre terme de recherche
                  </p>
                </div>
              )}
            </div>

            {/* Dialog pour ajouter une relation */}
            {showAddRelation && (
              <AddFamilyRelation
                relationshipTypes={safeRelationshipTypes}
                onClose={() => setShowAddRelation(false)}
              />
            )}
          </div>
        </main>
      </div>
    </AppLayout>
  );
}
