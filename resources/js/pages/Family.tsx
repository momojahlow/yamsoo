
import React from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Users, Plus, TreePine, MessageSquare, UserPlus } from "lucide-react";
import AppLayout from '@/layouts/app-layout';
import { FamilyMemberCard } from "@/components/family/FamilyMemberCard";

interface Member {
  id: number;
  name: string;
  email: string;
  avatar?: string | null;
  bio?: string | null;
  birth_date?: string | null;
  gender?: string | null;
  phone?: string | null;
  relation: string;
  status: string;
}

interface FamilyProps {
  members: Member[];
}

export default function Family({ members }: FamilyProps) {
  console.log('members', members);

  if (!members || members.length === 0) {
    return (
      <AppLayout>
        <div className="max-w-6xl mx-auto py-8 px-4">
          <div className="text-center">
            <div className="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
              <Users className="w-12 h-12 text-gray-400" />
            </div>
            <h2 className="text-2xl font-bold text-gray-900 mb-4">Ma famille</h2>
            <p className="text-gray-600 mb-8 max-w-md mx-auto">
              Vous n'avez pas encore de membres dans votre famille. Commencez par ajouter des relations familiales.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Button
                onClick={() => window.location.href = '/reseaux'}
                className="flex items-center gap-2"
              >
                <UserPlus className="w-4 h-4" />
                Ajouter des relations
              </Button>
              <Button
                variant="outline"
                onClick={() => window.location.href = '/famille/arbre'}
                className="flex items-center gap-2"
              >
                <TreePine className="w-4 h-4" />
                Voir l'arbre familial
              </Button>
            </div>
          </div>
        </div>
      </AppLayout>
    );
  }

  return (
    <AppLayout>
      <div className="max-w-6xl mx-auto py-8 px-4">
        {/* Header */}
        <div className="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
          <div>
            <h2 className="text-3xl font-bold text-gray-900">Ma famille</h2>
            <p className="text-gray-600 mt-1">
              {members.length} membre{members.length > 1 ? 's' : ''} dans votre famille
            </p>
          </div>
          <div className="flex gap-3">
            <Button
              variant="outline"
              className="flex items-center gap-2"
              onClick={() => window.location.href = '/reseaux'}
            >
              <Plus className="w-4 h-4" />
              Ajouter un membre
            </Button>
            <Button
              variant="outline"
              className="flex items-center gap-2"
              onClick={() => window.location.href = '/famille/arbre'}
            >
              <TreePine className="w-4 h-4" />
              Afficher l'arbre familial
            </Button>
          </div>
        </div>

                {/* Family Members Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
          {members.map((member) => (
            <div key={member.id} className="flex flex-col items-center">
              <FamilyMemberCard
                id={member.id.toString()}
                name={member.name}
                avatarUrl={member.avatar || undefined}
                relation={member.relation}
              />
            </div>
          ))}
        </div>

        {/* Quick Actions Card */}
        <Card className="mt-12">
          <CardContent className="p-6">
            <h3 className="text-lg font-semibold mb-4">Actions rapides</h3>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <Button
                variant="outline"
                className="flex items-center gap-2 h-auto p-4 flex-col"
                onClick={() => window.location.href = '/reseaux'}
              >
                <UserPlus className="w-6 h-6" />
                <div className="text-center">
                  <div className="font-medium">Ajouter des relations</div>
                  <div className="text-xs text-gray-500">Inviter de nouveaux membres</div>
                </div>
              </Button>

              <Button
                variant="outline"
                className="flex items-center gap-2 h-auto p-4 flex-col"
                onClick={() => window.location.href = '/messagerie'}
              >
                <MessageSquare className="w-6 h-6" />
                <div className="text-center">
                  <div className="font-medium">Messagerie familiale</div>
                  <div className="text-xs text-gray-500">Communiquer avec votre famille</div>
                </div>
              </Button>

              <Button
                variant="outline"
                className="flex items-center gap-2 h-auto p-4 flex-col"
                onClick={() => window.location.href = '/famille/arbre'}
              >
                <TreePine className="w-6 h-6" />
                <div className="text-center">
                  <div className="font-medium">Arbre généalogique</div>
                  <div className="text-xs text-gray-500">Voir les liens familiaux</div>
                </div>
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
