import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import AppSidebarLayout from '@/Layouts/app/app-sidebar-layout';
import {
  Users,
  Heart,
  Plus,
  ArrowRight,
  TreePine,
  UserPlus,
  Gift,
  Activity,
  Sparkles,
  Clock,
  Crown
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
}

interface Activity {
  id: string;
  type: string;
  text: string;
  time: string;
  avatar: string;
  icon: string;
  color: string;
}

interface Suggestion {
  id: number;
  suggested_user: {
    id: number;
    name: string;
    profile?: any;
  };
  relation_name: string;
  type: string;
}

interface Birthday {
  id: number;
  name: string;
  profile?: any;
  relation_type: string;
  days_until: number;
  age_turning: number;
}

interface DashboardProps {
  user: User;
  profile: any;
  dashboardStats: DashboardStats;
  recentActivities: Activity[];
  prioritySuggestions: Suggestion[];
  recentFamilyMembers: any[];
  upcomingBirthdays: Birthday[];
  familyStatistics: any;
}

const Dashboard: React.FC<DashboardProps> = ({
  user,
  profile,
  dashboardStats,
  recentActivities,
  prioritySuggestions,
  recentFamilyMembers,
  upcomingBirthdays,
  familyStatistics
}) => {
  const getGenderIcon = (gender?: string) => {
    return gender === 'female' ? '👩' : '👨';
  };

  const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
  };

  const stats = [
    {
      title: "Membres de famille",
      value: dashboardStats.total_family_members.toString(),
      icon: Users,
      color: "text-blue-600",
      bgColor: "bg-blue-50",
      change: `+${dashboardStats.new_members_this_month} ce mois`,
      href: "/famille"
    },
    {
      title: "Suggestions",
      value: dashboardStats.pending_suggestions.toString(),
      icon: Heart,
      color: "text-pink-600",
      bgColor: "bg-pink-50",
      change: `+${dashboardStats.new_suggestions_this_week} cette semaine`,
      href: "/suggestions"
    },
    {
      title: "Relations automatiques",
      value: dashboardStats.automatic_relations.toString(),
      icon: Sparkles,
      color: "text-purple-600",
      bgColor: "bg-purple-50",
      change: "Déduites intelligemment",
      href: "/famille/arbre"
    },
    {
      title: "Anniversaires",
      value: upcomingBirthdays.length.toString(),
      icon: Gift,
      color: "text-green-600",
      bgColor: "bg-green-50",
      change: "À venir ce mois",
      href: "#birthdays"
    }
  ];

  return (
    <AppSidebarLayout>
      <Head title="Tableau de bord" />

      <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
        <div className="max-w-7xl mx-auto p-6 space-y-8">
          {/* Header avec salutation personnalisée */}
          <div className="text-center py-8">
            <div className="flex items-center justify-center mb-4">
              <div className="text-6xl mr-4">
                {getGenderIcon(profile?.gender)}
              </div>
              <div>
                <h1 className="text-4xl font-bold text-gray-900 mb-2">
                  Bonjour, {profile?.first_name || user.name} !
                </h1>
                <p className="text-xl text-gray-600">
                  Bienvenue sur votre réseau familial
                </p>
              </div>
            </div>
            
            <div className="flex items-center justify-center space-x-4 mt-6">
              <Link href="/famille/arbre">
                <Button size="lg" className="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700">
                  <TreePine className="w-5 h-5 mr-2" />
                  Voir l'arbre familial
                </Button>
              </Link>
              <Link href="/suggestions">
                <Button variant="outline" size="lg">
                  <UserPlus className="w-5 h-5 mr-2" />
                  Découvrir des relations
                </Button>
              </Link>
            </div>
          </div>

          {/* Statistiques principales */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {stats.map((stat, index) => (
              <Link key={index} href={stat.href}>
                <Card className="border-0 shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer hover:scale-105 bg-white/80 backdrop-blur-sm">
                  <CardContent className="p-6">
                    <div className="flex items-center justify-between">
                      <div>
                        <p className="text-sm font-medium text-gray-600 mb-1">
                          {stat.title}
                        </p>
                        <p className="text-3xl font-bold text-gray-900 mb-1">
                          {stat.value}
                        </p>
                        <p className="text-xs text-gray-500">
                          {stat.change}
                        </p>
                      </div>
                      <div className={`p-4 rounded-xl ${stat.bgColor} transition-colors`}>
                        <stat.icon className={`w-8 h-8 ${stat.color}`} />
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </Link>
            ))}
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Activité récente */}
            <div className="lg:col-span-2">
              <Card className="border-0 shadow-lg bg-white/80 backdrop-blur-sm">
                <CardHeader>
                  <CardTitle className="flex items-center justify-between">
                    <div className="flex items-center">
                      <Activity className="w-6 h-6 text-blue-600 mr-3" />
                      Activité récente
                    </div>
                    <Badge variant="secondary">{recentActivities.length}</Badge>
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    {recentActivities.length > 0 ? (
                      recentActivities.map((activity) => (
                        <div key={activity.id} className="flex items-start space-x-4 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                          <Avatar className="w-10 h-10">
                            <AvatarFallback className={`bg-${activity.color}-100 text-${activity.color}-600`}>
                              {activity.avatar}
                            </AvatarFallback>
                          </Avatar>
                          <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-gray-900">
                              {activity.text}
                            </p>
                            <p className="text-xs text-gray-500 flex items-center mt-1">
                              <Clock className="w-3 h-3 mr-1" />
                              {activity.time}
                            </p>
                          </div>
                        </div>
                      ))
                    ) : (
                      <div className="text-center py-8 text-gray-500">
                        <Activity className="w-12 h-12 mx-auto mb-3 opacity-50" />
                        <p>Aucune activité récente</p>
                      </div>
                    )}
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Sidebar avec suggestions et anniversaires */}
            <div className="space-y-6">
              {/* Suggestions prioritaires */}
              <Card className="border-0 shadow-lg bg-white/80 backdrop-blur-sm">
                <CardHeader>
                  <CardTitle className="flex items-center justify-between">
                    <div className="flex items-center">
                      <Heart className="w-6 h-6 text-pink-600 mr-3" />
                      Suggestions
                    </div>
                    <Link href="/suggestions">
                      <Button variant="ghost" size="sm">
                        <ArrowRight className="w-4 h-4" />
                      </Button>
                    </Link>
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    {prioritySuggestions.length > 0 ? (
                      prioritySuggestions.map((suggestion) => (
                        <div key={suggestion.id} className="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 transition-colors">
                          <Avatar className="w-8 h-8">
                            <AvatarFallback>
                              {getInitials(suggestion.suggested_user.name)}
                            </AvatarFallback>
                          </Avatar>
                          <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-gray-900 truncate">
                              {suggestion.suggested_user.name}
                            </p>
                            <p className="text-xs text-gray-500">
                              {suggestion.relation_name}
                            </p>
                          </div>
                        </div>
                      ))
                    ) : (
                      <div className="text-center py-4 text-gray-500">
                        <Heart className="w-8 h-8 mx-auto mb-2 opacity-50" />
                        <p className="text-sm">Aucune suggestion</p>
                      </div>
                    )}
                  </div>
                </CardContent>
              </Card>

              {/* Actions rapides */}
              <Card className="border-0 shadow-lg bg-white/80 backdrop-blur-sm">
                <CardHeader>
                  <CardTitle className="flex items-center">
                    <Plus className="w-6 h-6 text-green-600 mr-3" />
                    Actions rapides
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    <Link href="/family-relations">
                      <Button variant="outline" className="w-full justify-start">
                        <UserPlus className="w-4 h-4 mr-2" />
                        Ajouter une relation
                      </Button>
                    </Link>
                    <Link href="/famille/arbre">
                      <Button variant="outline" className="w-full justify-start">
                        <TreePine className="w-4 h-4 mr-2" />
                        Explorer l'arbre
                      </Button>
                    </Link>
                    <Link href="/profile">
                      <Button variant="outline" className="w-full justify-start">
                        <Crown className="w-4 h-4 mr-2" />
                        Modifier mon profil
                      </Button>
                    </Link>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </div>
    </AppSidebarLayout>
  );
};

export default Dashboard;
