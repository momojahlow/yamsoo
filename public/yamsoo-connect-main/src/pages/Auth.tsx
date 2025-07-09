
import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { supabase } from "@/integrations/supabase/client";
import { LoginForm } from "@/components/auth/LoginForm";
import { AuthContainer } from "@/components/auth/AuthContainer";
import { AuthHeader } from "@/components/auth/AuthHeader";
import SignupForm from "@/components/SignupForm";
import { useTranslation } from "react-i18next";

const Auth = () => {
  const navigate = useNavigate();
  const [isSignup, setIsSignup] = useState(false);
  const { t } = useTranslation();

  useEffect(() => {
    const handleAuthStateChange = (event: string, session: any) => {
      console.log("Auth state changed:", { event, session });
      if (session) {
        navigate("/dashboard");
      }
    };

    supabase.auth.getSession().then(({ data: { session }, error }) => {
      console.log("Session check result:", { session, error });
      
      if (error) {
        console.error("Error checking session:", error);
        return;
      }

      if (session?.user) {
        console.log("Valid session found, redirecting to dashboard");
        navigate("/dashboard");
      }
    });

    const { data: { subscription } } = supabase.auth.onAuthStateChange(handleAuthStateChange);

    // Check if this is a password reset confirmation
    const hashParams = new URLSearchParams(window.location.hash.substring(1));
    const type = hashParams.get('type');
    if (type === 'recovery') {
      // Rediriger vers la page de réinitialisation du mot de passe
      navigate('/auth/reset-password');
    }

    return () => {
      subscription.unsubscribe();
    };
  }, [navigate]);

  return (
    <div className="flex min-h-screen">
      <div className="w-full md:w-2/5">
        {isSignup ? (
          <AuthContainer>
            <div className="text-center mb-6">
              <h2 className="text-2xl font-bold text-terracotta">{t('auth.signup')}</h2>
              <p className="text-sm text-gray-600 mt-1">
                {t('auth.subtitle')}
              </p>
            </div>
            <SignupForm />
            <div className="text-center mt-4">
              <button
                onClick={() => setIsSignup(false)}
                className="text-terracotta hover:underline text-sm"
              >
                {t('auth.alreadyHaveAccount')} {t('auth.signIn')}
              </button>
            </div>
          </AuthContainer>
        ) : (
          <AuthContainer>
            <AuthHeader />
            <LoginForm />
            <div className="text-center text-sm flex justify-center items-center mt-4">
              <p className="text-gray-600">
                {t('auth.needAnAccount')}{" "}
                <button 
                  onClick={() => setIsSignup(true)}
                  className="text-terracotta hover:underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-terracotta"
                >
                  {t('auth.createAccount')}
                </button>
              </p>
            </div>
          </AuthContainer>
        )}
      </div>
      
      {/* Partie droite avec le texte de présentation */}
      <div className="hidden md:flex md:w-3/5 bg-gradient-to-r from-orange-50 to-orange-100 flex-col justify-center px-12">
        <div className="max-w-2xl mx-auto">
          <h1 className="text-4xl font-bold text-terracotta mb-6">Bienvenue sur Yamsoo !</h1>
          
          <div className="space-y-6">
            <div className="bg-white p-6 rounded-lg shadow-sm border border-orange-200">
              <h3 className="text-xl font-semibold text-terracotta mb-2">Connectez votre famille</h3>
              <p className="text-gray-700">Créez votre arbre généalogique et restez en contact avec vos proches, où qu'ils soient dans le monde.</p>
            </div>
            
            <div className="bg-white p-6 rounded-lg shadow-sm border border-orange-200">
              <h3 className="text-xl font-semibold text-terracotta mb-2">Partagez vos moments</h3>
              <p className="text-gray-700">Échangez des photos, des messages et des souvenirs avec votre famille en toute sécurité.</p>
            </div>
            
            <div className="bg-white p-6 rounded-lg shadow-sm border border-orange-200">
              <h3 className="text-xl font-semibold text-terracotta mb-2">Organisez des événements</h3>
              <p className="text-gray-700">Planifiez facilement vos réunions familiales et gardez tout le monde informé.</p>
            </div>
          </div>
          
          <p className="mt-8 text-gray-600 text-center">La plateforme qui réunit les familles, préserve les histoires et renforce vos liens.</p>
        </div>
      </div>
    </div>
  );
};

export default Auth;
