import React from 'react';
import { Head } from '@inertiajs/react';
import { useTranslation } from '@/hooks/useTranslation';
import { KwdDashboardLayout } from '@/Layouts/modern';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
  Users, 
  Globe, 
  MessageSquare, 
  Activity,
  TrendingUp,
  Calendar,
  Star,
  Heart,
  TreePine,
  Settings,
  Zap,
  Sparkles,
  ChevronLeft,
  ChevronRight,
  Moon,
  Sun
} from 'lucide-react';

const KwdDemo: React.FC = () => {
  const { t, isRTL } = useTranslation();

  const stats = [
    {
      name: t('family_members'),
      value: '18',
      icon: Users,
      change: '+6',
      changeType: 'positive' as const,
    },
    {
      name: t('connections'),
      value: '67',
      icon: Globe,
      change: '+15',
      changeType: 'positive' as const,
    },
    {
      name: t('messages'),
      value: '42',
      icon: MessageSquare,
      change: '+11',
      changeType: 'positive' as const,
    },
    {
      name: t('suggestions'),
      value: '16',
      icon: Activity,
      change: '+8',
      changeType: 'positive' as const,
    },
  ];

  const features = [
    {
      title: t('collapsible_sidebar'),
      description: t('space_saving_collapsible_sidebar'),
      icon: ChevronLeft,
    },
    {
      title: t('advanced_animations'),
      description: t('smooth_micro_interactions'),
      icon: Sparkles,
    },
    {
      title: t('backdrop_blur'),
      description: t('modern_glass_morphism_effect'),
      icon: Zap,
    },
    {
      title: t('dark_mode_toggle'),
      description: t('seamless_theme_switching'),
      icon: Moon,
    },
  ];

  const advancedFeatures = [
    {
      title: t('smart_navigation'),
      description: t('intelligent_navigation_system'),
      icon: TreePine,
      status: 'active'
    },
    {
      title: t('performance_optimized'),
      description: t('optimized_for_performance'),
      icon: Zap,
      status: 'active'
    },
    {
      title: t('accessibility_ready'),
      description: t('full_accessibility_support'),
      icon: Heart,
      status: 'active'
    },
    {
      title: t('future_proof'),
      description: t('built_with_latest_technologies'),
      icon: Star,
      status: 'coming_soon'
    },
  ];

  return (
    <KwdDashboardLayout title="KWD Dashboard Demo">
      <Head title="KWD Dashboard Demo" />
      
      <div className="space-y-8">
        {/* Header */}
        <div className="text-center">
          <h1 className="text-4xl font-bold bg-gradient-to-r from-purple-600 via-pink-600 to-blue-600 bg-clip-text text-transparent mb-4">
            ✨ KWD Dashboard Layout
          </h1>
          <p className="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
            {t('kwd_layout_description')}
          </p>
          <div className="mt-6 flex justify-center space-x-4">
            <Badge className="bg-gradient-to-r from-purple-500 to-pink-500 text-white">
              {t('advanced')}
            </Badge>
            <Badge className="bg-gradient-to-r from-blue-500 to-indigo-500 text-white">
              {t('animations')}
            </Badge>
            <Badge className="bg-gradient-to-r from-green-500 to-emerald-500 text-white">
              {t('collapsible')}
            </Badge>
          </div>
        </div>

        {/* Stats Grid with Advanced Styling */}
        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {stats.map((stat, index) => {
            const gradients = [
              'from-purple-500 to-pink-500',
              'from-blue-500 to-indigo-500',
              'from-green-500 to-emerald-500',
              'from-orange-500 to-red-500'
            ];
            return (
              <Card key={stat.name} className={`bg-gradient-to-br ${gradients[index]} text-white hover:shadow-2xl transition-all transform hover:scale-105 hover:rotate-1 backdrop-blur-sm`}>
                <CardContent className="p-6 relative overflow-hidden">
                  <div className="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-full -mr-10 -mt-10"></div>
                  <div className="flex items-center relative z-10">
                    <div className="flex-shrink-0">
                      <stat.icon className="h-8 w-8 text-white/90" />
                    </div>
                    <div className={`${isRTL ? 'mr-5' : 'ml-5'} w-0 flex-1`}>
                      <dl>
                        <dt className="text-sm font-medium text-white/80 truncate">
                          {stat.name}
                        </dt>
                        <dd className="flex items-baseline">
                          <div className="text-2xl font-bold text-white">
                            {stat.value}
                          </div>
                          <div className={`${isRTL ? 'mr-2' : 'ml-2'} flex items-baseline text-sm font-semibold text-white/90`}>
                            <TrendingUp className="h-4 w-4 flex-shrink-0 self-center" />
                            <span className={`${isRTL ? 'mr-1' : 'ml-1'}`}>
                              {stat.change}
                            </span>
                          </div>
                        </dd>
                      </dl>
                    </div>
                  </div>
                </CardContent>
              </Card>
            );
          })}
        </div>

        {/* Core Features */}
        <Card className="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-gray-800 dark:via-gray-700 dark:to-gray-800 backdrop-blur-sm">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Sparkles className="h-6 w-6 text-purple-500" />
              {t('core_features')}
            </CardTitle>
            <CardDescription>
              {t('kwd_layout_features')}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {features.map((feature, index) => (
                <div key={index} className={`flex items-start space-x-4 p-4 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}>
                  <div className="flex-shrink-0">
                    <div className="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                      <feature.icon className="w-5 h-5 text-white" />
                    </div>
                  </div>
                  <div>
                    <h3 className="text-lg font-medium text-gray-900 dark:text-white">
                      {feature.title}
                    </h3>
                    <p className="text-gray-600 dark:text-gray-400">
                      {feature.description}
                    </p>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Advanced Features */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Star className="h-6 w-6 text-purple-500" />
              {t('advanced_features')}
            </CardTitle>
            <CardDescription>
              {t('cutting_edge_capabilities')}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {advancedFeatures.map((feature, index) => (
                <div key={index} className={`flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-purple-300 dark:hover:border-purple-600 transition-all ${isRTL ? 'flex-row-reverse' : ''}`}>
                  <div className={`flex items-center space-x-4 ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}>
                    <div className="flex-shrink-0">
                      <div className="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                        <feature.icon className="w-4 h-4 text-white" />
                      </div>
                    </div>
                    <div>
                      <h4 className="text-sm font-medium text-gray-900 dark:text-white">
                        {feature.title}
                      </h4>
                      <p className="text-xs text-gray-600 dark:text-gray-400">
                        {feature.description}
                      </p>
                    </div>
                  </div>
                  <Badge 
                    variant={feature.status === 'active' ? 'default' : 'secondary'}
                    className={feature.status === 'active' ? 'bg-green-500 text-white' : ''}
                  >
                    {feature.status === 'active' ? t('active') : t('coming_soon')}
                  </Badge>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Demo Actions */}
        <Card>
          <CardHeader>
            <CardTitle>{t('try_other_layouts')}</CardTitle>
            <CardDescription>
              {t('compare_different_layouts')}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <Button 
                variant="outline" 
                className="h-auto p-4 flex flex-col items-center space-y-2 border-blue-200 hover:border-blue-300 hover:bg-blue-50"
                onClick={() => window.location.href = '/layout-demo/kui'}
              >
                <Star className="h-6 w-6 text-blue-500" />
                <span>{t('kui_layout')}</span>
              </Button>
              <Button 
                variant="outline" 
                className="h-auto p-4 flex flex-col items-center space-y-2 border-orange-200 hover:border-orange-300 hover:bg-orange-50"
                onClick={() => window.location.href = '/layout-demo/starter'}
              >
                <Heart className="h-6 w-6 text-orange-500" />
                <span>{t('starter_layout')}</span>
              </Button>
              <Button 
                variant="outline" 
                className="h-auto p-4 flex flex-col items-center space-y-2"
                onClick={() => window.location.href = '/layout-demo'}
              >
                <Settings className="h-6 w-6 text-gray-500" />
                <span>{t('layout_selector')}</span>
              </Button>
            </div>
          </CardContent>
        </Card>

        {/* Implementation Note */}
        <Card className="bg-gradient-to-r from-purple-50 via-pink-50 to-blue-50 dark:from-purple-900/20 dark:via-pink-900/20 dark:to-blue-900/20 border-purple-200 dark:border-purple-800">
          <CardContent className="p-6">
            <div className="flex items-start space-x-4">
              <div className="flex-shrink-0">
                <div className="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                  <Sparkles className="w-4 h-4 text-white" />
                </div>
              </div>
              <div>
                <h3 className="text-lg font-medium text-purple-900 dark:text-purple-100">
                  {t('most_advanced_layout')}
                </h3>
                <p className="text-purple-700 dark:text-purple-300 mt-1">
                  {t('kwd_implementation_note')}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </KwdDashboardLayout>
  );
};

export default KwdDemo;
