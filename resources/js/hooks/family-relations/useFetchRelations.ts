
import { useState } from "react";
import { FamilyRelation, DbFamilyRelationType } from "@/types/family";
import { useToast } from "@/hooks/use-toast";
import { genderFromRelationType } from "@/utils/relationUtils";

export function useFetchRelations() {
  const [isLoading, setIsLoading] = useState(false);
  const { toast } = useToast();

  const fetchRelations = async () => {
    setIsLoading(true);

    try {
      // Utiliser l'API Laravel au lieu de Supabase
      const response = await fetch('/api/family-relations', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'include',
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      console.log("Fetching relations from Laravel API:", data);

      // Les données sont déjà formatées par l'API Laravel
      const allRelations = data.relations || [];
      const userId = data.userId;

      console.log("Relations from Laravel:", allRelations);

      // Les profils sont déjà inclus dans les données Laravel
      if (allRelations.length > 0) {
        // Transformer les données Laravel en format attendu par le frontend
        const relationsWithProfiles = allRelations.map(relation => {
          const relatedUser = relation.related_user;
          const relatedProfile = relatedUser.profile;

          console.log("Processing relation:", relation.relation_type);

          // Le type de relation est déjà correct depuis l'API Laravel
          let relationType = relation.relation_type as DbFamilyRelationType;

          // Vérifier si le type de relation correspond au genre du profil
          if (relatedProfile && relatedProfile.gender) {
            const expectedGender = genderFromRelationType(relationType);
            if (expectedGender && expectedGender !== relatedProfile.gender) {
              console.warn(`Gender mismatch for ${relatedProfile.first_name} ${relatedProfile.last_name}: relation ${relationType} expects ${expectedGender} but profile has ${relatedProfile.gender}`);

              // Corriger le type de relation basé sur le genre
              if (relationType === 'sister' && relatedProfile.gender === 'M') {
                relationType = 'brother' as DbFamilyRelationType;
                console.log(`Corrected relation type from 'sister' to 'brother' for ${relatedProfile.first_name}`);
              } else if (relationType === 'brother' && relatedProfile.gender === 'F') {
                relationType = 'sister' as DbFamilyRelationType;
                console.log(`Corrected relation type from 'brother' to 'sister' for ${relatedProfile.first_name}`);
              }
            }
          }

          console.log("Resulting relation type:", relationType);

          return {
            ...relation,
            relation_type: relationType,
            related_profile: relatedProfile,
            user_profile: null // Pas besoin du profil utilisateur ici
          } as FamilyRelation;
        });
        
        return {
          relations: relationsWithProfiles,
          userId: userId
        };
      }

      return {
        relations: [],
        userId: userId
      };
    } catch (error) {
      console.error('Erreur lors de la récupération des relations:', error);
      toast({
        title: "Erreur",
        description: "Une erreur est survenue lors du chargement des relations familiales",
        variant: "destructive",
      });
      return null;
    } finally {
      setIsLoading(false);
    }
  };

  return {
    fetchRelations,
    isLoading
  };
}

// Helper function to get the inverse relation type
function getInverseRelationType(relationType: DbFamilyRelationType): DbFamilyRelationType {
  const inverseMap: Record<string, DbFamilyRelationType> = {
    'father': 'son',
    'mother': 'daughter',
    'son': 'father',
    'daughter': 'mother',
    'brother': 'brother',
    'sister': 'sister',
    'grandfather': 'grandson',
    'grandmother': 'granddaughter',
    'grandson': 'grandfather',
    'granddaughter': 'grandmother',
    'uncle': 'nephew',
    'aunt': 'niece',
    'nephew': 'uncle',
    'niece': 'aunt',
    'cousin': 'cousin',
    'husband': 'wife',
    'wife': 'husband',
    'spouse': 'spouse',
  } as Record<string, DbFamilyRelationType>;

  console.log("Getting inverse for:", relationType, "Result:", inverseMap[relationType] || relationType);
  return inverseMap[relationType] || relationType;
}
