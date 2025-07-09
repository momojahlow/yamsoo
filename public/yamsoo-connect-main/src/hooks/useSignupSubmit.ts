
import { useCallback } from "react";
import { useNavigate } from "react-router-dom";
import { useToast } from "@/hooks/use-toast";
import { useSignupError } from "@/hooks/useSignupError";
import { supabase } from "@/integrations/supabase/client";
import { sendWelcomeEmail } from "@/services/email/welcomeEmailService";

interface FormData {
  firstName: string;
  lastName: string;
  email: string;
  password: string;
  confirmPassword: string;
  mobile: string;
  birthDate: string;
  gender: string;
}

export const useSignupSubmit = () => {
  const { toast } = useToast();
  const navigate = useNavigate();
  const { handleSignupError } = useSignupError();

  const submitSignup = useCallback(async (formData: FormData) => {
    console.log("🚀 Form submission started", {
      timestamp: new Date().toISOString(),
      formData: {
        ...formData,
        password: "[REDACTED]",
        confirmPassword: "[REDACTED]"
      }
    });
    
    try {
      console.log("📝 Preparing signup data...");
      const { data, error } = await supabase.auth.signUp({
        email: formData.email.trim().toLowerCase(),
        password: formData.password,
        options: {
          data: {
            first_name: formData.firstName.trim(),
            last_name: formData.lastName.trim(),
            mobile: formData.mobile?.trim() || null,
            birth_date: formData.birthDate || null,
            gender: formData.gender || null
          }
        }
      });

      if (error) {
        console.error("❌ Signup error:", {
          error,
          errorMessage: error.message,
          errorName: error.name,
          timestamp: new Date().toISOString()
        });
        throw error;
      }

      if (!data?.user) {
        console.error("❌ No user data returned");
        throw new Error("Erreur lors de la création du compte");
      }

      console.log("✅ Signup successful!", {
        userId: data.user.id,
        timestamp: new Date().toISOString()
      });

      await sendWelcomeEmail(formData.firstName, formData.email);
      
      toast({
        title: "Inscription réussie",
        description: "Votre compte a été créé avec succès",
      });
      
      navigate("/auth");
      
      return { success: true };
    } catch (error) {
      console.error("❌ Detailed signup error:", {
        error,
        timestamp: new Date().toISOString(),
        formData: {
          ...formData,
          password: "[REDACTED]",
          confirmPassword: "[REDACTED]"
        }
      });
      handleSignupError(error);
      return { success: false, error };
    }
  }, [toast, navigate, handleSignupError]);

  return { submitSignup };
};
