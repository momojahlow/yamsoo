import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { AnimatedStatsCard } from "@/components/dashboard/AnimatedStatsCard";
import { KwdDashboardLayout } from '@/Layouts/modern';
import { useTranslation } from '@/hooks/useTranslation';
import {
  Users,
  Heart,
  Plus,
  ArrowRight,
  TreePine,
  UserPlus,
  Bell,
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
  notifications: any[];
  unreadNotifications: number;
  pendingRequests: any[];
}

const Dashboard: React.FC<DashboardProps> = ({
  user,
  profile,
  dashboardStats,
  recentActivities,
  prioritySuggestions,
  recentFamilyMembers,
  upcomingBirthdays,
  familyStatistics,
  notifications,
  unreadNotifications,
  pendingRequests
}) => {
  const { t, isRTL } = useTranslation();

  const getGenderIcon = (gender?: string) => {
    return gender === 'female' ? '👩' : '👨';
  };

  const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
  };

  const stats = [
    {
      title: t('total_members'),
      value: dashboardStats.total_family_members.toString(),
      icon: Users,
      color: "text-blue-600",
      bgColor: "bg-blue-50",
      change: "",
      href: "/famille"
    },
    {
      title: t('pending_suggestions'),
      value: dashboardStats.pending_suggestions.toString(),
      icon: Heart,
      color: "text-pink-600",
      bgColor: "bg-pink-50",
      change: "",
      href: "/suggestions"
    },
    {
      title: t('notifications'),
      value: unreadNotifications.toString(),
      icon: Bell,
      color: "text-orange-600",
      bgColor: "bg-orange-50",
      change: "",
      href: "/notifications"
    },
    {
      title: t('pending_requests'),
      value: pendingRequests.length.toString(),
      icon: UserPlus,
      color: "text-purple-600",
      bgColor: "bg-purple-50",
      change: "",
      href: "/reseaux"
    }
  ];

  return (
    <KwdDashboardLayout title={t('dashboard')}>
      <Head title={t('dashboard')} />

      <div className={`min-h-screen bg-gradient-to-br from-orange-50 via-white to-red-50 ${isRTL ? 'rtl' : 'ltr'}`}>
        <div className="w-full max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-6 md:py-8 space-y-6 sm:space-y-8">
          {/* Header principal - Responsive */}
          <div className="text-center py-4 sm:py-6 md:py-8">
            <div className="flex flex-col items-center justify-center mb-4 sm:mb-6 gap-3 sm:gap-4">
              <div className="text-4xl sm:text-5xl md:text-6xl">
                {getGenderIcon(profile?.gender)}
              </div>
              <div className="text-center">
                <h1 className="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent mb-2 leading-tight">
                  {t('dashboard')}
                </h1>
                <p className="text-sm sm:text-base md:text-lg lg:text-xl text-gray-600">
                  {t('welcome')}
                </p>
              </div>
            </div>

            <div className={`flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 mt-4 sm:mt-6 ${isRTL ? 'flex-row-reverse' : ''}`}>
              <Link href="/famille/arbre" className="w-full sm:w-auto">
                <Button size="lg" className="w-full sm:w-auto bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 shadow-lg hover:scale-105 transition-all duration-200 text-white font-medium">
                  <TreePine className={`w-4 h-4 sm:w-5 sm:h-5 ${isRTL ? 'ml-2' : 'mr-2'}`} />
                  <span className="hidden sm:inline">{t('view_family_tree')}</span>
                  <span className="sm:hidden">{t('family_tree')}</span>
                </Button>
              </Link>
              <Link href="/suggestions" className="w-full sm:w-auto">
                <Button variant="outline" size="lg" className="w-full sm:w-auto border-gray-200 hover:border-orange-300 hover:bg-orange-50 transition-all duration-200">
                  <UserPlus className={`w-4 h-4 sm:w-5 sm:h-5 ${isRTL ? 'ml-2' : 'mr-2'}`} />
                  <span className="hidden sm:inline">{t('discover_people')}</span>
                  <span className="sm:hidden">{t('suggestions')}</span>
                </Button>
              </Link>
            </div>
          </div>

          {/* Statistiques principales - Responsive */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
            {stats.map((stat, index) => (
              <AnimatedStatsCard
                key={index}
                title={stat.title}
                value={stat.value}
                description={stat.change}
                icon={stat.icon}
                color={index % 2 === 0 ? 'orange' : index % 3 === 0 ? 'blue' : index % 4 === 0 ? 'green' : 'purple'}
                href={stat.href}
                trend={stat.change ? {
                  value: Math.floor(Math.random() * 20) + 5,
                  isPositive: Math.random() > 0.3
                } : undefined}
              />
            ))}
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 md:gap-8">
            {/* Activité récente */}
            <div className="lg:col-span-2">
              <Card className="border-0 shadow-lg bg-white/80 backdrop-blur-sm">
                <CardHeader>
                  <CardTitle className="flex items-center justify-between">
                    <div className={`flex items-center ${isRTL ? 'flex-row-reverse' : ''}`}>
                      <Activity className={`w-6 h-6 text-blue-600 ${isRTL ? 'ml-3' : 'mr-3'}`} />
                      {t('recent_activity')}
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

            {/* Sidebar avec demandes, suggestions et anniversaires */}
            <div className="space-y-6">
              {/* Demandes reçues */}
              {pendingRequests.length > 0 && (
                <Card className="border-0 shadow-lg bg-white/80 backdrop-blur-sm">
                  <CardHeader>
                    <CardTitle className="flex items-center justify-between">
                      <div className={`flex items-center ${isRTL ? 'flex-row-reverse' : ''}`}>
                        <UserPlus className={`w-6 h-6 text-purple-600 ${isRTL ? 'ml-3' : 'mr-3'}`} />
                        {t('pending_requests')}
                      </div>
                      <Link href="/reseaux">
                        <Button variant="ghost" size="sm">
                          <ArrowRight className="w-4 h-4" />
                        </Button>
                      </Link>
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-3">
                      {pendingRequests.map((request) => (
                        <div key={request.id} className="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                          <Avatar className="w-10 h-10">
                            <AvatarFallback className="bg-purple-100 text-purple-600">
                              {getInitials(request.requester?.name || request.requester_name || 'U')}
                            </AvatarFallback>
                          </Avatar>
                          <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-gray-900 truncate">
                              {request.requester?.name || request.requester_name}
                            </p>
                            <p className="text-xs text-gray-500">
                              {t('request_relationship')} {request.relationship_type?.display_name_fr || request.relationship_name}
                            </p>
                          </div>
                          <Badge variant="outline" className="text-xs">
                            {t('new')}
                          </Badge>
                        </div>
                      ))}
                      {pendingRequests.length > 3 && (
                        <div className="text-center pt-2">
                          <Link href="/reseaux">
                            <Button variant="ghost" size="sm" className="text-purple-600">
                              Voir toutes les demandes
                            </Button>
                          </Link>
                        </div>
                      )}
                    </div>
                  </CardContent>
                </Card>
              )}

              {/* Suggestions prioritaires */}
              <Card className="border-0 shadow-lg bg-white/80 backdrop-blur-sm">
                <CardHeader>
                  <CardTitle className="flex items-center justify-between">
                    <div className={`flex items-center ${isRTL ? 'flex-row-reverse' : ''}`}>
                      <Heart className={`w-6 h-6 text-pink-600 ${isRTL ? 'ml-3' : 'mr-3'}`} />
                      {t('suggestions')}
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
                        <p className="text-sm">{t('no_suggestions')}</p>
                      </div>
                    )}
                  </div>
                </CardContent>
              </Card>

              {/* Actions rapides */}
              <Card className="border-0 shadow-lg bg-white/80 backdrop-blur-sm">
                <CardHeader>
                  <CardTitle className={`flex items-center ${isRTL ? 'flex-row-reverse' : ''}`}>
                    <Plus className={`w-6 h-6 text-green-600 ${isRTL ? 'ml-3' : 'mr-3'}`} />
                    {t('quick_actions')}
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    <Link href="/reseaux">
                      <Button variant="outline" className={`w-full ${isRTL ? 'justify-end' : 'justify-start'}`}>
                        <UserPlus className={`w-4 h-4 ${isRTL ? 'ml-2' : 'mr-2'}`} />
                        {t('add_relationship')}
                      </Button>
                    </Link>
                    <Link href="/famille/arbre">
                      <Button variant="outline" className={`w-full ${isRTL ? 'justify-end' : 'justify-start'}`}>
                        <TreePine className={`w-4 h-4 ${isRTL ? 'ml-2' : 'mr-2'}`} />
                        {t('explore_tree')}
                      </Button>
                    </Link>
                    <Link href="/profile">
                      <Button variant="outline" className={`w-full ${isRTL ? 'justify-end' : 'justify-start'}`}>
                        <Crown className={`w-4 h-4 ${isRTL ? 'ml-2' : 'mr-2'}`} />
                        {t('edit_my_profile')}
                      </Button>
                    </Link>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </div>
    </KwdDashboardLayout>
  );
};

export default Dashboard;
