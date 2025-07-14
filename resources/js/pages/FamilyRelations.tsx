import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import { SidebarProvider } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/app/Sidebar";
import { useIsMobile } from "@/hooks/use-mobile";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
  Users,
  Heart,
  Clock,
  CheckCircle,
  XCircle,
  Plus,
  Search,
  Filter,
  ArrowRight,
  UserPlus,
  MessageSquare
} from 'lucide-react';

interface FamilyRelation {
  id: number;
  user_id: number;
  related_user_id: number;
  relationship_type_id: number;
  status: string;
  created_at: string;
  user: {
    id: number;
    name: string;
    email: string;
    profile?: {
      avatar_url?: string;
      bio?: string;
      location?: string;
    };
  };
  relatedUser: {
    id: number;
    name: string;
    email: string;
    profile?: {
      avatar_url?: string;
      bio?: string;
      location?: string;
    };
  };
  relationshipType: {
    id: number;
    name: string;
    code: string;
  };
}

interface RelationshipRequest {
  id: number;
  requester_id: number;
  requested_user_id: number;
  relationship_type_id: number;
  message?: string;
  mother_name?: string;
  status: string;
  created_at: string;
  requester: {
    id: number;
    name: string;
    email: string;
  };
  relationshipType: {
    id: number;
    name: string;
    code: string;
  };
}



interface FamilyStats {
  total_relations: number;
  pending_requests: number;
  accepted_relations: number;
  family_members: number;
}

interface Props {
  relationships: FamilyRelation[];
  pendingRequests: RelationshipRequest[];
  familyStats: FamilyStats;
}

export default function FamilyRelations({
  relationships,
  pendingRequests,
  familyStats
}: Props) {
  const [searchTerm, setSearchTerm] = useState('');
  const [filterStatus] = useState('all');
  const [showAddRelation, setShowAddRelation] = useState(false);
  const isMobile = useIsMobile();

  // Add null/undefined checks
  const safeRelationships: FamilyRelation[] = Array.isArray(relationships)
    ? relationships.filter((r: unknown): r is FamilyRelation => {
        if (!r || typeof r !== 'object') return false;
        const rel = r as Partial<FamilyRelation>;
        return !!rel.relatedUser && typeof rel.relatedUser.name === 'string' && !!rel.relationshipType && typeof rel.relationshipType.name === 'string';
      })
    : [];
  const safePendingRequests: RelationshipRequest[] = Array.isArray(pendingRequests)
    ? pendingRequests.filter((r: unknown): r is RelationshipRequest => {
        if (!r || typeof r !== 'object') return false;
        const req = r as Partial<RelationshipRequest>;
        return !!req.requester && !!req.relationshipType && typeof req.relationshipType.name === 'string';
      })
    : [];
  const safeFamilyStats = familyStats || {
    total_relations: 0,
    pending_requests: 0,
    accepted_relations: 0,
    family_members: 0
  };

  // Debug log to inspect data structure
  console.log('safeRelationships', safeRelationships);

  const filteredRelationships = safeRelationships.filter((relation: FamilyRelation) => {
    const matchesSearch = relation.relatedUser.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         relation.relatedUser.email.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesFilter = filterStatus === 'all' || relation.status === filterStatus;
    return matchesSearch && matchesFilter;
  });

  const stats = [
    {
      title: "Relations totales",
      value: (safeFamilyStats.total_relations || 0).toString(),
      icon: Heart,
      color: "text-red-600",
      bgColor: "bg-red-50",
      change: "+2 ce mois"
    },
    {
      title: "Demandes en attente",
      value: (safeFamilyStats.pending_requests || 0).toString(),
      icon: Clock,
      color: "text-orange-600",
      bgColor: "bg-orange-50",
      change: "À traiter"
    },
    {
      title: "Relations acceptées",
      value: (safeFamilyStats.accepted_relations || 0).toString(),
      icon: CheckCircle,
      color: "text-green-600",
      bgColor: "bg-green-50",
      change: "Actives"
    },
    {
      title: "Membres de famille",
      value: (safeFamilyStats.family_members || 0).toString(),
      icon: Users,
      color: "text-blue-600",
      bgColor: "bg-blue-50",
      change: "Connectés"
    }
  ];

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full bg-gray-50 dark:bg-gray-900">
        <AppSidebar />
        <main className="flex-1 p-6 md:p-8 md:ml-16 pb-20 md:pb-8">
          <Head title="Relations Familiales" />

          <div className="max-w-7xl mx-auto">
            {/* Header */}
            <div className="mb-8">
              <div className="flex items-center justify-between">
                <div>
                  <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                    Relations Familiales
                  </h1>
                  <p className="text-gray-600 dark:text-gray-400 mt-1">
                    Gérez vos connexions familiales et vos demandes
                  </p>
                </div>
                <Button onClick={() => setShowAddRelation(true)} className="hidden md:flex">
                  <UserPlus className="w-4 h-4 mr-2" />
                  Nouvelle relation
                </Button>
              </div>
            </div>

            {/* Modal d'ajout de relation (placeholder) */}
            {showAddRelation && (
              <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
                <div className="bg-white rounded-lg shadow-lg p-8 max-w-md w-full relative">
                  <button
                    className="absolute top-2 right-2 text-gray-400 hover:text-gray-600"
                    onClick={() => setShowAddRelation(false)}
                  >
                    <XCircle className="w-6 h-6" />
                  </button>
                  <h2 className="text-xl font-bold mb-4">Ajouter une relation (à implémenter)</h2>
                  <p className="text-gray-500">Fonctionnalité à venir...</p>
                </div>
              </div>
            )}

            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
              {stats.map((stat, index) => (
                <Card key={index} className="border-0 shadow-sm hover:shadow-md transition-shadow">
                  <CardContent className="p-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-sm font-medium text-gray-600 dark:text-gray-400">
                          {stat.title}
                        </p>
                        <p className="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                          {stat.value}
                        </p>
                        <p className="text-xs text-gray-500 mt-1">
                          {stat.change}
                        </p>
                      </div>
                      <div className={`p-3 rounded-lg ${stat.bgColor}`}>
                        <stat.icon className={`w-6 h-6 ${stat.color}`} />
                      </div>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
              {/* Relations existantes */}
              <div className="lg:col-span-2">
                <Card className="border-0 shadow-sm">
                  <CardHeader>
                    <div className="flex items-center justify-between">
                      <CardTitle className="flex items-center">
                        <div className="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                          <Heart className="w-4 h-4 text-green-600" />
                        </div>
                        Mes Relations
                      </CardTitle>
                      <div className="flex items-center space-x-2">
                        <div className="relative">
                          <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                          <Input
                            type="text"
                            placeholder="Rechercher..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="pl-10 w-48"
                          />
                        </div>
                        <Button variant="outline" size="sm">
                          <Filter className="w-4 h-4 mr-2" />
                          {filterStatus === 'all' ? 'Tous' : filterStatus === 'accepted' ? 'Acceptées' : 'En attente'}
                        </Button>
                      </div>
                    </div>
                  </CardHeader>
                  <CardContent>
                    {filteredRelationships.length === 0 ? (
                      <div className="text-center py-8">
                        <Users className="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <p className="text-gray-500">Aucune relation trouvée</p>
                        <Button
                          variant="outline"
                          className="mt-4"
                          onClick={() => setShowAddRelation(true)}
                        >
                          <Plus className="w-4 h-4 mr-2" />
                          Ajouter une relation
                        </Button>
                      </div>
                    ) : (
                      <div className="space-y-4">
                        {filteredRelationships.map((relation: FamilyRelation) => (
                          <div key={relation.id} className="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <div className="flex items-center space-x-4">
                              <div className="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                                {relation.relatedUser.profile?.avatar_url ? (
                                  <img
                                    src={relation.relatedUser.profile.avatar_url}
                                    alt={relation.relatedUser.name || 'Avatar'}
                                    className="w-12 h-12 rounded-full object-cover"
                                  />
                                ) : (
                                  <span className="text-white font-semibold">
                                    {relation.relatedUser.name ? relation.relatedUser.name.charAt(0).toUpperCase() : '?'}
                                  </span>
                                )}
                              </div>
                              <div>
                                <h3 className="font-medium text-gray-900 dark:text-white">
                                  {relation.relatedUser.name || 'Nom inconnu'}
                                </h3>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                  {relation.relatedUser.email || 'Email inconnu'}
                                </p>
                                <div className="flex items-center mt-1">
                                  <Badge variant="secondary" className="mr-2">
                                    {relation.relationshipType.name || 'Relation'}
                                  </Badge>
                                  <Badge
                                    variant={relation.status === 'accepted' ? 'default' : 'outline'}
                                    className={relation.status === 'accepted' ? 'bg-green-100 text-green-800' : ''}
                                  >
                                    {relation.status === 'accepted' ? 'Acceptée' : 'En attente'}
                                  </Badge>
                                </div>
                              </div>
                            </div>
                            <div className="flex items-center space-x-2">
                              <Button variant="ghost" size="sm">
                                <MessageSquare className="w-4 h-4" />
                              </Button>
                              <Button variant="ghost" size="sm">
                                <ArrowRight className="w-4 h-4" />
                              </Button>
                            </div>
                          </div>
                        ))}
                      </div>
                    )}
                  </CardContent>
                </Card>
              </div>

              {/* Demandes en attente */}
              <div>
                <Card className="border-0 shadow-sm">
                  <CardHeader>
                    <CardTitle className="flex items-center justify-between">
                      <div className="flex items-center">
                        <div className="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                          <Clock className="w-4 h-4 text-orange-600" />
                        </div>
                        Demandes en attente
                      </div>
                      {safePendingRequests.length > 0 && (
                        <Badge variant="secondary">{safePendingRequests.length}</Badge>
                      )}
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    {safePendingRequests.length === 0 ? (
                      <div className="text-center py-8">
                        <CheckCircle className="w-12 h-12 text-green-400 mx-auto mb-4" />
                        <p className="text-gray-500">Aucune demande en attente</p>
                      </div>
                    ) : (
                      <div className="space-y-4">
                        {safePendingRequests.map((request) => (
                          <div key={request.id} className="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div className="flex items-start justify-between mb-3">
                              <div>
                                <h4 className="font-medium text-gray-900 dark:text-white">
                                  {request.requester.name}
                                </h4>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                  {request.requester.email}
                                </p>
                                <p className="text-sm mt-1">
                                  Souhaite être votre <Badge variant="outline" className="ml-1">{request.relationshipType.name}</Badge>
                                </p>
                              </div>
                            </div>
                            {request.message && (
                              <p className="text-sm text-gray-500 mb-3 italic">"{request.message}"</p>
                            )}
                            <div className="flex gap-2">
                              <Button size="sm" variant="outline" className="text-red-600 hover:text-red-700 flex-1">
                                <XCircle className="w-4 h-4 mr-1" />
                                Rejeter
                              </Button>
                              <Button size="sm" className="bg-green-600 hover:bg-green-700 flex-1">
                                <CheckCircle className="w-4 h-4 mr-1" />
                                Accepter
                              </Button>
                            </div>
                          </div>
                        ))}
                      </div>
                    )}
                  </CardContent>
                </Card>

                {/* Actions rapides */}
                <Card className="border-0 shadow-sm mt-6">
                  <CardHeader>
                    <CardTitle className="flex items-center">
                      <div className="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                        <Plus className="w-4 h-4 text-purple-600" />
                      </div>
                      Actions rapides
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-3">
                      <Button
                        variant="outline"
                        className="w-full justify-start"
                        onClick={() => setShowAddRelation(true)}
                      >
                        <UserPlus className="w-4 h-4 mr-2" />
                        Ajouter une relation
                      </Button>
                      <Button variant="outline" className="w-full justify-start">
                        <Search className="w-4 h-4 mr-2" />
                        Rechercher des membres
                      </Button>
                      <Button variant="outline" className="w-full justify-start">
                        <MessageSquare className="w-4 h-4 mr-2" />
                        Nouveau message
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              </div>
            </div>
          </div>
        </main>
        {isMobile && <MobileNavBar />}
      </div>
    </SidebarProvider>
  );
}
