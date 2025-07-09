
import { Users, UserPlus, Search } from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";

interface EmptyProfilesStateProps {
  isSearching?: boolean;
  searchQuery?: string;
}

export const EmptyProfilesState = ({ isSearching = false, searchQuery = "" }: EmptyProfilesStateProps) => {
  if (isSearching && searchQuery) {
    return (
      <div className="col-span-full flex justify-center py-12">
        <Card className="w-full max-w-md">
          <CardContent className="flex flex-col items-center justify-center py-12 text-center">
            <div className="rounded-full bg-muted p-6 mb-4">
              <Search className="h-12 w-12 text-muted-foreground" />
            </div>
            <h3 className="text-lg font-semibold mb-2">Aucun résultat</h3>
            <p className="text-sm text-muted-foreground mb-4">
              Aucun utilisateur ne correspond à votre recherche "{searchQuery}".
            </p>
            <p className="text-xs text-muted-foreground">
              Essayez avec un autre terme de recherche.
            </p>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="col-span-full flex justify-center py-12">
      <Card className="w-full max-w-md">
        <CardContent className="flex flex-col items-center justify-center py-12 text-center">
          <div className="rounded-full bg-muted p-6 mb-4">
            <Users className="h-12 w-12 text-muted-foreground" />
          </div>
          <h3 className="text-lg font-semibold mb-2">Aucun utilisateur trouvé</h3>
          <p className="text-sm text-muted-foreground mb-4">
            Il n'y a pas encore d'autres utilisateurs inscrits sur la plateforme.
          </p>
          <div className="flex items-center gap-2 text-xs text-muted-foreground">
            <UserPlus className="h-4 w-4" />
            <span>Invitez des amis à rejoindre la plateforme</span>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};
