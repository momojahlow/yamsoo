
import { useEffect, useState } from "react";
import { Navigate } from "react-router-dom";
import { supabase } from "@/integrations/supabase/client";
import { useToast } from "@/hooks/use-toast";

interface PrivateRouteProps {
  children: React.ReactNode;
}

export const PrivateRoute: React.FC<PrivateRouteProps> = ({ children }) => {
  const [loading, setLoading] = useState(true);
  const [authenticated, setAuthenticated] = useState(false);
  const { toast } = useToast();

  useEffect(() => {
    console.log("Vérification de l'authentification...");
    checkUser();
    
    const { data: { subscription } } = supabase.auth.onAuthStateChange((event, session) => {
      console.log("Changement d'état d'authentification:", event);
      if (event === 'SIGNED_IN') {
        setAuthenticated(true);
      } else if (event === 'SIGNED_OUT') {
        setAuthenticated(false);
        toast({
          title: "Session terminée",
          description: "Votre session a été déconnectée",
          variant: "default",
        });
      }
    });

    return () => subscription.unsubscribe();
  }, []);

  async function checkUser() {
    try {
      const { data: { session } } = await supabase.auth.getSession();
      
      if (session?.user) {
        console.log("Session trouvée, utilisateur connecté");
        setAuthenticated(true);
        // SECURITY FIX: Remove global user ID assignment that was exposing sensitive data
        // Removed: window.CURRENT_USER_ID = session.user.id;
      } else {
        console.log("Aucune session trouvée, redirection vers /auth");
        setAuthenticated(false);
      }
    } catch (error) {
      console.error("Erreur lors de la vérification de l'utilisateur:", error);
      setAuthenticated(false);
    } finally {
      setLoading(false);
    }
  }

  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-screen">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
      </div>
    );
  }

  return authenticated ? <>{children}</> : <Navigate to="/auth" replace />;
};
