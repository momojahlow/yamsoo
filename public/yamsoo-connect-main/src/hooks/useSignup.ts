
import { useSignupState } from "./useSignupState";
import { createSupabaseUser } from "@/services/auth/signupService";
import { verifyProfileCreation } from "@/services/profile/profileVerificationService";
import { SignupFormData } from "@/utils/signupDataUtils";

// Fix: Use 'export type' when re-exporting a type with 'isolatedModules' enabled
export type { SignupFormData } from "@/utils/signupDataUtils";

export const useSignup = () => {
  const { isLoading, setIsLoading } = useSignupState();

  const signup = async (formData: SignupFormData) => {
    setIsLoading(true);
    
    try {
      const { user } = await createSupabaseUser(formData);
      const profile = await verifyProfileCreation(user.id);
      
      return { user, profile };
    } catch (error) {
      console.error('❌ Erreur détaillée:', {
        error,
        timestamp: new Date().toISOString(),
        context: 'processus d\'inscription'
      });
      throw error;
    } finally {
      setIsLoading(false);
    }
  };

  return { signup, isLoading };
};
