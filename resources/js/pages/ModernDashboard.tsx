import React, { useState, useEffect } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Progress } from "@/components/ui/progress";
import { Separator } from "@/components/ui/separator";
import { KwdDashboardLayout } from '@/layouts/modern';
import { useTranslation } from '@/hooks/useTranslation';
import {
  Users, Heart, Plus, ArrowRight, TreePine, UserPlus, Bell, Activity,
  Sparkles, Clock, Crown, Camera, MessageSquare, Calendar, MapPin,
  TrendingUp, Star, Gift, Zap, Globe, Shield, Settings, ChevronRight,
  BarChart3, PieChart, LineChart, Target, Award, Flame
} from "lucide-react";

interface User {
  id: number;
  name: string;
  email: string;
  profile?: {
    first_name?: string;
    last_name?: string;
    bio?: string;
    avatar?: string;
    birth_date?: string;
    gender?: 'male' | 'female';
  };
}

interface DashboardStats {
  total_family_members: number;
  new_members_this_month: number;
  new_members_this_week: number;
  pending_suggestions: number;
  new_suggestions_this_week: number;
  total_suggestions: number;
  automatic_relations: number;
  manual_relations: number;
  total_photos: number;
  total_albums: number;
  total_messages: number;
  active_conversations: number;
}

interface Activity {
  id: string;
  type: 'relation' | 'suggestion' | 'photo' | 'message';
  title: string;
  description: string;
  time: string;
  user?: User;
  icon: string;
}

interface DynamicBadges {
  notifications: number;
  suggestions: number;
  new_suggestions: number;
  pending_requests: number;
  albums: number;
  unread_messages: number;
  active_conversations: number;
  upcoming_events: number;
  total_badges: number;
}

interface Props {
  auth: { user: User };
  stats: DashboardStats;
  recent_activities: Activity[];
  family_members: User[];
  pending_suggestions: any[];
  badges: DynamicBadges;
}

export default function ModernDashboard({ auth, stats, recent_activities, family_members, pending_suggestions, badges }: Props) {
  const { t } = useTranslation();
  const [currentTime, setCurrentTime] = useState(new Date());

  useEffect(() => {
    const timer = setInterval(() => setCurrentTime(new Date()), 1000);
    return () => clearInterval(timer);
  }, []);

  const getGreeting = () => {
    const hour = currentTime.getHours();
    if (hour < 12) return "Bonjour";
    if (hour < 18) return "Bon après-midi";
    return "Bonsoir";
  };

  const quickActions = [
    {
      title: "Ajouter un membre",
      description: "Inviter un nouveau membre de la famille",
      icon: UserPlus,
      href: "/reseaux",
      color: "bg-gradient-to-br from-blue-500 to-blue-600",
      badge: badges.suggestions > 0 ? badges.suggestions : null,
      urgentBadge: badges.pending_requests > 0 ? badges.pending_requests : null
    },
    {
      title: "Albums photo",
      description: "Voir et gérer vos albums",
      icon: Camera,
      href: "/photo-albums",
      color: "bg-gradient-to-br from-orange-500 to-red-500",
      badge: badges.albums > 0 ? badges.albums : null
    },
    {
      title: "Messagerie",
      description: "Conversations en famille",
      icon: MessageSquare,
      href: "/messagerie",
      color: "bg-gradient-to-br from-green-500 to-emerald-600",
      badge: badges.active_conversations > 0 ? badges.active_conversations : null,
      urgentBadge: badges.unread_messages > 0 ? badges.unread_messages : null
    },
    {
      title: "Arbre familial",
      description: "Explorer votre généalogie",
      icon: TreePine,
      href: "/famille/arbre",
      color: "bg-gradient-to-br from-purple-500 to-purple-600",
      badge: null
    }
  ];

  const StatCard = ({ title, value, change, icon: Icon, color, trend }: any) => (
    <Card className="relative overflow-hidden border-0 shadow-lg hover:shadow-xl transition-all duration-300 group">
      <div className={`absolute inset-0 ${color} opacity-5 group-hover:opacity-10 transition-opacity`} />
      <CardContent className="p-6">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-sm font-medium text-gray-600 mb-1">{title}</p>
            <p className="text-3xl font-bold text-gray-900">{value}</p>
            {change && (
              <div className="flex items-center mt-2">
                <TrendingUp className={`w-4 h-4 mr-1 ${trend === 'up' ? 'text-green-500' : 'text-red-500'}`} />
                <span className={`text-sm font-medium ${trend === 'up' ? 'text-green-600' : 'text-red-600'}`}>
                  {change}
                </span>
              </div>
            )}
          </div>
          <div className={`p-3 rounded-xl ${color} bg-opacity-10`}>
            <Icon className={`w-6 h-6 ${color.replace('bg-', 'text-')}`} />
          </div>
        </div>
      </CardContent>
    </Card>
  );

  return (
    <KwdDashboardLayout title="Dashboard" badges={badges}>
      <Head title="Dashboard" />

      <div className="space-y-8">
        {/* Header avec salutation */}
        <div className="bg-gradient-to-br from-orange-50 via-red-50 to-pink-50 rounded-2xl p-6 md:p-8 border border-orange-100 shadow-sm">
          <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div className="flex items-center gap-4">
              <Avatar className="w-16 h-16 border-4 border-white shadow-lg">
                <AvatarImage src={auth.user.profile?.avatar} />
                <AvatarFallback className="bg-gradient-to-br from-orange-500 to-red-500 text-white text-xl font-bold">
                  {auth.user.name.charAt(0)}
                </AvatarFallback>
              </Avatar>
              <div>
                <h1 className="text-3xl md:text-4xl font-bold text-gray-900">
                  {getGreeting()}, {auth.user.profile?.first_name || auth.user.name} !
                </h1>
                <p className="text-gray-600 mt-1">
                  {currentTime.toLocaleDateString('fr-FR', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                  })}
                </p>
              </div>
            </div>

            <div className="flex items-center gap-3">
              <Button variant="outline" className="border-orange-200 hover:bg-orange-50 relative">
                <Bell className="w-4 h-4 mr-2" />
                Notifications
                {badges.total_badges > 0 && (
                  <Badge className="ml-2 bg-red-500 text-white animate-pulse">
                    {badges.total_badges}
                  </Badge>
                )}
                {badges.notifications > 0 && (
                  <div className="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-ping" />
                )}
              </Button>

              <Button className="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600">
                <Sparkles className="w-4 h-4 mr-2" />
                Yamsoo AI
              </Button>
            </div>
          </div>
        </div>

        {/* Statistiques principales */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <StatCard
            title="Membres de la famille"
            value={stats.total_family_members}
            change={`+${stats.new_members_this_month} ce mois`}
            icon={Users}
            color="bg-blue-500"
            trend="up"
          />
          <StatCard
            title="Relations découvertes"
            value={stats.automatic_relations + stats.manual_relations}
            change={`${stats.automatic_relations} automatiques`}
            icon={Heart}
            color="bg-red-500"
            trend="up"
          />
          <StatCard
            title="Photos partagées"
            value={stats.total_photos}
            change={`${stats.total_albums} albums`}
            icon={Camera}
            color="bg-orange-500"
            trend="up"
          />
          <StatCard
            title="Messages échangés"
            value={stats.total_messages}
            change={`${stats.active_conversations} conversations`}
            icon={MessageSquare}
            color="bg-green-500"
            trend="up"
          />
        </div>

        {/* Actions rapides */}
        <div>
          <h2 className="text-2xl font-bold text-gray-900 mb-6">Actions rapides</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {quickActions.map((action, index) => (
              <Link key={index} href={action.href}>
                <Card className="group hover:shadow-xl transition-all duration-300 cursor-pointer border-0 shadow-lg overflow-hidden">
                  <div className={`h-2 ${action.color}`} />
                  <CardContent className="p-6">
                    <div className="flex items-start justify-between mb-4">
                      <div className={`p-3 rounded-xl ${action.color} relative`}>
                        <action.icon className="w-6 h-6 text-white" />
                        {action.urgentBadge && (
                          <div className="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full flex items-center justify-center">
                            <span className="text-xs text-white font-bold">{action.urgentBadge}</span>
                          </div>
                        )}
                      </div>
                      <div className="flex flex-col gap-1">
                        {action.badge && (
                          <Badge className="bg-blue-100 text-blue-800 text-xs">
                            {action.badge}
                          </Badge>
                        )}
                        {action.urgentBadge && (
                          <Badge className="bg-red-500 text-white text-xs animate-pulse">
                            {action.urgentBadge} nouveau{action.urgentBadge > 1 ? 'x' : ''}
                          </Badge>
                        )}
                      </div>
                    </div>
                    <h3 className="font-semibold text-gray-900 mb-2 group-hover:text-orange-600 transition-colors">
                      {action.title}
                    </h3>
                    <p className="text-sm text-gray-600 mb-4">
                      {action.description}
                    </p>
                    <div className="flex items-center text-orange-600 text-sm font-medium">
                      Accéder
                      <ChevronRight className="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" />
                    </div>
                  </CardContent>
                </Card>
              </Link>
            ))}
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Activités récentes */}
          <div className="lg:col-span-2">
            <Card className="border-0 shadow-lg">
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Activity className="w-5 h-5 text-orange-500" />
                  Activités récentes
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {recent_activities.length > 0 ? (
                  recent_activities.slice(0, 6).map((activity, index) => (
                    <div key={activity.id} className="flex items-start gap-4 p-4 rounded-lg hover:bg-gray-50 transition-colors">
                      <div className="w-10 h-10 rounded-full bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center text-white text-lg">
                        {activity.icon}
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="font-medium text-gray-900">{activity.title}</p>
                        <p className="text-sm text-gray-600 mt-1">{activity.description}</p>
                        <div className="flex items-center gap-2 mt-2">
                          <Clock className="w-3 h-3 text-gray-400" />
                          <span className="text-xs text-gray-500">{activity.time}</span>
                        </div>
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="text-center py-8">
                    <Activity className="w-12 h-12 text-gray-300 mx-auto mb-4" />
                    <p className="text-gray-500">Aucune activité récente</p>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Sidebar avec suggestions et famille */}
          <div className="space-y-6">
            {/* Suggestions en attente */}
            {(badges.suggestions > 0 || badges.new_suggestions > 0) && (
              <Card className="border-0 shadow-lg">
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Sparkles className="w-5 h-5 text-yellow-500" />
                    Suggestions
                    <div className="flex gap-2">
                      {badges.suggestions > 0 && (
                        <Badge className="bg-yellow-100 text-yellow-800">
                          {badges.suggestions} en attente
                        </Badge>
                      )}
                      {badges.new_suggestions > 0 && (
                        <Badge className="bg-green-100 text-green-800 animate-pulse">
                          {badges.new_suggestions} nouvelles
                        </Badge>
                      )}
                    </div>
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-3">
                  {pending_suggestions.slice(0, 3).map((suggestion, index) => (
                    <div key={index} className="p-3 rounded-lg border border-yellow-200 bg-yellow-50">
                      <p className="text-sm font-medium text-gray-900">
                        Nouvelle relation suggérée
                      </p>
                      <p className="text-xs text-gray-600 mt-1">
                        Vérifiez cette suggestion de lien familial
                      </p>
                    </div>
                  ))}
                  <Button variant="outline" className="w-full mt-4" asChild>
                    <Link href="/network">
                      Voir toutes les suggestions
                    </Link>
                  </Button>
                </CardContent>
              </Card>
            )}

            {/* Membres de la famille récents */}
            <Card className="border-0 shadow-lg">
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Users className="w-5 h-5 text-blue-500" />
                  Famille
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-3">
                  {family_members.slice(0, 5).map((member, index) => (
                    <div key={member.id} className="flex items-center gap-3">
                      <Avatar className="w-8 h-8">
                        <AvatarImage src={member.profile?.avatar} />
                        <AvatarFallback className="bg-gradient-to-br from-blue-500 to-blue-600 text-white text-sm">
                          {member.name.charAt(0)}
                        </AvatarFallback>
                      </Avatar>
                      <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium text-gray-900 truncate">
                          {member.profile?.first_name || member.name}
                        </p>
                        <p className="text-xs text-gray-500">
                          Membre de la famille
                        </p>
                      </div>
                    </div>
                  ))}
                </div>
                <Button variant="outline" className="w-full mt-4" asChild>
                  <Link href="/famille">
                    Voir toute la famille
                  </Link>
                </Button>
              </CardContent>
            </Card>

            {/* Raccourcis utiles */}
            <Card className="border-0 shadow-lg">
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Zap className="w-5 h-5 text-purple-500" />
                  Raccourcis
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                <Button variant="ghost" className="w-full justify-start" asChild>
                  <Link href="/parametres">
                    <Settings className="w-4 h-4 mr-2" />
                    Paramètres
                  </Link>
                </Button>
                <Button variant="ghost" className="w-full justify-start" asChild>
                  <Link href="/test-albums">
                    <Camera className="w-4 h-4 mr-2" />
                    Test Albums
                  </Link>
                </Button>
                <Button variant="ghost" className="w-full justify-start" asChild>
                  <Link href="/test-locale">
                    <Globe className="w-4 h-4 mr-2" />
                    Test Langue
                  </Link>
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </KwdDashboardLayout>
  );
}
