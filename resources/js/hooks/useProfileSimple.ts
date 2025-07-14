import { useState } from "react";
import { useToast } from "@/hooks/use-toast";

interface Profile {
  id?: string;
  first_name: string;
  last_name: string;
  email: string;
  mobile: string;
  birth_date: string;
  gender: string;
  avatar_url: string | null;
}

export const useProfile = () => {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [profile, setProfile] = useState<Profile | null>(null);

  // Pour Laravel Breeze, le profil sera géré côté serveur
  // Ce hook est simplifié pour éviter les conflits avec React Router
  
  return {
    profile,
    loading,
    setProfile,
    toast
  };
};
