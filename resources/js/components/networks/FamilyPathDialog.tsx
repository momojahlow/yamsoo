
import { useState, useEffect } from "react";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { getSupabaseClient, supabaseRpc } from "@/utils/supabaseClient";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Separator } from "@/components/ui/separator";
import { getRelationLabel } from "@/utils/relationUtils";
import { User } from "@supabase/supabase-js";

interface FamilyPathDialogProps {
  targetUserId: string | null;
  currentUser: User | null;
  isOpen: boolean;
  onClose: () => void;
}

type FamilyPathMember = {
  id: string;
  name: string;
  avatar?: string;
  relationType: string;
};

type FamilyPath = {
  members: FamilyPathMember[];
  totalDepth: number;
};

export function FamilyPathDialog({
  targetUserId,
  currentUser,
  isOpen,
  onClose
}: FamilyPathDialogProps) {
  const [paths, setPaths] = useState<FamilyPath[]>([]);
  const [loading, setLoading] = useState(false);
  const supabaseClient = getSupabaseClient();

  useEffect(() => {
    if (isOpen && targetUserId && currentUser) {
      fetchFamilyPaths();
    } else {
      setPaths([]);
    }
  }, [isOpen, targetUserId, currentUser]);

  const fetchFamilyPaths = async () => {
    if (!targetUserId || !currentUser) return;
    
    setLoading(true);
    
    try {
      // Utiliser la fonction RPC pour récupérer tous les chemins familiaux
      const { data: pathsData, error } = await supabaseRpc.find_all_family_paths({
        target_user_id: targetUserId
      });
      
      if (error) {
        console.error("Erreur lors de la récupération des chemins familiaux:", error);
        return;
      }
      
      if (!pathsData || pathsData.length === 0) {
        console.log("Aucun chemin familial trouvé");
        return;
      }
      
      console.log("Chemins familiaux récupérés:", pathsData);
      
      // Récupérer les informations de profil pour tous les membres des chemins
      const allMemberIds = new Set<string>();
      pathsData.forEach(path => {
        path.path_members.forEach(id => allMemberIds.add(id));
      });
      
      const { data: profilesData, error: profilesError } = await supabaseClient
        .from('profiles')
        .select('id, first_name, last_name, avatar_url')
        .in('id', Array.from(allMemberIds));
      
      if (profilesError) {
        console.error("Erreur lors de la récupération des profils:", profilesError);
        return;
      }
      
      // Créer un map pour un accès facile aux données de profil
      const profilesMap = new Map();
      profilesData?.forEach(profile => {
        profilesMap.set(profile.id, {
          name: `${profile.first_name} ${profile.last_name}`,
          avatar: profile.avatar_url
        });
      });
      
      // Transformer les données en chemins avec informations de profil
      const formattedPaths: FamilyPath[] = pathsData.map(path => {
        const members: FamilyPathMember[] = [];
        
        // Ignorer le premier membre qui est l'utilisateur actuel
        for (let i = 1; i < path.path_members.length; i++) {
          const memberId = path.path_members[i];
          const relationType = path.path_relations[i-1]; // relation type correspond à l'index précédent
          
          const profile = profilesMap.get(memberId);
          if (profile) {
            members.push({
              id: memberId,
              name: profile.name,
              avatar: profile.avatar,
              relationType: relationType,
            });
          }
        }
        
        return {
          members,
          totalDepth: path.total_depth
        };
      });
      
      // Trier les chemins du plus court au plus long
      formattedPaths.sort((a, b) => a.totalDepth - b.totalDepth);
      
      setPaths(formattedPaths);
    } catch (error) {
      console.error("Erreur inattendue:", error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-md max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Chemins de relation familiale</DialogTitle>
          <DialogDescription>
            Voici les différentes façons dont vous êtes liés à cette personne.
          </DialogDescription>
        </DialogHeader>
        
        {loading && <div className="p-4 text-center">Chargement des chemins familiaux...</div>}
        
        {!loading && paths.length === 0 && (
          <div className="p-4 text-center">
            Aucun chemin familial n'a été trouvé entre vous et cette personne.
          </div>
        )}
        
        {!loading && paths.length > 0 && (
          <div className="space-y-6">
            {paths.map((path, pathIndex) => (
              <div key={pathIndex} className="border rounded-lg p-4">
                <div className="font-semibold mb-2">
                  Chemin {pathIndex + 1} ({path.totalDepth} niveau{path.totalDepth > 1 ? 's' : ''})
                </div>
                
                <div className="space-y-4">
                  {path.members.map((member, memberIndex) => (
                    <div key={memberIndex}>
                      {memberIndex > 0 && (
                        <div className="my-2 flex items-center">
                          <div className="h-0.5 flex-grow bg-gray-200"></div>
                          <span className="px-2 text-sm text-muted-foreground">via</span>
                          <div className="h-0.5 flex-grow bg-gray-200"></div>
                        </div>
                      )}
                      
                      <div className="flex items-center gap-3">
                        <Avatar>
                          <AvatarImage src={member.avatar || ''} />
                          <AvatarFallback>{member.name.substring(0, 2)}</AvatarFallback>
                        </Avatar>
                        
                        <div>
                          <div className="font-medium">{member.name}</div>
                          <div className="text-sm text-muted-foreground">
                            {getRelationLabel(member.relationType)}
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            ))}
          </div>
        )}
      </DialogContent>
    </Dialog>
  );
}
