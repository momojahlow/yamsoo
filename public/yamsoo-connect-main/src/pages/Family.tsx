
import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { SidebarProvider } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/app/Sidebar";
import { supabase } from "@/integrations/supabase/client";
import { FamilyRelation, FamilyRelationType, DbFamilyRelationType } from "@/types/family";
import { useToast } from "@/hooks/use-toast";
import { FamilyHeader } from "@/components/family/FamilyHeader";
import { FamilyTable } from "@/components/family/FamilyTable";
import { adaptRelationToGender, getInverseRelation } from "@/utils/relationUtils";
import { safeProfileData } from "@/utils/profileUtils";
import { useIsMobile } from "@/hooks/use-mobile";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";
import { Button } from "@/components/ui/button";
import { Trees } from "lucide-react";

export default function Family() {
  const navigate = useNavigate();
  const { toast } = useToast();
  const [currentUser, setCurrentUser] = useState<any>(null);
  const [relations, setRelations] = useState<FamilyRelation[]>([]);
  const [loading, setLoading] = useState(true);
  const isMobile = useIsMobile();

  useEffect(() => {
    const checkUser = async () => {
      const { data: { user } } = await supabase.auth.getUser();
      if (!user) {
        navigate("/auth");
      } else {
        setCurrentUser(user);
        fetchRelations(user.id);
      }
    };

    checkUser();
  }, [navigate]);

  const fetchRelations = async (userId: string) => {
    try {
      setLoading(true);
      console.log("Fetching relations for user:", userId);
      
      // Récupérer le genre de l'utilisateur courant
      const { data: currentUserProfile, error: profileError } = await supabase
        .from('profiles')
        .select('gender')
        .eq('id', userId)
        .single();

      if (profileError) {
        console.error("Error fetching user profile:", profileError);
        throw profileError;
      }

      // Mise à jour de la requête pour éviter d'utiliser des clés étrangères qui semblent manquer
      const { data: relations, error: relationsError } = await supabase
        .from('family_relations')
        .select(`
          id,
          user_id,
          related_user_id,
          relation_type,
          status,
          created_at,
          updated_at
        `)
        .or(`user_id.eq.${userId},related_user_id.eq.${userId}`)
        .eq('status', 'accepted'); // Uniquement les relations acceptées

      if (relationsError) {
        console.error("Error fetching relations:", relationsError);
        throw relationsError;
      }

      console.log("Fetched relations:", relations?.length);

      // Une fois les relations de base récupérées, obtenir les profils séparément
      if (relations && relations.length > 0) {
        const processedRelations = await Promise.all(relations.map(async (relation) => {
          // Déterminer l'ID de l'autre utilisateur
          const relatedUserId = relation.user_id === userId ? relation.related_user_id : relation.user_id;
          
          // Récupérer les informations de profil pour l'utilisateur lié
          const { data: profileData, error: profileError } = await supabase
            .from('profiles')
            .select('*')
            .eq('id', relatedUserId)
            .single();
            
          if (profileError) {
            console.error(`Error fetching profile for ${relatedUserId}:`, profileError);
            return null;
          }
          
          // Récupérer les informations de profil pour l'utilisateur courant
          const { data: currentUserProfileData, error: currentUserProfileError } = await supabase
            .from('profiles')
            .select('*')
            .eq('id', userId)
            .single();
            
          if (currentUserProfileError) {
            console.error(`Error fetching profile for current user:`, currentUserProfileError);
            return null;
          }

          return {
            ...relation,
            profiles: profileData,
            related_profile: profileData,
            user_profile: currentUserProfileData
          };
        }));

        // Filtrer les relations nulles
        const validRelations = processedRelations
          .filter(relation => relation !== null);

        console.log("Processed relations:", validRelations.length);
        setRelations(validRelations as FamilyRelation[]);
      } else {
        setRelations([]);
      }
    } catch (error) {
      console.error('Error fetching relations:', error);
      toast({
        title: "Erreur",
        description: "Impossible de charger les relations familiales",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  const handleViewFamilyTree = () => {
    navigate('/famille/arbre');
  };

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full bg-background">
        <AppSidebar />
        <main className="flex-1 md:ml-16 pb-20 md:pb-8">
          <div className={`container ${isMobile ? 'py-4 px-2 mt-14' : 'py-8'}`}>
            {/* Titre personnalisé pour mobile avec bouton arbre */}
            {isMobile && (
              <div className="flex justify-between items-center mb-4">
                <h1 className="text-2xl font-bold tracking-tight">Ma Famille</h1>
                <Button 
                  variant="outline" 
                  size="sm" 
                  onClick={handleViewFamilyTree}
                  className="whitespace-nowrap"
                >
                  <Trees className="h-3.5 w-3.5 mr-1" />
                  Arbre
                </Button>
              </div>
            )}
            
            {/* Sur desktop, FamilyHeader contient déjà le titre */}
            {!isMobile && <FamilyHeader />}
            
            <div className={isMobile ? 'mt-2' : 'mt-6'}>
              <FamilyTable relations={relations} loading={loading} />
            </div>
          </div>
        </main>
        
        {/* Navigation mobile en bas de l'écran */}
        {isMobile && <MobileNavBar />}
      </div>
    </SidebarProvider>
  );
}
