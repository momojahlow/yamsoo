import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { EmptyProfilesState } from '@/components/networks/EmptyProfilesState';
import { AddFamilyRelation } from '@/components/networks/AddFamilyRelation';

interface User {
  id: number;
  name: string;
  email: string;
  profile?: {
    avatar_url?: string;
    bio?: string;
    location?: string;
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

interface Props {
  users: User[];
  connections: Connection[];
  search?: string;
}

export default function Networks({ users, connections, search = '' }: Props) {
  const [searchTerm, setSearchTerm] = useState(search);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);

  const filteredUsers = users.filter(user =>
    user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    user.email.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const isConnected = (userId: number) => {
    return connections.some(conn =>
      (conn.user_id === userId || conn.connected_user_id === userId)
    );
  };

  if (users.length === 0) {
    return (
      <>
        <Head title="Réseaux" />
        <EmptyProfilesState />
      </>
    );
  }

  return (
    <>
      <Head title="Réseaux" />

      <div className="container mx-auto px-4 py-8">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
            Réseaux
          </h1>
          <p className="text-gray-600 dark:text-gray-400 mt-2">
            Découvrez et connectez-vous avec d'autres utilisateurs
          </p>
        </div>

        {/* Barre de recherche */}
        <div className="mb-6">
          <Input
            type="text"
            placeholder="Rechercher des utilisateurs..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="max-w-md"
          />
        </div>

        {/* Liste des utilisateurs */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredUsers.map((user) => (
            <Card key={user.id} className="hover:shadow-lg transition-shadow">
              <CardHeader>
                <div className="flex items-center gap-4">
                  <div className="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center">
                    {user.profile?.avatar_url ? (
                      <img
                        src={user.profile.avatar_url}
                        alt={user.name}
                        className="w-16 h-16 rounded-full object-cover"
                      />
                    ) : (
                      <span className="text-2xl font-semibold text-gray-600">
                        {user.name.charAt(0).toUpperCase()}
                      </span>
                    )}
                  </div>
                  <div className="flex-1">
                    <CardTitle className="text-lg">{user.name}</CardTitle>
                    <p className="text-sm text-gray-600 dark:text-gray-400">
                      {user.email}
                    </p>
                    {user.profile?.location && (
                      <p className="text-sm text-gray-500">
                        📍 {user.profile.location}
                      </p>
                    )}
                  </div>
                </div>
              </CardHeader>
              <CardContent>
                {user.profile?.bio && (
                  <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {user.profile.bio}
                  </p>
                )}

                <div className="flex items-center justify-between">
                  {isConnected(user.id) ? (
                    <Badge variant="success">Connecté</Badge>
                  ) : (
                    <Button
                      onClick={() => setSelectedUser(user)}
                      size="sm"
                    >
                      Se connecter
                    </Button>
                  )}
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        {/* Dialog pour ajouter une relation */}
        {selectedUser && (
          <AddFamilyRelation
            user={selectedUser}
            onClose={() => setSelectedUser(null)}
          />
        )}
      </div>
    </>
  );
}
