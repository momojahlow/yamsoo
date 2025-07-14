
import { ProfileAvatar } from "@/components/profile/ProfileAvatar";
import { ProfileForm } from "@/components/profile/ProfileForm";
import { useIsMobile } from "@/hooks/use-mobile";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Users,
  MessageSquare,
  Bell,
  Heart,
  TrendingUp,
  Calendar,
  Plus,
  ArrowRight
} from "lucide-react";
import AppLayout from '@/layouts/app-layout';

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
  email?: string;
  mobile?: string;
  birth_date?: string;
  gender?: string;
  avatar_url?: string | null;
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

  const stats = [
    {
      title: "Membres de famille",
      value: "12",
      icon: Users,
      color: "text-blue-600",
      bgColor: "bg-blue-50",
      change: "+2 ce mois"
    },
    {
      title: "Messages",
      value: messages.length.toString(),
      icon: MessageSquare,
      color: "text-green-600",
      bgColor: "bg-green-50",
      change: "+5 aujourd'hui"
    },
    {
      title: "Notifications",
      value: notifications.length.toString(),
      icon: Bell,
      color: "text-purple-600",
      bgColor: "bg-purple-50",
      change: unreadNotifications > 0 ? `${unreadNotifications} non lues` : "À jour"
    },
    {
      title: "Relations",
      value: "8",
      icon: Heart,
      color: "text-red-600",
      bgColor: "bg-red-50",
      change: "+1 cette semaine"
    }
  ];

  const recentActivities = [
    { id: 1, type: "message", text: "Nouveau message de Marie", time: "Il y a 5 min", avatar: "M" },
    { id: 2, type: "family", text: "Pierre a rejoint la famille", time: "Il y a 1h", avatar: "P" },
    { id: 3, type: "notification", text: "Nouvelle suggestion de relation", time: "Il y a 2h", avatar: "S" },
    { id: 4, type: "message", text: "Message de groupe mis à jour", time: "Il y a 3h", avatar: "G" }
  ];

  return (
    <AppLayout>
      <div className="min-h-screen flex w-full bg-gray-50 dark:bg-gray-900">
        <main className="flex-1 p-6 md:p-8 md:ml-16 pb-20 md:pb-8">
          <div className="max-w-7xl mx-auto">
            {/* Header */}
            <div className="mb-8">
              <div className="flex items-center justify-between">
                <div>
                  <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                    Tableau de bord
                  </h1>
                  <p className="text-gray-600 dark:text-gray-400 mt-1">
                    Bienvenue, {profile?.first_name || user?.name || "Utilisateur"}
                  </p>
                </div>
                <Button className="hidden md:flex">
                  <Plus className="w-4 h-4 mr-2" />
                  Nouvelle action
                </Button>
              </div>
            </div>

            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
              {stats.map((stat, index) => (
                <Card key={index} className="border-0 shadow-sm hover:shadow-md transition-shadow">
                  <CardContent className="p-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-sm font-medium text-gray-600 dark:text-gray-400">
                          {stat.title}
                        </p>
                        <p className="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                          {stat.value}
                        </p>
                        <p className="text-xs text-gray-500 mt-1">
                          {stat.change}
                        </p>
                      </div>
                      <div className={`p-3 rounded-lg ${stat.bgColor}`}>
                        <stat.icon className={`w-6 h-6 ${stat.color}`} />
                      </div>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
              {/* Profile Section */}
              <div className="lg:col-span-2">
                <Card className="border-0 shadow-sm">
                  <CardHeader>
                    <CardTitle className="flex items-center">
                      <div className="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <Users className="w-4 h-4 text-blue-600" />
                      </div>
                      Mon Profil
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                                         <div className="flex items-start space-x-4 mb-6">
                       <ProfileAvatar
                         avatarUrl={profile?.avatar || null}
                         firstName={profile?.first_name || ""}
                         lastName={profile?.last_name || ""}
                         uploading={false}
                         onAvatarUpload={() => {}}
                       />
                      <div className="flex-1">
                        <h3 className="text-lg font-semibold">
                          {profile?.first_name && profile?.last_name
                            ? `${profile.first_name} ${profile.last_name}`
                            : user?.name || "Utilisateur"}
                        </h3>
                        <p className="text-gray-600 dark:text-gray-400 text-sm">
                          {profile?.bio || "Aucune bio disponible"}
                        </p>
                        <div className="flex items-center mt-2 space-x-2">
                          <Badge variant="secondary">Membre actif</Badge>
                          <Badge variant="outline">Famille connectée</Badge>
                        </div>
                      </div>
                    </div>

                                         {profile && (
                       <ProfileForm
                         profile={{
                           first_name: profile.first_name || "",
                           last_name: profile.last_name || "",
                           email: profile.email || "",
                           mobile: profile.mobile || "",
                           birth_date: profile.birth_date || "",
                           gender: profile.gender || "",
                           avatar_url: profile.avatar_url || profile.avatar || null
                         }}
                         onSubmit={() => {}}
                         onInputChange={() => {}}
                         onGenderChange={() => {}}
                       />
                     )}
                  </CardContent>
                </Card>
              </div>

              {/* Recent Activity */}
              <div>
                <Card className="border-0 shadow-sm">
                  <CardHeader>
                    <CardTitle className="flex items-center justify-between">
                      <div className="flex items-center">
                        <div className="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                          <TrendingUp className="w-4 h-4 text-green-600" />
                        </div>
                        Activité récente
                      </div>
                      <Button variant="ghost" size="sm">
                        <ArrowRight className="w-4 h-4" />
                      </Button>
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-4">
                      {recentActivities.map((activity) => (
                        <div key={activity.id} className="flex items-start space-x-3">
                          <div className="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <span className="text-sm font-medium text-gray-600">
                              {activity.avatar}
                            </span>
                          </div>
                          <div className="flex-1 min-w-0">
                            <p className="text-sm text-gray-900 dark:text-white">
                              {activity.text}
                            </p>
                            <p className="text-xs text-gray-500 mt-1">
                              {activity.time}
                            </p>
                          </div>
                        </div>
                      ))}
                    </div>
                  </CardContent>
                </Card>

                {/* Quick Actions */}
                <Card className="border-0 shadow-sm mt-6">
                  <CardHeader>
                    <CardTitle className="flex items-center">
                      <div className="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                        <Calendar className="w-4 h-4 text-purple-600" />
                      </div>
                      Actions rapides
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-3">
                      <Button variant="outline" className="w-full justify-start">
                        <MessageSquare className="w-4 h-4 mr-2" />
                        Nouveau message
                      </Button>
                      <Button variant="outline" className="w-full justify-start">
                        <Users className="w-4 h-4 mr-2" />
                        Inviter un membre
                      </Button>
                      <Button variant="outline" className="w-full justify-start">
                        <Heart className="w-4 h-4 mr-2" />
                        Ajouter une relation
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              </div>
            </div>
          </div>
        </main>
        {isMobile && <MobileNavBar />}
      </div>
    </AppLayout>
  );
};

export default Dashboard;
