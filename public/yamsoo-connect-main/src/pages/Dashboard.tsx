
import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { useToast } from "@/hooks/use-toast";
import { supabase } from "@/integrations/supabase/client";
import { SidebarProvider } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/app/Sidebar";
import { ProfileAvatar } from "@/components/profile/ProfileAvatar";
import { ProfileForm } from "@/components/profile/ProfileForm";
import { useProfile } from "@/hooks/useProfile";
import { useIsMobile } from "@/hooks/use-mobile";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";

const Dashboard = () => {
  const { t } = useTranslation();
  const { toast } = useToast();
  const navigate = useNavigate();
  const [uploading, setUploading] = useState(false);
  const { user, loading, profile, setProfile } = useProfile();
  const isMobile = useIsMobile();

  useEffect(() => {
    const checkAuth = async () => {
      const { data: { session } } = await supabase.auth.getSession();
      if (!session) {
        navigate("/auth");
      }
    };
    checkAuth();
  }, [navigate]);

  const handleProfileUpdate = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!user) return;

    try {
      const { error } = await supabase
        .from("profiles")
        .update(profile)
        .eq("id", user.id);

      if (error) throw error;

      toast({
        title: t("profile.updateSuccess"),
        description: t("profile.updateSuccessMessage"),
      });
    } catch (error: any) {
      toast({
        variant: "destructive",
        title: t("profile.updateError"),
        description: error.message,
      });
    }
  };

  const handleAvatarUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    try {
      setUploading(true);
      if (!e.target.files || e.target.files.length === 0) {
        throw new Error(t("profile.noImageSelected") || "You must select an image to upload.");
      }

      const file = e.target.files[0];
      const fileExt = file.name.split('.').pop();
      const filePath = `${user?.id}-${Math.random()}.${fileExt}`;

      const { error: uploadError } = await supabase.storage
        .from('avatars')
        .upload(filePath, file);

      if (uploadError) {
        throw uploadError;
      }

      const { data: { publicUrl } } = supabase.storage
        .from('avatars')
        .getPublicUrl(filePath);

      if (!user?.id) throw new Error(t("profile.userNotLoggedIn") || "User not logged in");

      const { error: updateError } = await supabase
        .from('profiles')
        .update({ avatar_url: publicUrl })
        .eq('id', user.id);

      if (updateError) {
        throw updateError;
      }

      setProfile(prev => ({ ...prev, avatar_url: publicUrl }));
      toast({
        title: t("profile.updateSuccess"),
        description: t("profile.photoUpdated") || "Your profile photo has been updated.",
      });
    } catch (error: any) {
      toast({
        variant: "destructive",
        title: t("profile.updateError"),
        description: error.message,
      });
    } finally {
      setUploading(false);
    }
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setProfile(prev => ({ ...prev, [name]: value }));
  };

  const handleGenderChange = (value: string) => {
    setProfile(prev => ({ ...prev, gender: value }));
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-screen">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
      </div>
    );
  }

  if (!user) {
    return (
      <div className="flex justify-center items-center min-h-screen">
        <p className="text-red-500">{t("profile.unauthorized") || "Unauthorized access"}</p>
      </div>
    );
  }

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full bg-background">
        {/* Uniquement visible sur desktop */}
        <AppSidebar />
        <main className="flex-1 p-4 md:p-8 md:ml-16 pb-20 md:pb-8">
          <div className="max-w-2xl mx-auto">
            <h1 className="text-3xl font-bold mb-8">{t("profile.title")}</h1>
            
            <ProfileAvatar
              avatarUrl={profile?.avatar_url}
              firstName={profile?.first_name}
              lastName={profile?.last_name}
              uploading={uploading}
              onAvatarUpload={handleAvatarUpload}
            />
            
            {profile && (
              <ProfileForm
                profile={profile}
                onSubmit={handleProfileUpdate}
                onInputChange={handleInputChange}
                onGenderChange={handleGenderChange}
              />
            )}
          </div>
        </main>
        
        {/* Navigation mobile en bas de l'écran */}
        {isMobile && <MobileNavBar />}
      </div>
    </SidebarProvider>
  );
};

export default Dashboard;
