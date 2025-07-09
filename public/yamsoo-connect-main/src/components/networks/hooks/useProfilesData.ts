
import { useState, useEffect } from "react";
import { User } from "@supabase/supabase-js";
import { getSupabaseClient } from "@/utils/supabaseClient";
import { useToast } from "@/hooks/use-toast";

type Profile = {
  id: string;
  first_name: string;
  last_name: string;
  email: string;
  mobile?: string | null;
  birth_date?: string | null;
  gender?: string | null;
  avatar_url?: string | null;
  relation_id?: string;
  relation_status?: string;
};

export const useProfilesData = (user: User | null) => {
  const { toast } = useToast();
  const supabaseClient = getSupabaseClient();
  const [profiles, setProfiles] = useState<Profile[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchProfiles = async () => {
    if (!user) {
      console.log("🚫 useProfilesData: Aucun utilisateur connecté");
      setProfiles([]);
      setLoading(false);
      return;
    }

    try {
      console.log("🔍 useProfilesData: Récupération des profils pour:", user.id);
      
      // Récupérer TOUS les profils d'abord
      const { data: allProfiles, error: profilesError } = await supabaseClient
        .from('profiles')
        .select('*');

      if (profilesError) {
        console.error("❌ useProfilesData: Erreur profils:", profilesError);
        toast({
          title: "Erreur",
          description: "Impossible de charger les utilisateurs",
          variant: "destructive",
        });
        setLoading(false);
        return;
      }

      console.log("📊 useProfilesData: Profils totaux récupérés:", allProfiles?.length || 0);
      console.log("📊 useProfilesData: Détails des profils:", allProfiles?.map(p => ({ id: p.id, nom: `${p.first_name} ${p.last_name}`, email: p.email })));
      
      if (!allProfiles || allProfiles.length === 0) {
        console.log("ℹ️ useProfilesData: Aucun profil dans la base");
        setProfiles([]);
        setLoading(false);
        return;
      }

      // Filtrer pour exclure l'utilisateur actuel
      const otherProfiles = allProfiles.filter(profile => profile.id !== user.id);
      console.log("👥 useProfilesData: Profils autres que l'utilisateur actuel:", otherProfiles.length);
      console.log("👥 useProfilesData: Détails profils filtrés:", otherProfiles.map(p => ({ id: p.id, nom: `${p.first_name} ${p.last_name}`, email: p.email })));

      // Récupérer les relations de l'utilisateur actuel
      const { data: userRelations, error: relationsError } = await supabaseClient
        .from('family_relations')
        .select('id, related_user_id, user_id, status')
        .or(`user_id.eq.${user.id},related_user_id.eq.${user.id}`);

      if (relationsError) {
        console.error("⚠️ useProfilesData: Erreur relations (non bloquante):", relationsError);
      }

      console.log("🔗 useProfilesData: Relations utilisateur:", userRelations?.length || 0);

      // Créer une map des relations
      const relationMap = new Map<string, { id: string; status: string }>();
      userRelations?.forEach(relation => {
        const otherUserId = relation.user_id === user.id ? relation.related_user_id : relation.user_id;
        relationMap.set(otherUserId, {
          id: relation.id,
          status: relation.status
        });
      });

      // Enrichir les profils avec les relations
      const enrichedProfiles = otherProfiles.map(profile => {
        const relationInfo = relationMap.get(profile.id);
        return {
          ...profile,
          relation_id: relationInfo?.id,
          relation_status: relationInfo?.status
        };
      });

      console.log("✅ useProfilesData: Profils finaux:", enrichedProfiles.length);
      console.log("👤 useProfilesData: Profils finaux détails:", enrichedProfiles.map(p => ({
        id: p.id,
        name: `${p.first_name} ${p.last_name}`,
        email: p.email,
        hasRelation: !!p.relation_status
      })));

      setProfiles(enrichedProfiles);
    } catch (error) {
      console.error('❌ useProfilesData: Erreur générale:', error);
      toast({
        title: "Erreur",
        description: "Impossible de charger les utilisateurs",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchProfiles();
  }, [user]);

  return {
    profiles,
    loading,
    refetch: fetchProfiles
  };
};
