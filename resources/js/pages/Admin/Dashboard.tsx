import React from 'react';
import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Users,
  MessageSquare,
  Image,
  Shield,
  Activity,
  TrendingUp,
  UserCheck,
  UserX,
  Clock,
  Database
} from 'lucide-react';

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  role_name: string;
  is_active: boolean;
  created_at: string;
  last_seen_at: string;
  profile?: any;
}

interface Stats {
  total_users: number;
  active_users: number;
  inactive_users: number;
  total_messages: number;
  total_conversations: number;
  total_families: number;
  total_photos: number;
  total_albums: number;
  total_relationships: number;
  pending_relationships: number;
}

interface Admin {
  id: number;
  name: string;
  email: string;
  role: string;
  role_name: string;
  permissions: string[];
}

interface Props {
  admin: Admin;
  stats: Stats;
  roleStats: Record<string, number>;
  recentActivity: {
    new_users_today: number;
    messages_today: number;
    photos_uploaded_today: number;
    active_users_today: number;
  };
  recentUsers: User[];
  onlineUsers: User[];
  monthlyStats: Array<{
    month: string;
    month_name: string;
    new_users: number;
    messages: number;
    photos: number;
  }>;
  topActiveUsers: User[];
}

export default function AdminDashboard({
  admin,
  stats,
  roleStats,
  recentActivity,
  recentUsers,
  onlineUsers,
  monthlyStats,
  topActiveUsers
}: Props) {
  const formatNumber = (num: number) => {
    return new Intl.NumberFormat('fr-FR').format(num);
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const getRoleBadgeColor = (role: string) => {
    switch (role) {
      case 'super_admin': return 'bg-purple-500';
      case 'admin': return 'bg-red-500';
      case 'moderator': return 'bg-orange-500';
      default: return 'bg-blue-500';
    }
  };

  return (
    <AdminLayout title="Tableau de bord" admin={admin}>
      <Head title="Administration - Tableau de bord" />
      
      <div className="p-6 space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900">Administration</h1>
            <p className="text-gray-600">Tableau de bord de gestion Yamsoo</p>
          </div>
          <div className="flex items-center space-x-2">
            <Badge variant="outline" className="text-green-600 border-green-600">
              <Activity className="w-4 h-4 mr-1" />
              Système opérationnel
            </Badge>
          </div>
        </div>

        {/* Statistiques principales */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Utilisateurs</CardTitle>
              <Users className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{formatNumber(stats.total_users)}</div>
              <p className="text-xs text-muted-foreground">
                <span className="text-green-600">{stats.active_users} actifs</span>
                {stats.inactive_users > 0 && (
                  <span className="text-red-600 ml-2">{stats.inactive_users} inactifs</span>
                )}
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Messages</CardTitle>
              <MessageSquare className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{formatNumber(stats.total_messages)}</div>
              <p className="text-xs text-muted-foreground">
                {formatNumber(stats.total_conversations)} conversations
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Photos</CardTitle>
              <Image className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{formatNumber(stats.total_photos)}</div>
              <p className="text-xs text-muted-foreground">
                {formatNumber(stats.total_albums)} albums
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Familles</CardTitle>
              <Shield className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{formatNumber(stats.total_families)}</div>
              <p className="text-xs text-muted-foreground">
                {formatNumber(stats.total_relationships)} relations
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Activité du jour */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center">
              <TrendingUp className="w-5 h-5 mr-2" />
              Activité d'aujourd'hui
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div className="text-center">
                <div className="text-2xl font-bold text-blue-600">
                  {recentActivity.new_users_today}
                </div>
                <div className="text-sm text-gray-600">Nouveaux utilisateurs</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold text-green-600">
                  {recentActivity.messages_today}
                </div>
                <div className="text-sm text-gray-600">Messages envoyés</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold text-purple-600">
                  {recentActivity.photos_uploaded_today}
                </div>
                <div className="text-sm text-gray-600">Photos uploadées</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold text-orange-600">
                  {recentActivity.active_users_today}
                </div>
                <div className="text-sm text-gray-600">Utilisateurs actifs</div>
              </div>
            </div>
          </CardContent>
        </Card>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Utilisateurs récents */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center justify-between">
                <span className="flex items-center">
                  <UserCheck className="w-5 h-5 mr-2" />
                  Utilisateurs récents
                </span>
                <Button variant="outline" size="sm">
                  Voir tout
                </Button>
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {recentUsers.map((user) => (
                  <div key={user.id} className="flex items-center justify-between">
                    <div className="flex items-center space-x-3">
                      <div className="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                        {user.name.charAt(0).toUpperCase()}
                      </div>
                      <div>
                        <div className="font-medium">{user.name}</div>
                        <div className="text-sm text-gray-500">{user.email}</div>
                      </div>
                    </div>
                    <div className="text-right">
                      <Badge className={getRoleBadgeColor(user.role)}>
                        {user.role_name}
                      </Badge>
                      <div className="text-xs text-gray-500 mt-1">
                        {formatDate(user.created_at)}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Utilisateurs en ligne */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center">
                <Clock className="w-5 h-5 mr-2" />
                Utilisateurs en ligne ({onlineUsers.length})
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-3 max-h-64 overflow-y-auto">
                {onlineUsers.map((user) => (
                  <div key={user.id} className="flex items-center justify-between">
                    <div className="flex items-center space-x-3">
                      <div className="relative">
                        <div className="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                          {user.name.charAt(0).toUpperCase()}
                        </div>
                        <div className="absolute -bottom-1 -right-1 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                      </div>
                      <div>
                        <div className="font-medium">{user.name}</div>
                        <div className="text-xs text-gray-500">
                          {formatDate(user.last_seen_at)}
                        </div>
                      </div>
                    </div>
                    <Badge variant="outline" className={getRoleBadgeColor(user.role)}>
                      {user.role_name}
                    </Badge>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Actions rapides */}
        <Card>
          <CardHeader>
            <CardTitle>Actions rapides</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              <Button className="h-20 flex flex-col items-center justify-center">
                <Users className="w-6 h-6 mb-2" />
                Gérer les utilisateurs
              </Button>
              <Button variant="outline" className="h-20 flex flex-col items-center justify-center">
                <MessageSquare className="w-6 h-6 mb-2" />
                Modérer les messages
              </Button>
              <Button variant="outline" className="h-20 flex flex-col items-center justify-center">
                <Image className="w-6 h-6 mb-2" />
                Gérer les photos
              </Button>
              <Button variant="outline" className="h-20 flex flex-col items-center justify-center">
                <Database className="w-6 h-6 mb-2" />
                Statistiques système
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}
