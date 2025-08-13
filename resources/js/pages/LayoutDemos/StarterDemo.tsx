import React from 'react';
import { Head } from '@inertiajs/react';
import { useTranslation } from '@/hooks/useTranslation';
import { StarterDashboardLayout } from '@/Layouts/modern';
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
  Palette,
  Zap
} from 'lucide-react';

const StarterDemo: React.FC = () => {
  const { t, isRTL } = useTranslation();

  const stats = [
    {
      name: t('family_members'),
      value: '15',
      icon: Users,
      change: '+3',
      changeType: 'positive' as const,
    },
    {
      name: t('connections'),
      value: '52',
      icon: Globe,
      change: '+8',
      changeType: 'positive' as const,
    },
    {
      name: t('messages'),
      value: '31',
      icon: MessageSquare,
      change: '+7',
      changeType: 'positive' as const,
    },
    {
      name: t('suggestions'),
      value: '12',
      icon: Activity,
      change: '+4',
      changeType: 'positive' as const,
    },
  ];

  const features = [
    {
      title: t('gradient_design'),
      description: t('beautiful_gradient_interface'),
      icon: Palette,
    },
    {
      title: t('navigation_badges'),
      description: t('visual_notification_badges'),
      icon: Star,
    },
    {
      title: t('clean_minimal'),
      description: t('minimal_clean_design'),
      icon: Zap,
    },
    {
      title: t('user_friendly'),
      description: t('intuitive_user_experience'),
      icon: Heart,
    },
  ];

  return (
    <StarterDashboardLayout title="Starter Dashboard Demo">
      <Head title="Starter Dashboard Demo" />
      
      <div className="space-y-8">
        {/* Header */}
        <div className="text-center">
          <h1 className="text-4xl font-bold bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent mb-4">
            🚀 Starter Dashboard Layout
          </h1>
          <p className="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
            {t('starter_layout_description')}
          </p>
          <div className="mt-6 flex justify-center space-x-4">
            <Badge className="bg-gradient-to-r from-orange-500 to-red-500 text-white">
              {t('gradient_design')}
            </Badge>
            <Badge className="bg-gradient-to-r from-green-500 to-blue-500 text-white">
              {t('badges')}
            </Badge>
            <Badge className="bg-gradient-to-r from-purple-500 to-pink-500 text-white">
              {t('minimal')}
            </Badge>
          </div>
        </div>

        {/* Stats Grid with Gradient Cards */}
        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {stats.map((stat, index) => {
            const gradients = [
              'from-orange-500 to-red-500',
              'from-green-500 to-emerald-500',
              'from-blue-500 to-indigo-500',
              'from-purple-500 to-pink-500'
            ];
            return (
              <Card key={stat.name} className={`bg-gradient-to-br ${gradients[index]} text-white hover:shadow-xl transition-all transform hover:scale-105`}>
                <CardContent className="p-6">
                  <div className="flex items-center">
                    <div className="flex-shrink-0">
                      <stat.icon className="h-8 w-8 text-white/80" />
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

        {/* Features */}
        <Card className="bg-gradient-to-br from-white to-orange-50 dark:from-gray-800 dark:to-orange-900/20">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Star className="h-6 w-6 text-orange-500" />
              {t('key_features')}
            </CardTitle>
            <CardDescription>
              {t('starter_layout_features')}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {features.map((feature, index) => (
                <div key={index} className={`flex items-start space-x-4 ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}>
                  <div className="flex-shrink-0">
                    <div className="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center">
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

        {/* Interactive Demo */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Zap className="h-6 w-6 text-orange-500" />
              {t('interactive_elements')}
            </CardTitle>
            <CardDescription>
              {t('try_interactive_features')}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              <Button className="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 h-auto p-4 flex flex-col items-center space-y-2">
                <Users className="h-6 w-6" />
                <span>{t('add_family_member')}</span>
                <Badge variant="secondary" className="text-xs">
                  3
                </Badge>
              </Button>
              <Button className="bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 h-auto p-4 flex flex-col items-center space-y-2">
                <MessageSquare className="h-6 w-6" />
                <span>{t('new_messages')}</span>
                <Badge variant="secondary" className="text-xs">
                  7
                </Badge>
              </Button>
              <Button className="bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 h-auto p-4 flex flex-col items-center space-y-2">
                <Activity className="h-6 w-6" />
                <span>{t('suggestions')}</span>
                <Badge variant="secondary" className="text-xs">
                  12
                </Badge>
              </Button>
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
                className="h-auto p-4 flex flex-col items-center space-y-2 border-purple-200 hover:border-purple-300 hover:bg-purple-50"
                onClick={() => window.location.href = '/layout-demo/kwd'}
              >
                <TreePine className="h-6 w-6 text-purple-500" />
                <span>{t('kwd_layout')}</span>
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
        <Card className="bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 border-orange-200 dark:border-orange-800">
          <CardContent className="p-6">
            <div className="flex items-start space-x-4">
              <div className="flex-shrink-0">
                <div className="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center">
                  <Heart className="w-4 h-4 text-white" />
                </div>
              </div>
              <div>
                <h3 className="text-lg font-medium text-orange-900 dark:text-orange-100">
                  {t('perfect_for_families')}
                </h3>
                <p className="text-orange-700 dark:text-orange-300 mt-1">
                  {t('starter_implementation_note')}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </StarterDashboardLayout>
  );
};

export default StarterDemo;
