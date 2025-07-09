
import { Sparkles } from "lucide-react";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";
import { AppSidebar } from "@/components/app/Sidebar";
import { useIsMobile } from "@/hooks/use-mobile";
import { useProfile } from "@/hooks/useProfile";
import { SuggestionsList } from "@/components/suggestions/SuggestionsList";
import { useSuggestions } from "@/hooks/useSuggestions";
import { Profile as NotificationProfile } from "@/types/notifications";
import { useMemo } from "react";
import { useNavigate } from "react-router-dom";

export default function Suggestions() {
  const isMobile = useIsMobile();
  const { profile, loading: profileLoading } = useProfile();
  const navigate = useNavigate();
  
  // Utilisation de useMemo pour éviter la recréation du profil typé à chaque rendu
  const typedProfile = useMemo(() => {
    if (!profile || !profile.id) return null;
    
    return {
      id: profile.id,
      first_name: profile.first_name,
      last_name: profile.last_name,
      avatar_url: profile.avatar_url,
      gender: profile.gender,
      email: profile.email
    } as NotificationProfile;
  }, [profile]);
  
  const { suggestions, loading: suggestionsLoading, handleAcceptSuggestion, handleRejectSuggestion } = useSuggestions(typedProfile);
  
  // Combine loading states
  const loading = profileLoading || suggestionsLoading;

  return (
    <div className="flex h-screen w-full">
      {!isMobile && <AppSidebar />}
      
      <div className="relative flex flex-1 flex-col overflow-hidden">
        {isMobile && <MobileNavBar />}
        
        <main className="flex-1 overflow-auto p-4 md:p-6 pb-16 md:pb-6 safe-top safe-bottom">
          <div className="max-w-4xl mx-auto">
            <div className="flex items-center gap-2 mb-6">
              <Sparkles className="h-6 w-6 text-amber-500" />
              <h1 className="text-2xl font-bold">Suggestions</h1>
            </div>
            
            <SuggestionsList 
              suggestions={suggestions}
              loading={loading}
              onAcceptSuggestion={handleAcceptSuggestion}
              onRejectSuggestion={handleRejectSuggestion}
            />
          </div>
        </main>
      </div>
    </div>
  );
}
