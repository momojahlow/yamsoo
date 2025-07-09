
import { toast } from "@/hooks/use-toast";

/**
 * Share an image URL using the Web Share API or clipboard fallback
 */
export const shareImage = async (imageUrl: string): Promise<void> => {
  try {
    if (navigator.share) {
      try {
        // Tentative de partage direct de l'URL
        await navigator.share({
          title: "Image partagée depuis Yamsoo",
          url: imageUrl
        });
        toast({
          title: "Partage réussi",
          description: "Le lien de l'image a été partagé avec succès",
        });
        return;
      } catch (err) {
        console.log("Erreur lors du partage direct de l'URL, tentative avec blob...", err);
      }
    }
    
    // Fallback pour les navigateurs qui ne supportent pas Web Share API
    // ou si le partage direct de l'URL a échoué
    navigator.clipboard.writeText(imageUrl);
    toast({
      title: "Lien copié",
      description: "Le lien de l'image a été copié dans le presse-papier",
    });
  } catch (error) {
    console.error("Erreur lors du partage:", error);
    toast({
      title: "Erreur",
      description: "Impossible de partager l'image",
      variant: "destructive",
    });
  }
};
