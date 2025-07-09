
import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { SidebarProvider } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/app/Sidebar";
import { supabase } from "@/integrations/supabase/client";
import { NotificationsTable } from "@/components/notifications/NotificationsTable";
import { useNotifications } from "@/hooks/useNotifications";
import { useIsMobile } from "@/hooks/use-mobile";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";
import { useToast } from "@/hooks/use-toast";
import { AlertCircle } from "lucide-react";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";

export default function Notifications() {
  const navigate = useNavigate();
  const { toast } = useToast();
  const { notifications, loading, fetchNotifications, setupRealtimeSubscription, setLoading } = useNotifications();
  const [userId, setUserId] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const isMobile = useIsMobile();

  useEffect(() => {
    const checkUser = async () => {
      const { data: { user } } = await supabase.auth.getUser();
      if (!user) {
        navigate("/auth");
        return;
      } 
      
      setUserId(user.id);
      console.log("Initialisation des notifications pour l'utilisateur:", user.id);
      
      try {
        await fetchNotifications(user.id);
        setError(null); // Clear errors on successful fetch
        
        // Mettre en place la souscription en temps réel
        const cleanup = setupRealtimeSubscription(user.id);
        return () => {
          cleanup();
        };
      } catch (err) {
        console.error("Erreur lors de l'initialisation des notifications:", err);
        setError("Impossible de charger les notifications");
        toast({
          title: "Erreur",
          description: "Impossible de charger les notifications",
          variant: "destructive",
        });
      }
    };

    checkUser();
  }, [navigate, fetchNotifications, setupRealtimeSubscription, toast]);

  // Filter for relation notifications and log counts for debugging
  const relationNotifications = notifications.filter(n => n.type === 'relation');
  console.log("Total notifications:", notifications.length, "Relation notifications:", relationNotifications.length);

  const handleResponseSuccess = async () => {
    if (userId) {
      console.log("Rafraîchissement des notifications après une action...");
      try {
        await fetchNotifications(userId);
        setError(null); // Clear errors on successful fetch
        
        // Attendre un peu plus longtemps pour s'assurer que la base de données est mise à jour
        setTimeout(async () => {
          console.log("Rafraîchissement différé des notifications...");
          try {
            await fetchNotifications(userId);
          } catch (err) {
            console.error("Erreur lors du rafraîchissement différé:", err);
          }
        }, 2000);
      } catch (err) {
        console.error("Erreur lors du rafraîchissement:", err);
        setError("Impossible de rafraîchir les notifications");
      }
    }
  };

  const handleRetry = async () => {
    if (userId) {
      setLoading(true);
      setError(null);
      try {
        await fetchNotifications(userId);
        toast({
          title: "Succès",
          description: "Notifications rafraîchies avec succès",
        });
      } catch (err) {
        console.error("Erreur lors de la nouvelle tentative:", err);
        setError("Impossible de charger les notifications");
        toast({
          title: "Erreur",
          description: "Impossible de charger les notifications",
          variant: "destructive",
        });
      }
    }
  };

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full bg-background">
        {!isMobile && <AppSidebar />}
        <main className={`flex-1 ${isMobile ? 'pb-20' : 'md:ml-16 pb-8'} ${isMobile ? 'pt-6' : ''}`}>
          <div className="container">
            <div className="flex justify-between items-center mb-4 pt-6">
              <h1 className="text-2xl font-bold text-amber-800">Demandes de relations</h1>
            </div>

            {error && (
              <Alert variant="destructive" className="mb-4">
                <AlertCircle className="h-4 w-4" />
                <AlertTitle>Erreur</AlertTitle>
                <AlertDescription>
                  {error}
                  <button 
                    className="ml-4 underline text-sm"
                    onClick={handleRetry}
                  >
                    Essayer à nouveau
                  </button>
                </AlertDescription>
              </Alert>
            )}

            {loading ? (
              <div className="flex justify-center items-center py-8">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
              </div>
            ) : (
              <NotificationsTable 
                notifications={relationNotifications}
                onResponseSuccess={handleResponseSuccess}
              />
            )}
          </div>
        </main>
        
        {isMobile && <MobileNavBar />}
      </div>
    </SidebarProvider>
  );
}
