
import React from 'react';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

interface User {
  id: number;
  name: string;
  email: string;
  created_at: string;
  profile?: {
    avatar_url?: string;
  };
}

interface Message {
  id: number;
  content: string;
  created_at: string;
  sender: User;
  receiver: User;
}

interface Stats {
  total_users: number;
  total_messages: number;
  total_families: number;
  total_notifications: number;
}

interface Props {
  stats: Stats;
  recentUsers: User[];
  recentMessages: Message[];
}

export default function Admin({ stats, recentUsers, recentMessages }: Props) {
  return (
    <>
      <Head title="Administration" />

      <div className="container mx-auto px-4 py-8">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
            Administration
          </h1>
          <p className="text-gray-600 dark:text-gray-400 mt-2">
            Tableau de bord d'administration
          </p>
        </div>

        {/* Statistiques */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Utilisateurs</CardTitle>
              <Badge variant="secondary">{stats.total_users}</Badge>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.total_users}</div>
              <p className="text-xs text-muted-foreground">
                Total des utilisateurs inscrits
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Messages</CardTitle>
              <Badge variant="secondary">{stats.total_messages}</Badge>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.total_messages}</div>
              <p className="text-xs text-muted-foreground">
                Total des messages échangés
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Familles</CardTitle>
              <Badge variant="secondary">{stats.total_families}</Badge>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.total_families}</div>
              <p className="text-xs text-muted-foreground">
                Total des relations familiales
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Notifications</CardTitle>
              <Badge variant="secondary">{stats.total_notifications}</Badge>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.total_notifications}</div>
              <p className="text-xs text-muted-foreground">
                Total des notifications
              </p>
            </CardContent>
          </Card>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {/* Utilisateurs récents */}
          <Card>
            <CardHeader>
              <CardTitle>Utilisateurs Récents</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {recentUsers.map((user) => (
                  <div key={user.id} className="flex items-center gap-4">
                    <div className="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                      {user.profile?.avatar_url ? (
                        <img
                          src={user.profile.avatar_url}
                          alt={user.name}
                          className="w-10 h-10 rounded-full object-cover"
                        />
                      ) : (
                        <span className="text-sm font-semibold text-gray-600">
                          {user.name.charAt(0).toUpperCase()}
                        </span>
                      )}
                    </div>
                    <div className="flex-1">
                      <p className="font-medium">{user.name}</p>
                      <p className="text-sm text-gray-600 dark:text-gray-400">
                        {user.email}
                      </p>
                      <p className="text-xs text-gray-500">
                        Inscrit le {new Date(user.created_at).toLocaleDateString()}
                      </p>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Messages récents */}
          <Card>
            <CardHeader>
              <CardTitle>Messages Récents</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {recentMessages.map((message) => (
                  <div key={message.id} className="border-l-4 border-blue-500 pl-4">
                    <div className="flex items-center gap-2 mb-2">
                      <span className="text-sm font-medium">
                        {message.sender.name}
                      </span>
                      <span className="text-gray-400">→</span>
                      <span className="text-sm font-medium">
                        {message.receiver.name}
                      </span>
                    </div>
                    <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">
                      {message.content.substring(0, 100)}
                      {message.content.length > 100 && '...'}
                    </p>
                    <p className="text-xs text-gray-500">
                      {new Date(message.created_at).toLocaleDateString()}
                    </p>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Actions d'administration */}
        <div className="mt-8">
          <Card>
            <CardHeader>
              <CardTitle>Actions d'Administration</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex gap-4">
                <Button variant="outline">
                  Gérer les Utilisateurs
                </Button>
                <Button variant="outline">
                  Gérer les Messages
                </Button>
                <Button variant="outline">
                  Gérer les Familles
                </Button>
                <Button variant="outline">
                  Exporter les Données
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </>
  );
}
