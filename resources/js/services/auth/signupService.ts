
import { supabase } from "@/integrations/supabase/client";
import { cleanSignupData, validateRequiredFields, SignupFormData } from "@/utils/signupDataUtils";
import { supabaseRpc } from "@/utils/supabaseClient";

export const createSupabaseUser = async (formData: SignupFormData) => {
  console.log("🚀 Démarrage du processus d'inscription avec les données:", {
    ...formData,
    password: "[MASQUÉ]"
  });

  const cleanedData = cleanSignupData(formData);
  console.log("📝 Données nettoyées:", cleanedData);
  
  validateRequiredFields(cleanedData);

  // Vérifier si l'email existe déjà
  const { data: emailExists, error: emailCheckError } = await supabaseRpc.check_email_exists({
    email_to_check: cleanedData.email
  });

  if (emailCheckError) {
    console.error("❌ Erreur lors de la vérification de l'email:", emailCheckError);
  } else if (emailExists) {
    console.error("❌ Email déjà utilisé:", cleanedData.email);
    throw new Error("Email already exists");
  }

  console.log("🔑 Création de l'utilisateur avec les métadonnées:", {
    email: cleanedData.email,
    metadata: {
      first_name: cleanedData.firstName,
      last_name: cleanedData.lastName,
      mobile: cleanedData.mobile,
      birth_date: cleanedData.birthDate,
      gender: cleanedData.gender
    }
  });

  const { data: authData, error: authError } = await supabase.auth.signUp({
    email: cleanedData.email,
    password: formData.password,
    options: {
      data: {
        first_name: cleanedData.firstName,
        last_name: cleanedData.lastName,
        mobile: cleanedData.mobile || null,
        birth_date: cleanedData.birthDate || null,
        gender: cleanedData.gender || null
      }
    }
  });

  if (authError) {
    console.error('❌ Erreur détaillée d\'authentification:', {
      error: authError,
      context: 'processus d\'inscription',
      metadata: cleanedData
    });
    
    // Traitement spécifique pour l'erreur "User already registered"
    if (authError.message.includes("User already registered")) {
      throw new Error("Email already exists");
    }
    
    throw authError;
  }

  if (!authData.user?.id) {
    console.error('❌ Aucun ID utilisateur retourné lors de l\'inscription');
    throw new Error("Erreur lors de la création du compte");
  }

  return { user: authData.user, session: authData.session };
};
