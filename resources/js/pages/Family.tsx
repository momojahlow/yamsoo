
import React from "react";
import { Head } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Users, Plus, TreePine, MessageSquare, UserPlus } from "lucide-react";
import { KwdDashboardLayout } from '@/Layouts/modern';
import { FamilyMemberCard } from "@/components/family/FamilyMemberCard";
import { useTranslation } from "@/hooks/useTranslation";

interface Member {
  id: number;
  name: string;
  email: string;
  avatar?: string | null;
  bio?: string | null;
  birth_date?: string | null;
  gender?: string | null;
  phone?: string | null;
  relation: string;
  status: string;
}

interface User {
  id: number;
  name: string;
  email: string;
}

interface FamilyProps {
  members: Member[];
  auth?: {
    user: User | null;
  };
}

export default function Family({ members }: FamilyProps) {
  const { t, isRTL } = useTranslation();

  console.log('members', members);
  console.log('members type:', typeof members);
  console.log('members is array:', Array.isArray(members));

  // Sécurité : s'assurer que members est un tableau
  const safeMembers = Array.isArray(members) ? members : [];

  if (!safeMembers || safeMembers.length === 0) {
    return (
      <KwdDashboardLayout title={t('my_family')}>
        <Head title={t('my_family')} />

        <div className="min-h-screen bg-gradient-to-br from-orange-50 via-white to-red-50">
          <div className="w-full max-w-6xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-6 md:py-8 lg:py-12">
            <div className="flex items-center justify-center min-h-[60vh]">
              <div className="text-center max-w-md mx-auto">
                <div className="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 bg-gradient-to-br from-orange-500 to-red-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                  <Users className="w-8 h-8 sm:w-10 sm:w-10 md:w-12 md:h-12 text-white" />
                </div>
                <h2 className="text-xl sm:text-2xl md:text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent mb-4">{t('my_family')}</h2>
                <p className="text-gray-600 mb-8 text-sm sm:text-base leading-relaxed">
                  {t('no_family_members_yet')}
                </p>
                <div className="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
                  <Button
                    onClick={() => window.location.href = '/reseaux'}
                    className={`w-full sm:w-auto h-11 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium transition-all duration-200 shadow-lg hover:shadow-xl flex items-center ${isRTL ? 'flex-row-reverse' : ''}`}
                  >
                    <UserPlus className={`w-4 h-4 ${isRTL ? 'ml-2' : 'mr-2'}`} />
                    {t('add_relations')}
                  </Button>
                  <Button
                    variant="outline"
                    onClick={() => window.location.href = '/famille/arbre'}
                    className="w-full sm:w-auto h-11 border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-200"
                  >
                    <TreePine className="w-4 h-4 mr-2" />
                    Voir l'arbre familial
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </KwdDashboardLayout>
    );
  }

  return (
    <KwdDashboardLayout title={t('my_family')}>
      <Head title={t('my_family')} />

      <div className="min-h-screen bg-gradient-to-br from-orange-50 via-white to-red-50">
        <div className="w-full max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-6 md:py-8 lg:py-12">
          {/* Header responsive */}
          <div className="flex flex-col sm:flex-row items-center sm:items-start justify-between mb-6 sm:mb-8 md:mb-12 gap-4">
            <div className="text-center sm:text-left">
              <h2 className="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent leading-tight">{t('my_family')}</h2>
              <p className="text-gray-600 mt-2 sm:mt-3 text-xs sm:text-sm md:text-base">
                {safeMembers.length} {t('members_in_family')}
              </p>
            </div>
            <div className="flex flex-col sm:flex-row gap-2 sm:gap-3">
              <Button
                variant="outline"
                className={`w-full sm:w-auto h-9 sm:h-10 border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-200 text-sm flex items-center ${isRTL ? 'flex-row-reverse' : ''}`}
                onClick={() => window.location.href = '/reseaux'}
              >
                <Plus className={`w-3 h-3 sm:w-4 sm:h-4 ${isRTL ? 'ml-2' : 'mr-2'}`} />
                {t('add_member')}
              </Button>
              <Button
                variant="outline"
                className={`w-full sm:w-auto h-9 sm:h-10 border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-200 text-sm flex items-center ${isRTL ? 'flex-row-reverse' : ''}`}
                onClick={() => window.location.href = '/famille/arbre'}
              >
                <TreePine className={`w-3 h-3 sm:w-4 sm:h-4 ${isRTL ? 'ml-2' : 'mr-2'}`} />
                <span className="hidden sm:inline">{t('show_family_tree')}</span>
                <span className="sm:hidden">{t('family_tree')}</span>
              </Button>
            </div>
          </div>

          {/* Family Members Grid responsive */}
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4 md:gap-6">
          {safeMembers.map((member) => (
            <div key={member.id} className="flex flex-col items-center">
              <FamilyMemberCard
                id={member.id.toString()}
                name={member.name}
                avatarUrl={member.avatar || undefined}
                relation={member.relation}
              />
            </div>
          ))}
        </div>

          {/* Quick Actions Card responsive */}
          <Card className="mt-8 sm:mt-12 border-0 shadow-sm bg-gradient-to-br from-white to-gray-50/50">
            <CardContent className="p-4 sm:p-6">
              <h3 className="text-base sm:text-lg md:text-xl font-semibold mb-4 sm:mb-6 text-gray-900">⚡ {t('quick_actions')}</h3>
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                <Button
                  variant="outline"
                  className="flex items-center gap-3 h-auto p-4 sm:p-6 flex-col border-gray-200 hover:border-orange-300 hover:bg-orange-50 transition-all duration-200 group"
                  onClick={() => window.location.href = '/reseaux'}
                >
                  <div className="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                    <UserPlus className="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                  </div>
                  <div className="text-center">
                    <div className="font-medium text-sm sm:text-base text-gray-900">{t('add_relations')}</div>
                    <div className="text-xs sm:text-sm text-gray-500 mt-1">{t('invite_new_members')}</div>
                  </div>
                </Button>

                <Button
                  variant="outline"
                  className="flex items-center gap-3 h-auto p-4 sm:p-6 flex-col border-gray-200 hover:border-green-300 hover:bg-green-50 transition-all duration-200 group"
                  onClick={() => window.location.href = '/messagerie'}
                >
                  <div className="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                    <MessageSquare className="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                  </div>
                  <div className="text-center">
                    <div className="font-medium text-sm sm:text-base text-gray-900">{t('family_messaging')}</div>
                    <div className="text-xs sm:text-sm text-gray-500 mt-1">{t('communicate_with_family')}</div>
                  </div>
                </Button>

                <Button
                  variant="outline"
                  className="flex items-center gap-3 h-auto p-4 sm:p-6 flex-col border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-all duration-200 group sm:col-span-2 lg:col-span-1"
                  onClick={() => window.location.href = '/famille/arbre'}
                >
                  <div className="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                    <TreePine className="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                  </div>
                  <div className="text-center">
                    <div className="font-medium text-sm sm:text-base text-gray-900">{t('family_tree')}</div>
                    <div className="text-xs sm:text-sm text-gray-500 mt-1">{t('view_family_links')}</div>
                  </div>
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </KwdDashboardLayout>
  );
}
