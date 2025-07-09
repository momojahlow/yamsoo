
import { useState, useEffect } from "react";
import { useToast } from "@/hooks/use-toast";
import { supabase } from "@/integrations/supabase/client";
import { FamilyRelationType, FamilyRelation } from "@/types/family";
import { useFamilyRelation } from "@/hooks/useFamilyRelation";
import { useNetworks } from "./NetworksProvider";
import { ProfileCard } from "./ProfileCard";
import { EmptyProfilesState } from "./EmptyProfilesState";
import { ProfileListHeader } from "./ProfileListHeader";
import { VALID_DB_RELATION_TYPES } from "@/hooks/family-relations/relationTypeUtils";
import { Badge } from "@/components/ui/badge";
import { Bell } from "lucide-react";
import { Button } from "@/components/ui/button";

const RELATION_TYPES: FamilyRelationType[] = [
  ...VALID_DB_RELATION_TYPES,
  'boy', 'baby', 'child',
  'friend_m', 'friend_f',
  'colleague', 'sibling',
  'half_brother'
];

const ProfileList = ({ profiles, onSendMessage, onRelationAdded }: {
  profiles: any[];
  onSendMessage: (profileId: string) => void;
  onRelationAdded: () => void;
}) => {
  const { toast } = useToast();
  const { addRelation, fetchPendingRelations } = useFamilyRelation();
  const { refetchProfiles, searchQuery } = useNetworks();
  const [currentUserId, setCurrentUserId] = useState<string | null>(null);
  const [pendingRelations, setPendingRelations] = useState<FamilyRelation[]>([]);
  const [showPendingRelations, setShowPendingRelations] = useState(false);

  useEffect(() => {
    const fetchCurrentUser = async () => {
      const { data: { user } } = await supabase.auth.getUser();
      if (user) {
        setCurrentUserId(user.id);
        console.log("Current user ID set:", user.id);
        
        // Récupérer les demandes de relation en attente
        const pendingRels = await fetchPendingRelations();
        setPendingRelations(pendingRels);
      }
    };
    fetchCurrentUser();
  }, [fetchPendingRelations]);

  const handleAddRelation = async (profileId: string, relationType: FamilyRelationType) => {
    try {
      if (!currentUserId) {
        console.error("Tentative d'ajout d'une relation sans être connecté");
        toast({
          title: "Erreur d'authentification",
          description: "Vous devez être connecté pour ajouter une relation",
          variant: "destructive",
        });
        return;
      }

      if (profileId === currentUserId) {
        console.error("Tentative d'ajout d'une relation avec soi-même");
        toast({
          title: "Erreur",
          description: "Vous ne pouvez pas ajouter une relation avec vous-même",
          variant: "destructive",
        });
        return;
      }

      if (!profileId) {
        console.error("ID de profil manquant");
        toast({
          title: "Erreur",
          description: "Impossible d'identifier le profil sélectionné",
          variant: "destructive",
        });
        return;
      }

      console.log(`Tentative d'ajout d'une relation de type ${relationType} avec le profil ${profileId}`);
      
      const success = await addRelation(
        profileId,
        relationType
      );

      if (success) {
        toast({
          title: "Succès",
          description: "La demande de relation a été envoyée",
        });
        onRelationAdded();
      }
    } catch (error) {
      console.error('Error sending relation request:', error);
      toast({
        title: "Erreur",
        description: "Impossible d'envoyer la demande de relation",
        variant: "destructive",
      });
    }
  };

  const handleTogglePendingRelations = () => {
    setShowPendingRelations(!showPendingRelations);
  };

  console.log("🔍 ProfileList - Profils reçus:", profiles.length);
  console.log("📝 Terme de recherche dans ProfileList:", searchQuery);
  console.log("📋 Détails des profils dans ProfileList:", profiles.map(p => ({ 
    id: p.id, 
    nom: `${p.first_name} ${p.last_name}`, 
    email: p.email 
  })));

  return (
    <div className="space-y-8">
      <div className="flex justify-between items-center">
        <ProfileListHeader />
        {pendingRelations.length > 0 && (
          <Button onClick={handleTogglePendingRelations} variant="outline" className="flex items-center gap-2">
            <Bell size={16} />
            Demandes en attente
            <Badge variant="destructive" className="ml-1">{pendingRelations.length}</Badge>
          </Button>
        )}
      </div>
      
      {showPendingRelations && pendingRelations.length > 0 && (
        <div className="bg-slate-50 p-4 rounded-lg border mb-4">
          <h3 className="font-medium text-lg mb-2">Demandes de relation en attente</h3>
          <ul className="space-y-2">
            {pendingRelations.map(relation => (
              <li key={relation.id} className="p-2 bg-white rounded border">
                <div className="flex justify-between items-center">
                  <div>
                    <span className="font-medium">{relation.user_profile?.first_name} {relation.user_profile?.last_name}</span>
                    <span className="text-sm text-muted-foreground ml-2">
                      souhaite être votre {relation.relation_type}
                    </span>
                  </div>
                  <div className="flex gap-2">
                    <Button size="sm" onClick={() => {
                      toast({
                        title: "Information",
                        description: "Cette fonctionnalité est accessible depuis les notifications",
                      });
                    }}>
                      Voir
                    </Button>
                  </div>
                </div>
              </li>
            ))}
          </ul>
        </div>
      )}
      
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {profiles.length === 0 ? (
          <EmptyProfilesState 
            isSearching={Boolean(searchQuery && searchQuery.trim())} 
            searchQuery={searchQuery} 
          />
        ) : (
          profiles.map((profile) => (
            <ProfileCard
              key={profile.id}
              profile={profile}
              onSendMessage={onSendMessage}
              onAddRelation={handleAddRelation}
              relationTypes={RELATION_TYPES}
            />
          ))
        )}
      </div>
    </div>
  );
};

export { ProfileList };
