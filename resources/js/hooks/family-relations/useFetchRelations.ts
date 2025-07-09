
import { useState } from "react";
import { supabase } from "@/integrations/supabase/client";
import { FamilyRelation, DbFamilyRelationType } from "@/types/family";
import { useToast } from "@/hooks/use-toast";
import { genderFromRelationType } from "@/utils/relationUtils";

export function useFetchRelations() {
  const [isLoading, setIsLoading] = useState(false);
  const { toast } = useToast();

  const fetchRelations = async () => {
    setIsLoading(true);
    
    try {
      const { data: { user }, error: authError } = await supabase.auth.getUser();
      
      if (authError || !user) {
        console.error('Erreur d\'authentification:', authError);
        return null;
      }

      console.log("Fetching relations for user:", user.id);

      // Modified query to avoid foreign key relationship issues
      // Get sent relations
      const { data: sentRelations, error: sentError } = await supabase
        .from('family_relations')
        .select(`
          id,
          user_id,
          related_user_id,
          relation_type,
          status,
          created_at,
          updated_at
        `)
        .eq('user_id', user.id)
        .eq('status', 'accepted');

      if (sentError) {
        console.error('Erreur lors de la récupération des relations envoyées:', sentError);
        toast({
          title: "Erreur",
          description: "Impossible de charger vos relations familiales",
          variant: "destructive",
        });
        return null;
      }

      // Get received relations
      const { data: receivedRelations, error: receivedError } = await supabase
        .from('family_relations')
        .select(`
          id,
          user_id,
          related_user_id,
          relation_type,
          status,
          created_at,
          updated_at
        `)
        .eq('related_user_id', user.id)
        .eq('status', 'accepted');

      if (receivedError) {
        console.error('Erreur lors de la récupération des relations reçues:', receivedError);
        toast({
          title: "Erreur",
          description: "Impossible de charger vos relations familiales",
          variant: "destructive",
        });
        return null;
      }

      // Combine relations
      const allRelations = [...(sentRelations || []), ...(receivedRelations || [])];
      console.log("Combined relations:", allRelations);

      // Now get profiles for these relations
      if (allRelations.length > 0) {
        const profileIds = allRelations.flatMap(rel => [rel.user_id, rel.related_user_id]);
        const uniqueProfileIds = [...new Set(profileIds)];
        
        const { data: profiles, error: profilesError } = await supabase
          .from('profiles')
          .select('*')
          .in('id', uniqueProfileIds);
          
        if (profilesError) {
          console.error('Erreur lors de la récupération des profils:', profilesError);
          return null;
        }
        
        // Create a map of profiles for quick lookup
        const profileMap = new Map();
        profiles?.forEach(profile => {
          profileMap.set(profile.id, profile);
        });
        
        // Attach profiles to relations and ensure proper typing
        const relationsWithProfiles = allRelations.map(relation => {
          const isInverse = relation.related_user_id === user.id;
          const relatedProfileId = isInverse ? relation.user_id : relation.related_user_id;
          const relatedProfile = profileMap.get(relatedProfileId);
          
          console.log("Processing relation:", relation.relation_type, "isInverse:", isInverse);
          
          // Get the appropriate relation type
          let relationType = isInverse 
            ? getInverseRelationType(relation.relation_type as DbFamilyRelationType) 
            : relation.relation_type as DbFamilyRelationType;
            
          // Verify if the relation type matches the gender of the related profile
          if (relatedProfile && relatedProfile.gender) {
            const expectedGender = genderFromRelationType(relationType);
            if (expectedGender && expectedGender !== relatedProfile.gender) {
              console.warn(`Gender mismatch for ${relatedProfile.first_name} ${relatedProfile.last_name}: relation ${relationType} expects ${expectedGender} but profile has ${relatedProfile.gender}`);
              
              // Correct the relation type based on gender
              if (relationType === 'sister' && relatedProfile.gender === 'M') {
                relationType = 'brother' as DbFamilyRelationType;
                console.log(`Corrected relation type from 'sister' to 'brother' for ${relatedProfile.first_name}`);
              } else if (relationType === 'brother' && relatedProfile.gender === 'F') {
                relationType = 'sister' as DbFamilyRelationType;
                console.log(`Corrected relation type from 'brother' to 'sister' for ${relatedProfile.first_name}`);
              }
              
              // Add more corrections for other gender-specific relation types if needed
            }
          }
            
          console.log("Resulting relation type:", relationType);
          
          return {
            ...relation,
            relation_type: relationType,
            related_profile: profileMap.get(relatedProfileId),
            user_profile: profileMap.get(user.id)
          } as FamilyRelation;
        });
        
        return relationsWithProfiles;
      }
      
      return [];
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
