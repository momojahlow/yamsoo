
import { useEffect, useState } from "react";
import { SidebarProvider } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/app/Sidebar";
import { AuthContainer } from "@/components/auth/AuthContainer";
import { LoginForm } from "@/components/auth/LoginForm";
import { useIsMobile } from "@/hooks/use-mobile";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";

export default function Auth() {
  const isMobile = useIsMobile();
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  useEffect(() => {
    // Vérifier l'authentification via Laravel
    const checkAuth = async () => {
      try {
        const response = await fetch('/check-auth');
        if (response.ok) {
          setIsAuthenticated(true);
          window.location.href = '/dashboard';
        }
      } catch (error) {
        console.error('Erreur lors de la vérification de l\'authentification:', error);
      }
    };

    checkAuth();
  }, []);

  if (isAuthenticated) {
    return null; // Redirection en cours
  }

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full bg-background">
        <AppSidebar />
        <main className="flex-1 p-4 md:p-8 md:ml-16 pb-20 md:pb-8">
          <div className="max-w-md mx-auto">
            <AuthContainer>
              <LoginForm />
            </AuthContainer>
          </div>
        </main>
        {isMobile && <MobileNavBar />}
      </div>
    </SidebarProvider>
  );
}
