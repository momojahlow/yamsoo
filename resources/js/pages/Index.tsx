
import { useEffect, useState } from "react";
import { SidebarProvider } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/app/Sidebar";
import { useIsMobile } from "@/hooks/use-mobile";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";
import { FloatingLogoutButton } from "@/components/FloatingLogoutButton";

export default function Index() {
  const [loading, setLoading] = useState(true);
  const isMobile = useIsMobile();

  useEffect(() => {
    const checkAuth = async () => {
      try {
        const response = await fetch('/check-auth');
        if (response.ok) {
          // Rediriger vers le dashboard si l'utilisateur est connecté
          window.location.href = '/dashboard';
        } else {
          // Rediriger vers la page d'authentification si non connecté
          window.location.href = '/auth';
        }
      } catch (error) {
        console.error("Erreur lors de la vérification de l'authentification:", error);
        window.location.href = '/auth';
      } finally {
        setLoading(false);
      }
    };

    checkAuth();
  }, []);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-gray-900 mx-auto"></div>
          <p className="mt-4 text-gray-600">Chargement...</p>
        </div>
      </div>
    );
  }

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full bg-background">
        <AppSidebar />
        <main className="flex-1 p-4 md:p-8 md:ml-16 pb-20 md:pb-8">
          <div className="max-w-4xl mx-auto">
            <h1 className="text-3xl font-bold mb-8">Bienvenue sur Yamsoo</h1>
            <p className="text-gray-600">Redirection en cours...</p>
          </div>
        </main>
        {isMobile && <MobileNavBar />}
        <FloatingLogoutButton showOnMobile={true} showOnDesktop={false} />
      </div>
    </SidebarProvider>
  );
}
