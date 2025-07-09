
import { useState } from "react";
import { supabase } from "@/integrations/supabase/client";
import { FamilyRelationType, FamilyRelationStatus, DbFamilyRelationType } from "@/types/family";
import { useToast } from "@/hooks/use-toast";
import { isValidRelationType, getValidDbRelationType } from "./relationTypeUtils";

export function useAddRelation() {
  const [isLoading, setIsLoading] = useState(false);
  const { toast } = useToast();

  const addRelation = async (relatedUserId: string, relationType: FamilyRelationType) => {
    setIsLoading(true);
    
    try {
      const { data: { user }, error: authError } = await supabase.auth.getUser();
      
      if (authError || !user) {
        console.error('Erreur d\'authentification:', authError);
        toast({
          title: "Erreur d'authentification",
          description: "Votre session a expiré. Veuillez vous reconnecter.",
          variant: "destructive",
        });
        return false;
      }
      
      // Vérification que le type de relation est valide
      if (!isValidRelationType(relationType)) {
        console.error('Type de relation invalide:', relationType);
        toast({
          title: "Erreur de validation",
          description: `Le type de relation "${relationType}" n'est pas valide.`,
          variant: "destructive",
        });
        return false;
      }

      // Vérification qu'une demande de relation n'existe pas déjà
      const { data: existingRelations, error: existingError } = await supabase
        .from('family_relations')
        .select('id, status')
        .or(`and(user_id.eq.${user.id},related_user_id.eq.${relatedUserId}),and(user_id.eq.${relatedUserId},related_user_id.eq.${user.id})`);
      
      if (existingError) {
        console.error('Erreur lors de la vérification des relations existantes:', existingError);
        toast({
          title: "Erreur",
          description: "Impossible de vérifier les relations existantes.",
          variant: "destructive",
        });
        return false;
      }
      
      // Si une relation existe déjà, afficher un message approprié
      if (existingRelations && existingRelations.length > 0) {
        const pendingRelation = existingRelations.find(rel => rel.status === 'pending');
        const acceptedRelation = existingRelations.find(rel => rel.status === 'accepted');
        
        if (pendingRelation) {
          toast({
            title: "Demande déjà existante",
            description: "Une demande de relation avec cette personne est déjà en attente.",
            variant: "default",
          });
          return false;
        }
        
        if (acceptedRelation) {
          toast({
            title: "Relation déjà existante",
            description: "Vous avez déjà une relation établie avec cette personne.",
            variant: "default",
          });
          return false;
        }
      }
      
      // Convertir le type de relation UI en type de relation DB valide
      const dbRelationType = getValidDbRelationType(relationType);
      
      // S'assurer que le status est un des types acceptés par la DB
      const status: FamilyRelationStatus = 'pending';

      console.log(`Tentative d'ajout de relation: user_id=${user.id}, related_user_id=${relatedUserId}, relation_type=${dbRelationType}, status=${status}`);
      
      // Utiliser un objet intermédiaire et une conversion de type explicite
      // Utiliser "as const" pour que TypeScript traite les valeurs comme des littéraux de type
      const relationData = {
        user_id: user.id,
        related_user_id: relatedUserId,
        relation_type: dbRelationType as any, // Utiliser 'any' temporairement pour contourner le problème de type
        status: status
      };
      
      // Insérer dans la table family_relations
      const { error } = await supabase
        .from('family_relations')
        .insert(relationData);
      
      if (error) {
        console.error('Error inserting relation:', error);
        toast({
          title: "Erreur",
          description: "Impossible d'ajouter la relation: " + error.message,
          variant: "destructive",
        });
        return false;
      }
      
      toast({
        title: "Succès",
        description: "Demande de relation envoyée avec succès!",
      });
      
      return true;
    } catch (error) {
      console.error('Error adding relation:', error);
      toast({
        title: "Erreur système",
        description: "Une erreur inattendue s'est produite",
        variant: "destructive",
      });
      return false;
    } finally {
      setIsLoading(false);
    }
  };

  return {
    addRelation,
    isLoading
  };
}
