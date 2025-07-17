import { useState } from "react";
import { Head } from "@inertiajs/react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import AppLayout from '@/layouts/app-layout';
import {
  Heart,
  Users,
  CheckCircle,
  Clock,
  UserPlus,
  MessageSquare,
  Mail
} from "lucide-react";

interface RelationshipType {
  id: number;
  name: string;
  display_name: string;
}

interface User {
  id: number;
  name: string;
  email: string;
}

interface Profile {
  id: number;
  first_name?: string;
  last_name?: string;
  avatar_url?: string;
}

interface UserWithProfile extends User {
  profile?: Profile;
}

interface Relationship {
  id: number;
  user_id: number;
  related_user_id: number;
  relationship_type_id: number;
  status: string;
  created_at: string;
  user: UserWithProfile;
  relatedUser: UserWithProfile;
  relationshipType: RelationshipType;
}

interface PendingRequest {
  id: number;
  requester_id: number;
  target_user_id: number;
  relationship_type_id: number;
  status: string;
  message?: string;
  created_at: string;
  requester: UserWithProfile;
  targetUser: UserWithProfile;
  relationshipType: RelationshipType;
}

interface FamilyStats {
  totalRelations: number;
  pendingRequests: number;
  relationsByType: Record<string, number>;
}

interface FamilyRelationsProps {
  relationships: Relationship[];
  pendingRequests: PendingRequest[];
  relationshipTypes: RelationshipType[];
  familyStats: FamilyStats;
}

const FamilyRelations = ({ 
  relationships, 
  pendingRequests, 
  relationshipTypes, 
  familyStats 
}: FamilyRelationsProps) => {
  const [showAddRelation, setShowAddRelation] = useState(false);

  const getDisplayName = (user: UserWithProfile) => {
    if (user.profile?.first_name && user.profile?.last_name) {
      return `${user.profile.first_name} ${user.profile.last_name}`;
    }
    return user.name;
  };

  const getInitials = (user: UserWithProfile) => {
    const name = getDisplayName(user);
    const parts = name.split(' ');
    return parts.length > 1 
      ? `${parts[0][0]}${parts[1][0]}`.toUpperCase()
      : name.slice(0, 2).toUpperCase();
  };

  const getRelationBadgeColor = (relationType: string) => {
    switch (relationType.toLowerCase()) {
      case 'père':
      case 'mère':
        return 'bg-blue-100 text-blue-800';
      case 'fils':
      case 'fille':
        return 'bg-pink-100 text-pink-800';
      case 'frère':
      case 'sœur':
        return 'bg-green-100 text-green-800';
      case 'époux':
      case 'épouse':
        return 'bg-rose-100 text-rose-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const breadcrumbs = [
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'Relations Familiales', href: '/family-relations' }
  ];

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Relations Familiales" />
      
      <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div className="max-w-7xl mx-auto p-6 md:p-8">
          {/* Header */}
          <div className="mb-8">
            <div className="flex items-center justify-between">
              <div className="flex items-center">
                <div className="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                  <Heart className="w-6 h-6 text-white" />
                </div>
                <div>
                  <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                    Relations Familiales
                  </h1>
                  <p className="text-gray-600 dark:text-gray-400 mt-1">
                    Vos liens familiaux établis
                  </p>
                </div>
              </div>
              <Button onClick={() => setShowAddRelation(true)}>
                <UserPlus className="w-4 h-4 mr-2" />
                Ajouter une relation
              </Button>
            </div>
          </div>

          {/* Stats Cards */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <Card>
              <CardContent className="p-6">
                <div className="flex items-center">
                  <div className="p-2 bg-blue-100 rounded-lg">
                    <Users className="w-6 h-6 text-blue-600" />
                  </div>
                  <div className="ml-4">
                    <p className="text-sm font-medium text-gray-600">Total Relations</p>
                    <p className="text-2xl font-bold text-gray-900">{familyStats.totalRelations}</p>
                  </div>
                </div>
              </CardContent>
            </Card>
            
            <Card>
              <CardContent className="p-6">
                <div className="flex items-center">
                  <div className="p-2 bg-yellow-100 rounded-lg">
                    <Clock className="w-6 h-6 text-yellow-600" />
                  </div>
                  <div className="ml-4">
                    <p className="text-sm font-medium text-gray-600">En attente</p>
                    <p className="text-2xl font-bold text-gray-900">{familyStats.pendingRequests}</p>
                  </div>
                </div>
              </CardContent>
            </Card>
            
            <Card>
              <CardContent className="p-6">
                <div className="flex items-center">
                  <div className="p-2 bg-green-100 rounded-lg">
                    <CheckCircle className="w-6 h-6 text-green-600" />
                  </div>
                  <div className="ml-4">
                    <p className="text-sm font-medium text-gray-600">Acceptées</p>
                    <p className="text-2xl font-bold text-gray-900">{relationships.length}</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Relations existantes */}
          {relationships.length > 0 && (
            <div className="mb-8">
              <div className="flex items-center mb-6">
                <div className="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-3">
                  <CheckCircle className="w-4 h-4 text-green-600" />
                </div>
                <h2 className="text-xl font-semibold text-gray-900 dark:text-white">Mes relations</h2>
              </div>
              
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {relationships.map((relation) => {
                  // Déterminer qui est l'autre utilisateur
                  const otherUser = relation.user_id === relation.user.id 
                    ? relation.relatedUser 
                    : relation.user;
                  
                  return (
                    <Card key={relation.id} className="border-0 shadow-sm hover:shadow-md transition-all duration-200">
                      <CardContent className="p-6">
                        <div className="flex items-center space-x-4">
                          <Avatar className="w-12 h-12">
                            <AvatarImage src={otherUser.profile?.avatar_url || ''} />
                            <AvatarFallback className="bg-slate-100 text-slate-500">
                              {getInitials(otherUser)}
                            </AvatarFallback>
                          </Avatar>
                          
                          <div className="flex-1 min-w-0">
                            <h3 className="font-medium text-gray-900 truncate">
                              {getDisplayName(otherUser)}
                            </h3>
                            <p className="text-sm text-gray-500 truncate">
                              {otherUser.email}
                            </p>
                            <Badge 
                              className={`mt-2 ${getRelationBadgeColor(relation.relationshipType.display_name)}`}
                              variant="secondary"
                            >
                              {relation.relationshipType.display_name}
                            </Badge>
                          </div>
                          
                          <div className="flex flex-col space-y-2">
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={() => window.location.href = `/messagerie?selectedContactId=${otherUser.id}`}
                            >
                              <MessageSquare className="w-4 h-4" />
                            </Button>
                          </div>
                        </div>
                      </CardContent>
                    </Card>
                  );
                })}
              </div>
            </div>
          )}

          {/* Demandes en attente */}
          {pendingRequests.length > 0 && (
            <div className="mb-8">
              <div className="flex items-center mb-6">
                <div className="w-8 h-8 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center mr-3">
                  <Clock className="w-4 h-4 text-yellow-600" />
                </div>
                <h2 className="text-xl font-semibold text-gray-900 dark:text-white">Demandes en attente</h2>
              </div>
              
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {pendingRequests.map((request) => (
                  <Card key={request.id} className="border-0 shadow-sm">
                    <CardContent className="p-6">
                      <div className="flex items-center space-x-4">
                        <Avatar className="w-12 h-12">
                          <AvatarImage src={request.requester.profile?.avatar_url || ''} />
                          <AvatarFallback className="bg-slate-100 text-slate-500">
                            {getInitials(request.requester)}
                          </AvatarFallback>
                        </Avatar>
                        
                        <div className="flex-1 min-w-0">
                          <h3 className="font-medium text-gray-900 truncate">
                            {getDisplayName(request.requester)}
                          </h3>
                          <p className="text-sm text-gray-500 truncate">
                            {request.requester.email}
                          </p>
                          <Badge className="mt-2 bg-yellow-100 text-yellow-800" variant="secondary">
                            {request.relationshipType.display_name}
                          </Badge>
                        </div>
                      </div>
                      
                      {request.message && (
                        <div className="mt-4 p-3 bg-gray-50 rounded-lg">
                          <p className="text-sm text-gray-600">{request.message}</p>
                        </div>
                      )}
                      
                      <div className="flex space-x-2 mt-4">
                        <Button size="sm" className="flex-1">
                          Accepter
                        </Button>
                        <Button variant="outline" size="sm" className="flex-1">
                          Refuser
                        </Button>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            </div>
          )}

          {/* État vide */}
          {relationships.length === 0 && pendingRequests.length === 0 && (
            <div className="text-center py-12">
              <div className="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <Heart className="w-12 h-12 text-gray-400" />
              </div>
              <h3 className="text-xl font-semibold text-gray-900 mb-2">
                Aucune relation familiale
              </h3>
              <p className="text-gray-600 mb-6 max-w-md mx-auto">
                Commencez à construire votre réseau familial en ajoutant des relations.
              </p>
              <Button onClick={() => setShowAddRelation(true)}>
                <UserPlus className="w-4 h-4 mr-2" />
                Ajouter votre première relation
              </Button>
            </div>
          )}
        </div>
      </div>
    </AppLayout>
  );
};

export default FamilyRelations;
