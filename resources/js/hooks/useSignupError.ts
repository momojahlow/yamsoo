
import { useToast } from "@/hooks/use-toast";

export const useSignupError = () => {
  const { toast } = useToast();

  const handleSignupError = (error: unknown) => {
    console.error("Detailed signup error:", error);
    
    let errorMessage = "Une erreur est survenue lors de l'inscription";
    let errorDetail = "";
    
    if (error instanceof Error) {
      console.log("Error message:", error.message);
      console.log("Error name:", error.name);
      
      // Gérer spécifiquement le cas de l'email déjà existant
      if (error.message.includes("Email already exists") || error.message.includes("User already registered")) {
        errorMessage = "Email déjà utilisé";
        errorDetail = "Cette adresse email est déjà associée à un compte. Veuillez utiliser une autre adresse email ou vous connecter.";
      } else if (error.message.includes("Invalid email")) {
        errorMessage = "Email invalide";
        errorDetail = "Veuillez vérifier le format de votre adresse email";
      } else if (error.message.includes("Password")) {
        errorMessage = "Mot de passe invalide";
        errorDetail = "Le mot de passe doit contenir au moins 6 caractères";
      }
    }
    
    toast({
      variant: "destructive",
      title: errorMessage,
      description: errorDetail || "Veuillez réessayer ou contacter le support si le problème persiste.",
    });
  };

  return { handleSignupError };
};
