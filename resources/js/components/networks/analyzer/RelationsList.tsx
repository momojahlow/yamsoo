
import { FamilyRelation } from "@/types/family";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { getRelationLabel } from "@/utils/relationUtils";

interface RelationsListProps {
  relations: FamilyRelation[];
}

export const RelationsList = ({ relations }: RelationsListProps) => {
  if (!relations || relations.length === 0) {
    return (
      <div className="text-center py-8 text-muted-foreground">
        Aucune relation trouvée dans la base de données
      </div>
    );
  }

  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Utilisateur</TableHead>
          <TableHead>Relation</TableHead>
          <TableHead>Avec</TableHead>
          <TableHead>Statut</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {relations.map((relation) => {
          const userProfile = relation.user_profile;
          const relatedProfile = relation.related_profile;
          
          if (!userProfile || !relatedProfile) return null;
          
          // Create initials for both profiles
          const userInitials = `${userProfile.first_name[0]}${userProfile.last_name[0]}`.toUpperCase();
          const relatedInitials = `${relatedProfile.first_name[0]}${relatedProfile.last_name[0]}`.toUpperCase();
          
          return (
            <TableRow key={relation.id}>
              <TableCell>
                <div className="flex items-center gap-2">
                  <Avatar className="h-8 w-8">
                    <AvatarImage src={userProfile.avatar_url || ''} />
                    <AvatarFallback>
                      {userInitials}
                    </AvatarFallback>
                  </Avatar>
                  <div>
                    <div className="font-medium">{userProfile.first_name} {userProfile.last_name}</div>
                    <div className="text-xs text-muted-foreground">{userProfile.email}</div>
                  </div>
                </div>
              </TableCell>
              <TableCell>
                <Badge variant="outline">
                  {getRelationLabel(relation.relation_type)}
                </Badge>
              </TableCell>
              <TableCell>
                <div className="flex items-center gap-2">
                  <Avatar className="h-8 w-8">
                    <AvatarImage src={relatedProfile.avatar_url || ''} />
                    <AvatarFallback>
                      {relatedInitials}
                    </AvatarFallback>
                  </Avatar>
                  <div>
                    <div className="font-medium">{relatedProfile.first_name} {relatedProfile.last_name}</div>
                    <div className="text-xs text-muted-foreground">{relatedProfile.email}</div>
                  </div>
                </div>
              </TableCell>
              <TableCell>
                <Badge variant={relation.status === 'accepted' ? 'default' : relation.status === 'rejected' ? 'destructive' : 'outline'}>
                  {relation.status === 'pending' ? 'En attente' : relation.status === 'accepted' ? 'Acceptée' : 'Refusée'}
                </Badge>
              </TableCell>
            </TableRow>
          );
        })}
      </TableBody>
    </Table>
  );
};
