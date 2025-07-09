
import { useEffect, useState } from "react";
import { supabase } from "@/integrations/supabase/client";
import { User } from "@supabase/supabase-js";
import { useNavigate } from "react-router-dom";
import { SidebarProvider, SidebarTrigger } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/app/Sidebar";
import { useIsMobile } from "@/hooks/use-mobile";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";

export default function Index() {
  const navigate = useNavigate();
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const isMobile = useIsMobile();

  useEffect(() => {
    console.log("Vérification de l'authentification...");
    const checkAuth = async () => {
      try {
        const { data: { session }, error } = await supabase.auth.getSession();
        
        if (error) {
          console.error("Erreur lors de la vérification de la session:", error);
          navigate("/auth");
          return;
        }

        if (!session) {
          console.log("Aucune session active, redirection vers /auth");
          navigate("/auth");
          return;
        }

        console.log("Session trouvée, utilisateur connecté:", session.user);
        setUser(session.user);
        setLoading(false);
      } catch (error) {
        console.error("Erreur inattendue:", error);
        navigate("/auth");
      }
    };

    checkAuth();

    const { data: { subscription } } = supabase.auth.onAuthStateChange((event, session) => {
      console.log("Changement d'état d'authentification:", event);
      if (event === 'SIGNED_IN') {
        setUser(session?.user ?? null);
        setLoading(false);
      } else if (event === 'SIGNED_OUT') {
        navigate("/auth");
      }
    });

    return () => {
      subscription.unsubscribe();
    };
  }, [navigate]);

  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-screen bg-background">
        <div className="animate-pulse text-lg font-medium">
          Chargement...
        </div>
      </div>
    );
  }

  if (!user) {
    return null;
  }

  return (
    <div className="min-h-screen w-full bg-background">
      <SidebarProvider defaultOpen>
        <div className="flex min-h-screen bg-background overflow-hidden">
          <AppSidebar />
          <div className="flex-1 relative md:ml-16 pb-20 md:pb-8">
            {!isMobile && (
              <div className="sticky top-0 z-10 bg-background p-4 border-b">
                <SidebarTrigger />
              </div>
            )}
            <main className={`p-4 lg:p-8 overflow-auto ${isMobile ? 'pt-20' : ''}`}>
              <div className="container mx-auto">
                <div className="space-y-8">
                  <h1 className="text-4xl font-bold tracking-tight text-brown">Bienvenue sur Yamsoo!</h1>
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div className="p-6 bg-white rounded-lg shadow-sm border">
                      <h2 className="text-xl font-semibold mb-4">Commencez à explorer</h2>
                      <p className="text-muted-foreground">
                        Découvrez votre réseau familial et communiquez avec vos proches.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </main>
          </div>
          
          {/* Navigation mobile en bas de l'écran */}
          {isMobile && <MobileNavBar />}
        </div>
      </SidebarProvider>
    </div>
  );
}
