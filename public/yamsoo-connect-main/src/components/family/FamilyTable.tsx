
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { getRelationLabel, getStatusLabel } from "@/utils/familyUtils";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { FamilyRelation, FamilyRelationType } from "@/types/family";
import { MessageSquare, Trash2, Trees } from "lucide-react";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { getSupabaseClient } from "@/utils/supabaseClient";
import { useToast } from "@/hooks/use-toast";
import { getInverseRelation, adaptRelationToGender } from "@/utils/relationUtils";
import { useIsMobile } from "@/hooks/use-mobile";

interface FamilyTableProps {
  relations: FamilyRelation[];
  loading: boolean;
}

export function FamilyTable({ relations, loading }: FamilyTableProps) {
  const navigate = useNavigate();
  const [selectedRelation, setSelectedRelation] = useState<{ id: string; name: string } | null>(null);
  const { toast } = useToast();
  const supabaseClient = getSupabaseClient();
  const isMobile = useIsMobile();

  const getViewerRelation = (relation: FamilyRelation, member: any, currentUser: any) => {
    const isInverse = relation.user_id !== currentUser.id;
    
    if (isInverse) {
      let inverseRelation = getInverseRelation(relation.relation_type);
      return adaptRelationToGender(inverseRelation as FamilyRelationType, member.gender || 'M');
    }
    
    return adaptRelationToGender(relation.relation_type as FamilyRelationType, member.gender || 'F');
  };

  const handleDeleteRelation = async () => {
    if (!selectedRelation) return;

    try {
      const { error } = await supabaseClient.from('family_relations')
        .delete()
        .eq('id', selectedRelation.id);

      if (error) throw error;

      toast({
        title: "Succès",
        description: `La relation avec ${selectedRelation.name} a été supprimée`,
      });

      window.location.reload();
    } catch (error) {
      toast({
        title: "Erreur",
        description: "Impossible de supprimer la relation",
        variant: "destructive",
      });
    } finally {
      setSelectedRelation(null);
    }
  };

  const handleSendMessage = (id: string) => {
    navigate("/messagerie", { state: { selectedContactId: id } });
  };
  
  const handleViewFamilyTree = () => {
    navigate('/famille/arbre');
  };

  if (loading) {
    return <div className="p-8 text-center">Chargement des relations familiales...</div>;
  }

  if (relations.length === 0) {
    return (
      <div className="p-8 text-center">
        <p className="text-muted-foreground">Aucune relation familiale trouvée.</p>
        <p className="text-sm text-muted-foreground mt-2">
          Vous pouvez ajouter des relations dans la section "Réseaux".
        </p>
        {isMobile && (
          <Button 
            onClick={handleViewFamilyTree}
            variant="outline"
            className="mt-4"
            size="sm"
          >
            <Trees className="h-4 w-4 mr-2" />
            Voir l'arbre familial
          </Button>
        )}
      </div>
    );
  }

  // Si nous sommes sur mobile, ajouter un bouton flottant pour accéder à l'arbre familial
  if (isMobile) {
    return (
      <>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Membre</TableHead>
              <TableHead>Relation</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {relations.map((relation) => {
              const profile = relation.related_profile || relation.profiles;
              if (!profile) return null;
              
              const member = profile;
              const currentUser = relation.user_profile;
              const viewerRelation = getViewerRelation(relation, member, currentUser);

              return (
                <TableRow key={relation.id}>
                  <TableCell>
                    <div className="flex items-center gap-2">
                      <Avatar className="h-8 w-8">
                        <AvatarImage
                          src={member.avatar_url || ""}
                          alt={`${member.first_name} ${member.last_name}`}
                        />
                        <AvatarFallback>
                          {member.first_name?.[0]}
                          {member.last_name?.[0]}
                        </AvatarFallback>
                      </Avatar>
                      <div className="flex flex-col">
                        <div className="font-medium text-sm">
                          {member.first_name} {member.last_name}
                        </div>
                        <div className="text-xs text-muted-foreground">
                          {member.email}
                        </div>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>{getRelationLabel(viewerRelation)}</TableCell>
                  <TableCell>
                    <div className="flex items-center gap-1">
                      <Button 
                        variant="ghost" 
                        size="icon" 
                        className="h-8 w-8 text-primary hover:bg-primary/10" 
                        onClick={() => handleSendMessage(profile.id)}
                        title="Envoyer un message"
                      >
                        <MessageSquare className="h-3.5 w-3.5" />
                      </Button>
                      <Button 
                        variant="ghost" 
                        size="icon" 
                        className="h-8 w-8 hover:text-destructive" 
                        onClick={() => setSelectedRelation({ id: relation.id, name: `${member.first_name} ${member.last_name}` })}
                        title="Supprimer la relation"
                      >
                        <Trash2 className="h-3.5 w-3.5" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              );
            })}
          </TableBody>
        </Table>

        <AlertDialog open={!!selectedRelation} onOpenChange={() => setSelectedRelation(null)}>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Confirmer la suppression</AlertDialogTitle>
              <AlertDialogDescription>
                Êtes-vous sûr de vouloir supprimer la relation avec {selectedRelation?.name} ?
                Cette action est irréversible.
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel>Annuler</AlertDialogCancel>
              <AlertDialogAction onClick={handleDeleteRelation} className="bg-destructive text-destructive-foreground hover:bg-destructive/90">
                Supprimer
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
      </>
    );
  }

  // Desktop version
  return (
    <>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Membre</TableHead>
            <TableHead>Relation</TableHead>
            <TableHead>Statut</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {relations.map((relation) => {
            const profile = relation.related_profile || relation.profiles;
            if (!profile) return null;
            
            const member = profile;
            const currentUser = relation.user_profile;
            const viewerRelation = getViewerRelation(relation, member, currentUser);

            return (
              <TableRow key={relation.id}>
                <TableCell>
                  <div className="flex items-center gap-3">
                    <Avatar>
                      <AvatarImage
                        src={member.avatar_url || ""}
                        alt={`${member.first_name} ${member.last_name}`}
                      />
                      <AvatarFallback>
                        {member.first_name?.[0]}
                        {member.last_name?.[0]}
                      </AvatarFallback>
                    </Avatar>
                    <div className="flex flex-col">
                      <div className="font-medium">
                        {member.first_name} {member.last_name}
                      </div>
                      <div className="text-sm text-muted-foreground">
                        {member.email}
                      </div>
                    </div>
                  </div>
                </TableCell>
                <TableCell>{getRelationLabel(viewerRelation)}</TableCell>
                <TableCell>
                  <span className="px-2 py-1 rounded-full text-sm bg-green-100 text-green-800">
                    {getStatusLabel(relation.status)}
                  </span>
                </TableCell>
                <TableCell>
                  <div className="flex items-center gap-2">
                    <Button 
                      variant="ghost" 
                      size="icon" 
                      className="text-primary hover:bg-primary/10" 
                      onClick={() => handleSendMessage(profile.id)}
                      title="Envoyer un message"
                    >
                      <MessageSquare className="h-4 w-4" />
                    </Button>
                    <Button 
                      variant="ghost" 
                      size="icon" 
                      className="hover:text-destructive" 
                      onClick={() => setSelectedRelation({ id: relation.id, name: `${member.first_name} ${member.last_name}` })}
                      title="Supprimer la relation"
                    >
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            );
          })}
        </TableBody>
      </Table>

      <AlertDialog open={!!selectedRelation} onOpenChange={() => setSelectedRelation(null)}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Confirmer la suppression</AlertDialogTitle>
            <AlertDialogDescription>
              Êtes-vous sûr de vouloir supprimer la relation avec {selectedRelation?.name} ?
              Cette action est irréversible.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Annuler</AlertDialogCancel>
            <AlertDialogAction onClick={handleDeleteRelation} className="bg-destructive text-destructive-foreground hover:bg-destructive/90">
              Supprimer
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </>
  );
}
