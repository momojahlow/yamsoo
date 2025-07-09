
import { createContext, useContext, useState, useEffect } from "react";
import { User } from "@supabase/supabase-js";
import { useProfilesData } from "./hooks/useProfilesData";
import { useUnreadMessages } from "./hooks/useUnreadMessages";
import { useToast } from "@/hooks/use-toast";
import { getSupabaseClient } from "@/utils/supabaseClient";

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

interface NetworksContextType {
  user: User | null;
  loading: boolean;
  profiles: Profile[];
  unreadCount: number;
  searchQuery: string;
  setSearchQuery: (query: string) => void;
  filteredProfiles: Profile[];
  refetchProfiles: () => void;
  handleRelationResponse: (relationId: string, accept: boolean) => Promise<void>;
}

const NetworksContext = createContext<NetworksContextType | undefined>(undefined);

export const useNetworks = () => {
  const context = useContext(NetworksContext);
  if (!context) {
    throw new Error("useNetworks must be used within a NetworksProvider");
  }
  return context;
};

export const NetworksProvider = ({ user, children }: { user: User | null; children: React.ReactNode }) => {
  const [searchQuery, setSearchQuery] = useState("");
  const { toast } = useToast();
  const { profiles, loading, refetch } = useProfilesData(user);
  const unreadCount = useUnreadMessages(user?.id);
  const supabaseClient = getSupabaseClient();

  const handleRelationResponse = async (relationId: string, accept: boolean) => {
    try {
      const { data, error } = await supabaseClient
        .from('family_relations')
        .update({ 
          status: accept ? 'accepted' : 'rejected' 
        })
        .eq('id', relationId);

      if (error) throw error;

      toast({
        title: "Succès",
        description: accept ? "Relation acceptée" : "Relation refusée",
      });

      refetch();
    } catch (error) {
      console.error('Error updating relation:', error);
      toast({
        title: "Erreur",
        description: "Impossible de mettre à jour la relation",
        variant: "destructive",
      });
    }
  };

  // Filtrer les profils avec une recherche simplifiée
  const filteredProfiles = profiles.filter(profile => {
    if (!searchQuery || !searchQuery.trim()) {
      return true; // Afficher tous les profils si pas de recherche
    }
    
    const searchTerm = searchQuery.toLowerCase().trim();
    
    // Recherche dans les champs principaux
    const firstName = (profile.first_name || '').toLowerCase();
    const lastName = (profile.last_name || '').toLowerCase();
    const email = (profile.email || '').toLowerCase();
    const mobile = (profile.mobile || '').toLowerCase();
    const fullName = `${firstName} ${lastName}`.trim();
    
    const isMatch = [
      firstName.includes(searchTerm),
      lastName.includes(searchTerm),
      fullName.includes(searchTerm),
      email.includes(searchTerm),
      mobile.includes(searchTerm)
    ].some(match => match);
    
    console.log(`🔍 NetworksProvider: Recherche "${searchTerm}" sur "${fullName}" (${email}):`, isMatch);
    
    return isMatch;
  });

  // Logging pour debug
  useEffect(() => {
    console.log("🔍 NetworksProvider - État actuel:");
    console.log("- Utilisateur:", user?.id || 'non connecté');
    console.log("- Profils bruts récupérés:", profiles.length);
    console.log("- Terme de recherche:", `"${searchQuery}"`);
    console.log("- Profils après filtrage:", filteredProfiles.length);
    
    if (profiles.length > 0) {
      console.log("- Exemples de profils bruts:", profiles.slice(0, 3).map(p => ({
        id: p.id,
        nom: `${p.first_name} ${p.last_name}`,
        email: p.email
      })));
    }
    
    if (filteredProfiles.length > 0) {
      console.log("- Profils filtrés:", filteredProfiles.map(p => ({
        nom: `${p.first_name} ${p.last_name}`,
        email: p.email
      })));
    } else if (searchQuery && profiles.length > 0) {
      console.log("❌ Aucun profil trouvé pour la recherche mais des profils existent");
    }
  }, [profiles, searchQuery, filteredProfiles.length, user?.id]);

  return (
    <NetworksContext.Provider
      value={{
        user,
        loading,
        profiles: profiles,
        unreadCount,
        searchQuery,
        setSearchQuery,
        filteredProfiles,
        refetchProfiles: refetch,
        handleRelationResponse
      }}
    >
      {children}
    </NetworksContext.Provider>
  );
};
