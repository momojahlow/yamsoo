import React from 'react';
import { Head } from '@inertiajs/react';
import { useTranslation } from '@/hooks/useTranslation';
import { KuiDashboardLayout } from '@/Layouts/modern';
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
  Bell,
  Search
} from 'lucide-react';

const KuiDemo: React.FC = () => {
  const { t, isRTL } = useTranslation();

  const stats = [
    {
      name: t('family_members'),
      value: '12',
      icon: Users,
      change: '+2',
      changeType: 'positive' as const,
    },
    {
      name: t('connections'),
      value: '48',
      icon: Globe,
      change: '+12',
      changeType: 'positive' as const,
    },
    {
      name: t('messages'),
      value: '24',
      icon: MessageSquare,
      change: '+3',
      changeType: 'positive' as const,
    },
    {
      name: t('suggestions'),
      value: '8',
      icon: Activity,
      change: '-1',
      changeType: 'negative' as const,
    },
  ];

  const features = [
    {
      title: t('professional_design'),
      description: t('clean_professional_interface'),
      icon: Star,
    },
    {
      title: t('dark_mode'),
      description: t('built_in_dark_mode_support'),
      icon: Settings,
    },
    {
      title: t('search_functionality'),
      description: t('integrated_search_bar'),
      icon: Search,
    },
    {
      title: t('notifications'),
      description: t('notification_system'),
      icon: Bell,
    },
  ];

  return (
    <KuiDashboardLayout title="KUI Dashboard Demo">
      <Head title="KUI Dashboard Demo" />
      
      <div className="space-y-8">
        {/* Header */}
        <div className="text-center">
          <h1 className="text-4xl font-bold text-gray-900 dark:text-white mb-4">
            🎨 KUI Dashboard Layout
          </h1>
          <p className="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
            {t('kui_layout_description')}
          </p>
          <div className="mt-6 flex justify-center space-x-4">
            <Badge className="bg-blue-500 text-white">
              {t('professional')}
            </Badge>
            <Badge className="bg-green-500 text-white">
              {t('dark_mode')}
            </Badge>
            <Badge className="bg-purple-500 text-white">
              {t('responsive')}
            </Badge>
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {stats.map((stat) => (
            <Card key={stat.name} className="hover:shadow-lg transition-shadow">
              <CardContent className="p-6">
                <div className="flex items-center">
                  <div className="flex-shrink-0">
                    <stat.icon className="h-8 w-8 text-blue-500" />
                  </div>
                  <div className={`${isRTL ? 'mr-5' : 'ml-5'} w-0 flex-1`}>
                    <dl>
                      <dt className="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                        {stat.name}
                      </dt>
                      <dd className="flex items-baseline">
                        <div className="text-2xl font-semibold text-gray-900 dark:text-white">
                          {stat.value}
                        </div>
                        <div className={`${isRTL ? 'mr-2' : 'ml-2'} flex items-baseline text-sm font-semibold ${
                          stat.changeType === 'positive' ? 'text-green-600' : 'text-red-600'
                        }`}>
                          <TrendingUp className={`h-4 w-4 flex-shrink-0 self-center ${
                            stat.changeType === 'negative' ? 'rotate-180' : ''
                          }`} />
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
          ))}
        </div>

        {/* Features */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Star className="h-6 w-6 text-blue-500" />
              {t('key_features')}
            </CardTitle>
            <CardDescription>
              {t('kui_layout_features')}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {features.map((feature, index) => (
                <div key={index} className={`flex items-start space-x-4 ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}>
                  <div className="flex-shrink-0">
                    <div className="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                      <feature.icon className="w-5 h-5 text-blue-600 dark:text-blue-400" />
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
                className="h-auto p-4 flex flex-col items-center space-y-2"
                onClick={() => window.location.href = '/layout-demo/starter'}
              >
                <Heart className="h-6 w-6 text-orange-500" />
                <span>{t('starter_layout')}</span>
              </Button>
              <Button 
                variant="outline" 
                className="h-auto p-4 flex flex-col items-center space-y-2"
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
        <Card className="bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800">
          <CardContent className="p-6">
            <div className="flex items-start space-x-4">
              <div className="flex-shrink-0">
                <div className="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                  <Star className="w-4 h-4 text-white" />
                </div>
              </div>
              <div>
                <h3 className="text-lg font-medium text-blue-900 dark:text-blue-100">
                  {t('implementation_ready')}
                </h3>
                <p className="text-blue-700 dark:text-blue-300 mt-1">
                  {t('kui_implementation_note')}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </KuiDashboardLayout>
  );
};

export default KuiDemo;
