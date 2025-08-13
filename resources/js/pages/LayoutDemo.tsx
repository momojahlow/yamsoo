import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import { useTranslation } from '@/hooks/useTranslation';
import { 
  KuiDashboardLayout, 
  StarterDashboardLayout, 
  KwdDashboardLayout,
  ModernLayoutType 
} from '@/Layouts/modern';
import LayoutSelector from '@/components/LayoutSelector';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
  Users, 
  Heart, 
  TreePine, 
  Globe, 
  MessageSquare, 
  Activity,
  TrendingUp,
  Calendar,
  Star
} from 'lucide-react';

const LayoutDemo: React.FC = () => {
  const { t, isRTL } = useTranslation();
  const [currentLayout, setCurrentLayout] = useState<ModernLayoutType>('kwd');

  const layoutComponents = {
    kui: KuiDashboardLayout,
    starter: StarterDashboardLayout,
    kwd: KwdDashboardLayout,
  };

  const LayoutComponent = layoutComponents[currentLayout];

  // Demo content
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

  const recentActivity = [
    {
      id: 1,
      type: 'family_added',
      message: t('new_family_member_added'),
      time: '2 heures',
      icon: Users,
    },
    {
      id: 2,
      type: 'message_received',
      message: t('new_message_received'),
      time: '4 heures',
      icon: MessageSquare,
    },
    {
      id: 3,
      type: 'suggestion_found',
      message: t('new_suggestion_found'),
      time: '1 jour',
      icon: Star,
    },
  ];

  const demoContent = (
    <div className="space-y-8">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
          {t('layout_demo')}
        </h1>
        <p className="mt-2 text-gray-600 dark:text-gray-400">
          {t('layout_demo_description')}
        </p>
      </div>

      {/* Layout Selector */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <TreePine className="h-5 w-5" />
            {t('layout_selector')}
          </CardTitle>
          <CardDescription>
            {t('switch_between_layouts')}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <LayoutSelector 
            currentLayout={currentLayout}
            onLayoutChange={setCurrentLayout}
          />
        </CardContent>
      </Card>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {stats.map((stat) => (
          <Card key={stat.name}>
            <CardContent className="p-6">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <stat.icon className="h-8 w-8 text-gray-400" />
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
                        {stat.changeType === 'positive' ? (
                          <TrendingUp className="h-4 w-4 flex-shrink-0 self-center" />
                        ) : (
                          <TrendingUp className="h-4 w-4 flex-shrink-0 self-center rotate-180" />
                        )}
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

      {/* Recent Activity */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Activity className="h-5 w-5" />
              {t('recent_activity')}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {recentActivity.map((activity) => (
                <div key={activity.id} className={`flex items-center space-x-4 ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}>
                  <div className="flex-shrink-0">
                    <div className="h-8 w-8 bg-orange-100 dark:bg-orange-900/20 rounded-full flex items-center justify-center">
                      <activity.icon className="h-4 w-4 text-orange-600 dark:text-orange-400" />
                    </div>
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium text-gray-900 dark:text-white">
                      {activity.message}
                    </p>
                    <p className="text-sm text-gray-500 dark:text-gray-400">
                      {t('time_ago', { time: activity.time })}
                    </p>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Calendar className="h-5 w-5" />
              {t('upcoming_events')}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div className="text-center py-8">
                <Calendar className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                <p className="text-gray-500 dark:text-gray-400">
                  {t('no_upcoming_events')}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Quick Actions */}
      <Card>
        <CardHeader>
          <CardTitle>{t('quick_actions')}</CardTitle>
          <CardDescription>
            {t('common_actions_description')}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <Button className="h-auto p-4 flex flex-col items-center space-y-2">
              <Users className="h-6 w-6" />
              <span>{t('add_family_member')}</span>
            </Button>
            <Button variant="outline" className="h-auto p-4 flex flex-col items-center space-y-2">
              <TreePine className="h-6 w-6" />
              <span>{t('view_family_tree')}</span>
            </Button>
            <Button variant="outline" className="h-auto p-4 flex flex-col items-center space-y-2">
              <Globe className="h-6 w-6" />
              <span>{t('explore_network')}</span>
            </Button>
            <Button variant="outline" className="h-auto p-4 flex flex-col items-center space-y-2">
              <MessageSquare className="h-6 w-6" />
              <span>{t('send_message')}</span>
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  );

  return (
    <LayoutComponent title={t('layout_demo')}>
      <Head title={t('layout_demo')} />
      {demoContent}
    </LayoutComponent>
  );
};

export default LayoutDemo;
