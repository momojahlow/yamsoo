
import { useEffect, useState } from "react";
import { Button } from "@/components/ui/button";
import { SidebarProvider } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/app/Sidebar";
import { supabase } from "@/integrations/supabase/client";
import { FamilyRelation } from "@/types/family";
import { useToast } from "@/hooks/use-toast";
import { FamilyTreeView } from "@/components/family/FamilyTreeView";
import { Spinner } from "@/components/ui/spinner";
import { useFamilyRelation } from "@/hooks/useFamilyRelation";
import { useIsMobile } from "@/hooks/use-mobile";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";
import { ArrowLeft } from "lucide-react";

export default function FamilyTree() {
  const { toast } = useToast();
  const [relations, setRelations] = useState<FamilyRelation[]>([]);
  const [loading, setLoading] = useState(true);
  const { fetchRelations, isLoading: relationLoading } = useFamilyRelation();
  const isMobile = useIsMobile();

  useEffect(() => {
    const checkUser = async () => {
      const { data: { user } } = await supabase.auth.getUser();
      if (!user) {
        window.location.href = "/auth";
      } else {
        loadFamilyRelations();
      }
    };

    checkUser();
  }, []);

  const loadFamilyRelations = async () => {
    try {
      setLoading(true);
      const familyRelations = await fetchRelations();

      if (familyRelations) {
        console.log("Loaded family relations:", familyRelations);
        // Cast to ensure type safety
        setRelations(familyRelations as FamilyRelation[]);
      } else {
        setRelations([]);
      }
    } catch (error) {
      console.error('Error loading family relations:', error);
      toast({
        title: "Erreur",
        description: "Impossible de charger votre arbre familial",
        variant: "destructive",
      });
      setRelations([]);
    } finally {
      setLoading(false);
    }
  };

  const handleBack = () => {
    window.location.href = '/famille';
  };

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full bg-background">
        {!isMobile && <AppSidebar />}
        <main className="flex-1 md:ml-16 pb-20 md:pb-8">
          <div className={`container ${isMobile ? 'py-4' : 'py-8'}`}>
            <div className={`flex justify-between items-center ${isMobile ? 'mb-4' : 'mb-8'}`}>
              <h1 className={`${isMobile ? 'text-xl' : 'text-3xl'} font-bold text-brown`}>
                Arbre Généalogique
              </h1>
              <Button onClick={handleBack} variant="outline" size={isMobile ? "sm" : "default"}>
                {isMobile ? <ArrowLeft className="h-4 w-4" /> : "Retour"}
              </Button>
            </div>

            {loading ? (
              <div className="flex justify-center items-center h-[60vh]">
                <Spinner size={isMobile ? "md" : "lg"} />
                <span className="ml-2 text-muted-foreground">Chargement de votre arbre familial...</span>
              </div>
            ) : (
              <FamilyTreeView relations={relations} />
            )}
          </div>
        </main>

        {/* Navigation mobile en bas de l'écran */}
        {isMobile && <MobileNavBar />}
      </div>
    </SidebarProvider>
  );
}
