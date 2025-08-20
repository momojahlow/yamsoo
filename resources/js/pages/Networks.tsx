import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { EmptyProfilesState } from '@/components/networks/EmptyProfilesState';
import { AddFamilyRelation } from '@/components/networks/AddFamilyRelation';
import YamsooButton from '@/components/YamsooButton';
import { useTranslation } from '@/hooks/useTranslation';
import { ConfirmDialog } from '@/components/ui/confirm-dialog';
import { ConfirmationDialog } from '@/components/ui/confirmation-dialog';
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
import { KwdDashboardLayout } from '@/Layouts/modern';
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
  name: string; // Nouveau nom principal
  display_name_fr: string;
  display_name_ar: string;
  display_name_en: string;
  name_fr: string; // Compatibilité
  category: string;
  generation_level: number;
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
  inverse_relationship_name?: string;
  message?: string;
  mother_name?: string;
  created_at: string;
  requester?: User;
  relationshipType?: RelationshipType;
}

interface SentRequest {
  id: number;
  target_user_id: number;
  target_user_name: string;
  target_user_email: string;
  relationship_name: string;
  created_at: string;
  targetUser?: User;
  relationshipType?: RelationshipType;
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
  const { t, isRTL } = useTranslation();
  const safeUsers = users || [];
  const safeConnections = connections || [];
  const safeExistingRelations = existingRelations || [];
  const safePendingRequests = pendingRequests || [];
  const safeSentRequests = sentRequests || [];
  const safeRelationshipTypes = relationshipTypes || [];

  // Fonction helper pour obtenir le nom localisé d'une relation
  const getLocalizedRelationName = (relationshipName: string) => {
    const relationType = safeRelationshipTypes.find(type =>
      type.name_fr === relationshipName ||
      type.display_name_fr === relationshipName ||
      type.name === relationshipName
    );

    if (relationType) {
      return isRTL ? (relationType.display_name_ar || relationType.name_ar) : (relationType.display_name_fr || relationType.name_fr);
    }

    return relationshipName; // Fallback au nom original
  };

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

  const handleCancelRequest = (requestId: number) => {
    router.delete(`/family-relations/${requestId}`, {
      onSuccess: () => {
        toast({ title: 'Demande annulée', description: 'La demande a été annulée avec succès.' });
        router.reload({ only: ['sentRequests', 'pendingRequests', 'existingRelations'] });
      },
      onError: () => {
        toast({ title: 'Erreur', description: 'Impossible d\'annuler la demande.' });
      }
    });
  };

  const handleStartConversation = (userId: number) => {
    // Redirect to messages page with the user ID to start a conversation
    router.visit(`/messages?user=${userId}`);
  };

  if (safeUsers.length === 0) {
    return (
      <KwdDashboardLayout title={t('networks')}>
        <Head title={t('networks')} />
        <EmptyProfilesState />
      </KwdDashboardLayout>
    );
  }

  return (
    <KwdDashboardLayout title={t('networks')}>
      <Head title={t('networks')} />

          <div className="max-w-7xl mx-auto">
            {/* Header moderne */}
            <div className="mb-12">
              <div className="flex items-center justify-between mb-8">
                <div>
                  <div className="flex items-center mb-4">
                    <div className="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl sm:rounded-2xl flex items-center justify-center mr-3 sm:mr-4 shadow-lg">
                      <Users className="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                    </div>
                    <div>
                      <h1 className="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white">
                        {t('networks')}
                      </h1>
                      <p className="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-1 sm:mt-2">
                        {t('discover_people')}
                      </p>
                    </div>
                  </div>
                </div>
                <Button
                  onClick={() => {
                    console.log('Bouton cliqué, showAddRelation:', !showAddRelation);
                    setShowAddRelation(true);
                  }}
                  className={`hidden sm:flex bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 shadow-lg items-center ${isRTL ? 'flex-row-reverse' : ''}`}
                >
                  <UserPlus className={`w-4 h-4 ${isRTL ? 'ml-2' : 'mr-2'}`} />
                  <span className="hidden md:inline">{t('add_relationship')}</span>
                  <span className="md:hidden">{t('add')}</span>
                </Button>
              </div>

              {/* Stats modernes responsive */}
              <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 lg:gap-6 mb-6 sm:mb-8">
                <Card className="border-0 shadow-lg bg-white/80 backdrop-blur-sm cursor-pointer hover:scale-105 transition-transform relative overflow-hidden" onClick={() => scrollToSection('relations-section')}>
                  <div className="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 to-red-500"></div>
                  <CardContent className="p-4 sm:p-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-gray-600 text-xs sm:text-sm font-medium">{t('relations')}</p>
                        <p className="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">{safeExistingRelations.length}</p>
                      </div>
                      <Heart className="w-6 h-6 sm:w-8 sm:h-8 text-orange-500" />
                    </div>
                  </CardContent>
                </Card>

                <Card className="border-0 shadow-lg bg-white/80 backdrop-blur-sm cursor-pointer hover:scale-105 transition-transform relative overflow-hidden" onClick={() => scrollToSection('connections-section')}>
                  <div className="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-green-500 to-green-600"></div>
                  <CardContent className="p-4 sm:p-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-gray-600 text-xs sm:text-sm font-medium">{t('connected')}</p>
                        <p className="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">{safeConnections.length}</p>
                      </div>
                      <CheckCircle className="w-6 h-6 sm:w-8 sm:h-8 text-green-500" />
                    </div>
                  </CardContent>
                </Card>

                <Card className="border-0 shadow-lg bg-white/80 backdrop-blur-sm cursor-pointer hover:scale-105 transition-transform relative overflow-hidden" onClick={() => scrollToSection('pending-section')}>
                  <div className="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-yellow-500 to-orange-500"></div>
                  <CardContent className="p-4 sm:p-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-gray-600 text-xs sm:text-sm font-medium">{t('received')}</p>
                        <p className="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">{safePendingRequests.length}</p>
                      </div>
                      <Clock className="w-6 h-6 sm:w-8 sm:h-8 text-yellow-500" />
                    </div>
                  </CardContent>
                </Card>

                <Card className="border-0 shadow-lg bg-white/80 backdrop-blur-sm cursor-pointer hover:scale-105 transition-transform relative overflow-hidden" onClick={() => scrollToSection('sent-section')}>
                  <div className="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
                  <CardContent className="p-4 sm:p-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-gray-600 text-xs sm:text-sm font-medium">{t('sent')}</p>
                        <p className="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">{safeSentRequests.length}</p>
                      </div>
                      <Clock className="w-6 h-6 sm:w-8 sm:h-8 text-blue-500" />
                    </div>
                  </CardContent>
                </Card>

                <Card className="border-0 shadow-lg bg-white/80 backdrop-blur-sm cursor-pointer hover:scale-105 transition-transform relative overflow-hidden" onClick={() => scrollToSection('discover-section')}>
                  <div className="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-pink-500"></div>
                  <CardContent className="p-4 sm:p-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-gray-600 text-xs sm:text-sm font-medium">{t('discoveries')}</p>
                        <p className="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">{safeUsers.length}</p>
                      </div>
                      <Globe className="w-6 h-6 sm:w-8 sm:h-8 text-purple-500" />
                    </div>
                  </CardContent>
                </Card>
              </div>
            </div>

            {/* Section Relations Familiales */}
            <div className="mb-12">
              <div className="flex items-center justify-between mb-8">
                <div className={`flex items-center ${isRTL ? 'flex-row-reverse' : ''}`}>
                  <div className={`w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg ${isRTL ? 'ml-4' : 'mr-4'}`}>
                    <Heart className="w-5 h-5 text-white" />
                  </div>
                  <div>
                    <h2 className="text-2xl font-bold text-gray-900 dark:text-white">{t('family_relations')}</h2>
                    <p className="text-gray-600 dark:text-gray-400">{t('your_established_family_links')}</p>
                  </div>
                </div>
                <Button onClick={() => setShowAddRelation(true)} className={`md:hidden flex items-center ${isRTL ? 'flex-row-reverse' : ''}`}>
                  <UserPlus className={`w-4 h-4 ${isRTL ? 'ml-2' : 'mr-2'}`} />
                  {t('add')}
                </Button>
              </div>

              {/* Relations existantes */}
              {safeExistingRelations.length > 0 && (
                <div className="mb-8" id="relations-section">
                  <div className={`flex items-center mb-6 ${isRTL ? 'flex-row-reverse' : ''}`}>
                    <div className={`w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center ${isRTL ? 'ml-3' : 'mr-3'}`}>
                      <CheckCircle className="w-4 h-4 text-green-600" />
                    </div>
                    <h3 className="text-xl font-semibold text-gray-900 dark:text-white">{t('my_relations')}</h3>
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
                              <Badge className="bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 mt-2 inline-block">
                                {getLocalizedRelationName(relation.relationship_name)}
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
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {safePendingRequests.map((request) => {
                      // Vérification de sécurité pour éviter les erreurs
                      const requester = request.requester || {};
                      const requesterName = requester.name || request.requester_name || 'Utilisateur inconnu';
                      const requesterEmail = requester.email || request.requester_email || '';

                      return (
                        <Card key={request.id} className="border-0 shadow-lg bg-white/80 backdrop-blur-sm hover:shadow-xl transition-all duration-200">
                          <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                              <div className="flex items-center space-x-3">
                                <Avatar className="w-10 h-10">
                                  <AvatarImage src={requester.profile?.avatar_url || ''} />
                                  <AvatarFallback className="bg-gray-100 text-gray-600">
                                    {requesterName.split(' ').map(n => n[0]).join('').toUpperCase()}
                                  </AvatarFallback>
                                </Avatar>

                                <div className="flex-1 min-w-0">
                                  <h3 className="font-medium text-gray-900 dark:text-white truncate">
                                    {requesterName}
                                  </h3>
                                  <Badge variant="outline" className="text-xs mt-1">
                                    {request.relationship_name}
                                  </Badge>
                                </div>
                              </div>

                              <div className="flex items-center gap-2">
                                <ConfirmationDialog
                                  title="Accepter la demande"
                                  description="Êtes-vous sûr de vouloir accepter cette demande de relation ?"
                                  confirmText="Oui, accepter"
                                  cancelText="Annuler"
                                  onConfirm={() => handleAcceptRequest(request.id)}
                                >
                                  <Button variant="outline" size="sm" className="text-green-600 border-green-200 hover:bg-green-50">
                                    <CheckCircle className="w-4 h-4" />
                                  </Button>
                                </ConfirmationDialog>

                                <ConfirmationDialog
                                  title="Rejeter la demande"
                                  description="Êtes-vous sûr de vouloir rejeter cette demande de relation ?"
                                  confirmText="Oui, rejeter"
                                  cancelText="Annuler"
                                  onConfirm={() => handleRejectRequest(request.id)}
                                  variant="destructive"
                                >
                                  <Button variant="outline" size="sm" className="text-red-600 border-red-200 hover:bg-red-50">
                                    <XCircle className="w-4 h-4" />
                                  </Button>
                                </ConfirmationDialog>
                              </div>
                            </div>

                            {request.message && (
                              <p className="text-xs text-gray-500 mt-2 truncate">
                                "{request.message}"
                              </p>
                            )}
                          </CardContent>
                        </Card>
                      );
                    })}
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
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {safeSentRequests.map((request) => {
                      // Vérification de sécurité pour éviter les erreurs
                      const targetUser = request.targetUser || request.target_user || {};
                      const targetName = targetUser.name || request.target_user_name || 'Utilisateur inconnu';
                      const targetEmail = targetUser.email || request.target_user_email || '';

                      return (
                        <Card key={request.id} className="border-0 shadow-lg bg-white/80 backdrop-blur-sm hover:shadow-xl transition-all duration-200">
                          <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                              <div className="flex items-center space-x-3">
                                <Avatar className="w-10 h-10">
                                  <AvatarImage src={targetUser.profile?.avatar_url || ''} />
                                  <AvatarFallback className="bg-gray-100 text-gray-600">
                                    {targetName.split(' ').map(n => n[0]).join('').toUpperCase()}
                                  </AvatarFallback>
                                </Avatar>

                                <div className="flex-1 min-w-0">
                                  <h3 className="font-medium text-gray-900 dark:text-white truncate">
                                    {targetName}
                                  </h3>
                                  <div className="flex items-center gap-2 mt-1">
                                    <Badge variant="outline" className="text-xs">
                                      {request.relationshipType?.display_name_fr || request.relationship_name || 'Relation'}
                                    </Badge>
                                    <Badge variant="secondary" className="text-xs">
                                      En attente
                                    </Badge>
                                  </div>
                                </div>
                              </div>

                              <div className="flex items-center gap-2">
                                <ConfirmationDialog
                                  title="Annuler la demande"
                                  description="Êtes-vous sûr de vouloir annuler cette demande de relation ?"
                                  confirmText="Oui, annuler"
                                  cancelText="Non, garder"
                                  onConfirm={() => handleCancelRequest(request.id)}
                                  variant="destructive"
                                >
                                  <Button variant="outline" size="sm" className="text-red-600 border-red-200 hover:bg-red-50">
                                    <XCircle className="w-4 h-4" />
                                  </Button>
                                </ConfirmationDialog>
                              </div>
                            </div>

                            {request.message && (
                              <p className="text-xs text-gray-500 mt-2 truncate">
                                "{request.message}"
                              </p>
                            )}
                          </CardContent>
                        </Card>
                      );
                    })}
                  </div>
                </div>
              )}
            </div>

            {/* Section Découverte */}
            <div className="mb-12" id="discover-section">
              <div className={`flex items-center mb-8 ${isRTL ? 'flex-row-reverse' : ''}`}>
                <div className={`w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg ${isRTL ? 'ml-4' : 'mr-4'}`}>
                  <Users className="w-5 h-5 text-white" />
                </div>
                <div>
                  <h2 className="text-2xl font-bold text-gray-900 dark:text-white">{t('discover_users')}</h2>
                  <p className="text-gray-600 dark:text-gray-400">{t('find_connect_new_members')}</p>
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
                    <Card key={user.id} className="border-0 shadow-lg bg-white/80 backdrop-blur-sm hover:shadow-xl transition-all duration-200">
                      <CardContent className="p-4">
                        <div className="flex items-center justify-between flex-wrap gap-3 mb-3">
                          <div className="flex items-center space-x-3 flex-nowrap min-w-0">
                            <Avatar className="w-10 h-10 flex-shrink-0">
                              <AvatarImage src={user.profile?.avatar_url || ''} />
                              <AvatarFallback className="bg-gray-100 text-gray-600">
                                {user.name.split(' ').map(n => n[0]).join('').toUpperCase()}
                              </AvatarFallback>
                            </Avatar>
                            <div className="min-w-0">
                              <h3 className="font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                {user.name}
                              </h3>
                            </div>
                          </div>

                          <div className="flex items-center gap-1 flex-nowrap">
                            <YamsooButton
                              targetUserId={user.id}
                              targetUserName={user.name}
                              variant="outline"
                              size="sm"
                            />
                            <Button
                              variant="outline"
                              size="sm"
                              className="text-blue-600 border-blue-200 hover:bg-blue-50"
                              onClick={() => handleStartConversation(user.id)}
                              title="Démarrer une conversation"
                            >
                              <MessageSquare className="h-4 w-4" />
                            </Button>
                          </div>
                        </div>

                        <div className="mt-3">
                          <label className="block text-sm font-medium text-gray-700 mb-2">{t('add_as')} :</label>
                        {(() => {
                          // Vérifier si une invitation existe déjà avec cet utilisateur
                          const hasExistingRequest = sentRequests.some(req => req.target_user_id === user.id) ||
                                                   pendingRequests.some(req => req.requester_email === user.email);

                          return (
                            <Select
                              value={selectedRelation}
                              onValueChange={(value) => handleSelectChange(user.id, value)}
                              disabled={hasExistingRequest}
                            >
                              <SelectTrigger className={`w-full ${hasExistingRequest ? 'opacity-50 cursor-not-allowed' : ''}`}>
                                <SelectValue placeholder={hasExistingRequest ? 'Invitation en cours...' : t('select_family_relation')} />
                              </SelectTrigger>
                              <SelectContent>
                                <div className={`py-1.5 text-xs font-semibold text-muted-foreground ${isRTL ? 'pr-2' : 'pl-2'}`}>{t('close_family')}</div>
                                {safeRelationshipTypes.map((type) => (
                                  <SelectItem key={type.id} value={type.id.toString()}>
                                    {isRTL ? type.display_name_ar || type.name_ar : type.display_name_fr || type.name_fr}
                                  </SelectItem>
                                ))}
                              </SelectContent>
                            </Select>
                          );
                        })()}
                      </div>
                      <div className="w-full mt-4">
                        <Button
                          className="w-full bg-orange-200 hover:bg-orange-300 text-brown-800 font-semibold"
                          disabled={!selectedRelation || disableButton || submitting}
                          onClick={() => handleSendRelation(user.id)}
                        >
                          {isAlreadyFamily || isExistingRelation
                            ? t('already_family')
                            : hasSentRequest
                              ? t('request_pending')
                              : isPending
                                ? t('invitation_received')
                                : t('request_relation')}
                        </Button>
                        </div>
                      </CardContent>
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
    </KwdDashboardLayout>
  );
}
