
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { supabase } from "@/integrations/supabase/client";
import { Database } from "@/integrations/supabase/types";
import { useToast } from "@/hooks/use-toast";
import { PendingRelations } from "./PendingRelations";
import { supabaseRpc } from "@/utils/supabaseClient";

type Profile = Database['public']['Tables']['profiles']['Row'];
type FamilyRelation = Database['public']['Tables']['family_relations']['Row'];

type PendingRelation = {
  relation: FamilyRelation;
  requester: Profile;
};

interface PendingRelationsListProps {
  onAccept: (relationId: string) => Promise<void>;
  onReject: (relationId: string) => Promise<void>;
}

export function PendingRelationsList({ onAccept, onReject }: PendingRelationsListProps) {
  const { toast } = useToast();
  const queryClient = useQueryClient();

  const { data: pendingRelations = [], isLoading } = useQuery({
    queryKey: ["pendingRelations"],
    queryFn: async () => {
      const { data: { user } } = await supabase.auth.getUser();
      if (!user) return [];

      console.log("Fetching pending relations for user:", user.id);

      // SECURITY IMPROVEMENT: RLS policies now ensure only authorized relations are returned
      const { data: relationsData, error: relationsError } = await supabase
        .from('family_relations')
        .select(`
          id,
          user_id,
          related_user_id,
          relation_type,
          status,
          created_at
        `)
        .eq('related_user_id', user.id)
        .eq('status', 'pending');

      if (relationsError) {
        console.error('Error fetching relations:', relationsError);
        toast({
          title: "Erreur",
          description: "Impossible de charger les relations en attente",
          variant: "destructive",
        });
        return [];
      }

      console.log("Found pending relations:", relationsData);

      if (relationsData && relationsData.length > 0) {
        const requesterIds = relationsData.map(relation => relation.user_id);
        
        // SECURITY IMPROVEMENT: RLS policies ensure only visible profiles are returned
        const { data: requesterProfiles, error: profilesError } = await supabase
          .from('profiles')
          .select('*')
          .in('id', requesterIds);
          
        if (profilesError) {
          console.error('Error fetching requester profiles:', profilesError);
          toast({
            title: "Erreur",
            description: "Impossible de charger les profils des demandeurs",
            variant: "destructive",
          });
          return [];
        }
        
        const profileMap = new Map();
        (requesterProfiles || []).forEach(profile => {
          profileMap.set(profile.id, profile);
        });
        
        const pendingRelationsWithProfiles = relationsData.map(relation => {
          const requesterProfile = profileMap.get(relation.user_id);
          
          return {
            relation,
            requester: requesterProfile || null
          };
        }).filter((item): item is PendingRelation => item.requester !== null);
        
        return pendingRelationsWithProfiles;
      }
      
      return [];
    },
  });

  const handleAccept = async (relationId: string) => {
    try {
      console.log("Accepting relation:", relationId);
      // SECURITY IMPROVEMENT: Use secure RPC function
      const { error } = await supabaseRpc.update_family_relation_status({
        relation_id: relationId,
        new_status: 'accepted'
      });
      
      if (error) throw error;
      
      await queryClient.invalidateQueries({ queryKey: ["pendingRelations"] });
      toast({
        title: "Succès",
        description: "Relation familiale acceptée avec succès",
      });
    } catch (error) {
      console.error('Error accepting relation:', error);
      toast({
        title: "Erreur",
        description: "Impossible d'accepter la relation familiale",
        variant: "destructive",
      });
    }
  };

  const handleReject = async (relationId: string) => {
    try {
      console.log("Rejecting relation:", relationId);
      // SECURITY IMPROVEMENT: Use secure RPC function
      const { error } = await supabaseRpc.update_family_relation_status({
        relation_id: relationId,
        new_status: 'rejected'
      });
      
      if (error) throw error;
      
      await queryClient.invalidateQueries({ queryKey: ["pendingRelations"] });
      toast({
        title: "Succès",
        description: "Relation familiale rejetée avec succès",
      });
    } catch (error) {
      console.error('Error rejecting relation:', error);
      toast({
        title: "Erreur",
        description: "Impossible de rejeter la relation familiale",
        variant: "destructive",
      });
    }
  };

  if (isLoading) {
    return <div>Chargement...</div>;
  }

  return (
    <PendingRelations
      pendingRelations={pendingRelations}
      onAccept={handleAccept}
      onReject={handleReject}
    />
  );
}
