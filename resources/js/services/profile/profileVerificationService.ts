
import { supabase } from "@/integrations/supabase/client";

export const verifyProfileCreation = async (userId: string) => {
  // Attente pour la création du profil par le trigger
  await new Promise(resolve => setTimeout(resolve, 1000));

  // Vérification de la création du profil
  const { data: profileData, error: profileError } = await supabase
    .from('profiles')
    .select('*')
    .eq('id', userId)
    .single();

  if (profileError || !profileData) {
    console.error('❌ Erreur lors de la vérification de la création du profil:', profileError);
    throw new Error("Erreur lors de la création du profil");
  }

  console.log("✅ Utilisateur et profil créés avec succès:", {
    userId,
    profile: profileData
  });

  return profileData;
};
