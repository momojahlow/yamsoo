
import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useToast } from "@/hooks/use-toast";
import { supabase } from "@/integrations/supabase/client";
import { User } from "@supabase/supabase-js";

interface Profile {
  id?: string; // Add id as an optional property
  first_name: string;
  last_name: string;
  email: string;
  mobile: string;
  birth_date: string;
  gender: string;
  avatar_url: string | null;
}

export const useProfile = () => {
  const navigate = useNavigate();
  const { toast } = useToast();
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [profile, setProfile] = useState<Profile>({
    first_name: "",
    last_name: "",
    email: "",
    mobile: "",
    birth_date: "",
    gender: "",
    avatar_url: null,
  });

  const createProfile = async (userId: string, email: string) => {
    try {
      console.log("Création d'un nouveau profil pour l'utilisateur:", userId);
      const { data: userData } = await supabase.auth.getUser();
      const userMetadata = userData.user?.user_metadata || {};

      const { error } = await supabase
        .from("profiles")
        .insert({
          id: userId,
          email: email,
          first_name: userMetadata.first_name || "",
          last_name: userMetadata.last_name || "",
          mobile: userMetadata.mobile || "",
          birth_date: userMetadata.birth_date || null,
          gender: userMetadata.gender || null,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString()
        });

      if (error) throw error;

      console.log("Profil créé avec succès");
      return await fetchProfile(userId);
    } catch (error: any) {
      console.error("Erreur lors de la création du profil:", error);
      throw error;
    }
  };

  const fetchProfile = async (userId: string) => {
    try {
      console.log("Récupération du profil pour l'utilisateur:", userId);
      const { data: userData } = await supabase.auth.getUser();
      const userMetadata = userData.user?.user_metadata || {};
      
      const { data, error } = await supabase
        .from("profiles")
        .select("*")
        .eq("id", userId)
        .maybeSingle();

      if (error) {
        console.error("Erreur lors de la récupération du profil:", error);
        throw error;
      }

      if (data) {
        console.log("Données du profil récupérées:", data);
        // Synchroniser les données du profil avec les métadonnées utilisateur
        const updatedProfile = {
          ...data,
          mobile: data.mobile || userMetadata.mobile || "",
          birth_date: data.birth_date || userMetadata.birth_date || "",
          gender: data.gender || userMetadata.gender || "",
        };
        
        // Mettre à jour le profil si des données sont manquantes
        if (!data.mobile && userMetadata.mobile || 
            !data.birth_date && userMetadata.birth_date || 
            !data.gender && userMetadata.gender) {
          const { error: updateError } = await supabase
            .from("profiles")
            .update(updatedProfile)
            .eq("id", userId);

          if (updateError) {
            console.error("Erreur lors de la mise à jour du profil:", updateError);
          }
        }

        setProfile(updatedProfile);
      } else {
        console.log("Aucun profil trouvé, tentative de création...");
        if (userData.user) {
          await createProfile(userId, userData.user.email || "");
        } else {
          toast({
            variant: "destructive",
            title: "Erreur",
            description: "Impossible de créer le profil : utilisateur non trouvé",
          });
          navigate("/auth");
        }
      }
    } catch (error: any) {
      console.error("Erreur dans fetchProfile:", error);
      toast({
        variant: "destructive",
        title: "Erreur",
        description: error.message,
      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const getSession = async () => {
      try {
        const { data: { session }, error } = await supabase.auth.getSession();
        
        if (error) {
          console.error("Erreur lors de la récupération de la session:", error);
          navigate("/auth");
          return;
        }

        if (!session) {
          console.log("Aucune session trouvée, redirection vers /auth");
          navigate("/auth");
          return;
        }

        setUser(session.user);
        await fetchProfile(session.user.id);
      } catch (error) {
        console.error("Erreur inattendue:", error);
        navigate("/auth");
      }
    };

    getSession();

    const { data: { subscription } } = supabase.auth.onAuthStateChange(async (event, session) => {
      if (event === 'SIGNED_OUT') {
        navigate("/auth");
      } else if (session?.user) {
        setUser(session.user);
        await fetchProfile(session.user.id);
      }
    });

    return () => {
      subscription.unsubscribe();
    };
  }, [navigate]);

  return { user, loading, profile, setProfile };
};
