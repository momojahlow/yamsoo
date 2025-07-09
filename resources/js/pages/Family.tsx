
import { useEffect, useState } from "react";
import { SidebarProvider } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/app/Sidebar";
import { useIsMobile } from "@/hooks/use-mobile";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

interface FamilyMember {
  id: number;
  name: string;
  email: string;
  pivot?: {
    relation: string;
  };
}

interface Family {
  id: number;
  name: string;
  description?: string;
}

interface FamilyProps {
  family: Family | null;
  members: FamilyMember[];
}

export default function Family({ family, members }: FamilyProps) {
  const isMobile = useIsMobile();

  const handleViewFamilyTree = () => {
    window.location.href = '/famille/arbre';
  };

  if (!family) {
    return (
      <SidebarProvider>
        <div className="min-h-screen flex w-full bg-background">
          <AppSidebar />
          <main className="flex-1 p-4 md:p-8 md:ml-16 pb-20 md:pb-8">
            <div className="max-w-4xl mx-auto">
              <div className="text-center py-12">
                <h1 className="text-3xl font-bold mb-4">Aucune famille trouvée</h1>
                <p className="text-gray-600 mb-6">
                  Vous n'avez pas encore créé ou rejoint de famille.
                </p>
                <Button onClick={() => window.location.href = '/families/create'}>
                  Créer une famille
                </Button>
              </div>
            </div>
          </main>
          {isMobile && <MobileNavBar />}
        </div>
      </SidebarProvider>
    );
  }

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full bg-background">
        <AppSidebar />
        <main className="flex-1 p-4 md:p-8 md:ml-16 pb-20 md:pb-8">
          <div className="max-w-4xl mx-auto">
            <div className="flex justify-between items-center mb-8">
              <div>
                <h1 className="text-3xl font-bold">{family.name}</h1>
                {family.description && (
                  <p className="text-gray-600 mt-2">{family.description}</p>
                )}
              </div>
              <Button onClick={handleViewFamilyTree}>
                Voir l'arbre généalogique
              </Button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {members.map((member) => (
                <Card key={member.id}>
                  <CardHeader>
                    <CardTitle className="flex items-center justify-between">
                      <span>{member.name}</span>
                      {member.pivot?.relation && (
                        <span className="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded">
                          {member.pivot.relation}
                        </span>
                      )}
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <p className="text-gray-600">{member.email}</p>
                  </CardContent>
                </Card>
              ))}
            </div>

            {members.length === 0 && (
              <div className="text-center py-12">
                <p className="text-gray-600 mb-4">
                  Aucun membre dans cette famille pour le moment.
                </p>
                <Button onClick={() => window.location.href = '/families/add-member'}>
                  Ajouter un membre
                </Button>
              </div>
            )}
          </div>
        </main>
        {isMobile && <MobileNavBar />}
      </div>
    </SidebarProvider>
  );
}
