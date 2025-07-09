
import { useState, useCallback } from "react";
import { Button } from "@/components/ui/button";
import { useToast } from "@/hooks/use-toast";
import { supabase } from "@/integrations/supabase/client";
import { useNavigate } from "react-router-dom";
import { LoginFormFields } from "./LoginFormFields";
import { ResetPasswordDialog } from "./ResetPasswordDialog";
import { useTranslation } from "react-i18next";

export const LoginForm = () => {
  const { toast } = useToast();
  const navigate = useNavigate();
  const { t } = useTranslation();
  
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading] = useState(false);
  const [isResetDialogOpen, setIsResetDialogOpen] = useState(false);

  const handleLogin = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();

    if (!email || !password) {
      toast({
        variant: "destructive",
        title: "Erreur",
        description: "Veuillez remplir tous les champs",
      });
      return;
    }

    setLoading(true);
    console.log("🔑 Tentative de connexion avec l'email:", email);

    try {
      const { data, error } = await supabase.auth.signInWithPassword({
        email: email.trim().toLowerCase(),
        password,
      });

      if (error) {
        console.error("❌ Erreur de connexion:", error);
        
        const errorMessage = error.message.includes("Invalid login credentials")
          ? "Email ou mot de passe incorrect"
          : error.message.includes("Email not confirmed")
          ? "Veuillez confirmer votre email avant de vous connecter"
          : "Une erreur est survenue lors de la connexion";

        toast({
          variant: "destructive",
          title: "Échec de la connexion",
          description: errorMessage,
        });
        return;
      }

      if (!data?.user) {
        throw new Error("Aucune donnée utilisateur retournée");
      }

      console.log("✅ Connexion réussie!", {
        userId: data.user.id,
        timestamp: new Date().toISOString()
      });

      toast({
        title: "Connexion réussie",
        description: "Bienvenue!",
      });

      navigate("/dashboard");
    } catch (error: any) {
      console.error("❌ Erreur inattendue:", error);
      
      toast({
        variant: "destructive",
        title: "Erreur inattendue",
        description: "Une erreur est survenue lors de la connexion. Veuillez réessayer.",
      });
    } finally {
      setLoading(false);
    }
  }, [email, password, toast, navigate]);

  const handleForgotPassword = () => {
    setIsResetDialogOpen(true);
  };

  return (
    <>
      <form onSubmit={handleLogin} className="space-y-4">
        <LoginFormFields 
          email={email}
          setEmail={setEmail}
          password={password}
          setPassword={setPassword}
          onForgotPassword={handleForgotPassword}
        />

        <Button
          type="submit"
          className="w-full bg-[#FF6B35] text-white text-base font-semibold rounded-lg py-2.5 hover:bg-[#FF6B35]/90 transition-colors"
          disabled={loading}
        >
          {loading ? t('common.loading') : 'Connexion'}
        </Button>
      </form>

      <ResetPasswordDialog 
        isOpen={isResetDialogOpen}
        onOpenChange={setIsResetDialogOpen}
      />
    </>
  );
};
