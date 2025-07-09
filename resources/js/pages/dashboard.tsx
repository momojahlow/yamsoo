
import { SidebarProvider } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/app/Sidebar";
import { ProfileAvatar } from "@/components/profile/ProfileAvatar";
import { ProfileForm } from "@/components/profile/ProfileForm";
import { useIsMobile } from "@/hooks/use-mobile";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";

interface User {
  id: number;
  name: string;
  email: string;
  family?: Record<string, unknown>;
}

interface Profile {
  id?: number;
  first_name?: string;
  last_name?: string;
  bio?: string;
  avatar?: string;
}

interface DashboardProps {
  user: User;
  profile: Profile | null;
  notifications: Array<Record<string, unknown>>;
  messages: Array<Record<string, unknown>>;
  unreadNotifications: number;
}

const Dashboard = ({ user, profile, notifications, messages, unreadNotifications }: DashboardProps) => {
  const isMobile = useIsMobile();

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full bg-background">
        <AppSidebar />
        <main className="flex-1 p-4 md:p-8 md:ml-16 pb-20 md:pb-8">
          <div className="max-w-2xl mx-auto">
            <div className="space-y-8">
              {/* En-tête du profil */}
              <div className="flex items-center space-x-4">
                <ProfileAvatar
                  avatarUrl={profile?.avatar || null}
                  firstName={profile?.first_name || ""}
                  lastName={profile?.last_name || ""}
                />
                <div>
                  <h1 className="text-2xl font-bold">
                    {profile?.first_name && profile?.last_name
                      ? `${profile.first_name} ${profile.last_name}`
                      : user?.name || "Utilisateur"}
                  </h1>
                  <p className="text-muted-foreground">
                    {profile?.bio || "Aucune bio disponible"}
                  </p>
                </div>
              </div>

              {/* Statistiques rapides */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="bg-card p-4 rounded-lg border">
                  <h3 className="font-semibold">Messages</h3>
                  <p className="text-2xl font-bold">{messages.length}</p>
                </div>
                <div className="bg-card p-4 rounded-lg border">
                  <h3 className="font-semibold">Notifications</h3>
                  <p className="text-2xl font-bold">{notifications.length}</p>
                  {unreadNotifications > 0 && (
                    <span className="text-sm text-red-500">
                      {unreadNotifications} non lues
                    </span>
                  )}
                </div>
                <div className="bg-card p-4 rounded-lg border">
                  <h3 className="font-semibold">Famille</h3>
                  <p className="text-2xl font-bold">
                    {user?.family ? "Connecté" : "Non connecté"}
                  </p>
                </div>
              </div>

              {/* Formulaire de profil */}
              <div className="bg-card p-6 rounded-lg border">
                <h2 className="text-xl font-semibold mb-4">Mon Profil</h2>
                {profile && (
                  <ProfileForm
                    profile={profile}
                    onSubmit={() => {}}
                    onInputChange={() => {}}
                    onGenderChange={() => {}}
                  />
                )}
              </div>
            </div>
          </div>
        </main>
        {isMobile && <MobileNavBar />}
      </div>
    </SidebarProvider>
  );
};

export default Dashboard;
