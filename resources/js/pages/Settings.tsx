import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { useTranslation } from '@/hooks/useTranslation';
import { KwdDashboardLayout } from '@/Layouts/modern';
import { AnimatedCard, AnimatedCardContent, AnimatedCardHeader, AnimatedCardTitle, AnimatedCardDescription } from '@/components/ui/animated-card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
  Settings as SettingsIcon,
  User,
  Lock,
  Palette,
  Bell,
  Shield,
  Globe,
  Smartphone,
  Database,
  HelpCircle,
  ChevronRight,
  ChevronLeft,
  Languages,
  Moon,
  Sun,
  Volume2,
  Eye,
  Download
} from 'lucide-react';

interface SettingsCategory {
  id: string;
  title: string;
  description: string;
  icon: React.ComponentType<any>;
  href: string;
  badge?: string;
  color: string;
}

const Settings: React.FC = () => {
  const { t, isRTL } = useTranslation();
  const [activeCategory, setActiveCategory] = useState<string | null>(null);

  const settingsCategories: SettingsCategory[] = [
    {
      id: 'profile',
      title: t('profile_settings'),
      description: t('manage_personal_info'),
      icon: User,
      href: '/settings/profile',
      color: 'from-blue-500 to-indigo-500'
    },
    {
      id: 'security',
      title: t('security_privacy'),
      description: t('password_privacy_settings'),
      icon: Shield,
      href: '/settings/password',
      color: 'from-green-500 to-emerald-500'
    },
    {
      id: 'appearance',
      title: t('appearance'),
      description: t('theme_language_display'),
      icon: Palette,
      href: '/settings/appearance',
      color: 'from-purple-500 to-pink-500'
    },
    {
      id: 'notifications',
      title: t('notification_settings'),
      description: t('manage_alerts_emails'),
      icon: Bell,
      href: '/settings/notifications',
      badge: 'Nouveau',
      color: 'from-orange-500 to-red-500'
    },
    {
      id: 'privacy',
      title: t('privacy_settings'),
      description: t('control_data_sharing'),
      icon: Eye,
      href: '/settings/privacy',
      color: 'from-gray-500 to-gray-600'
    },
    {
      id: 'language',
      title: t('language_region'),
      description: t('language_timezone_settings'),
      icon: Globe,
      href: '/settings/language',
      color: 'from-cyan-500 to-blue-500'
    },
    {
      id: 'mobile',
      title: t('mobile_settings'),
      description: t('app_mobile_preferences'),
      icon: Smartphone,
      href: '/settings/mobile',
      color: 'from-pink-500 to-rose-500'
    },
    {
      id: 'data',
      title: t('data_export'),
      description: t('download_backup_data'),
      icon: Download,
      href: '/settings/data',
      color: 'from-indigo-500 to-purple-500'
    }
  ];

  const quickActions = [
    {
      title: t('change_language'),
      description: isRTL ? 'Français' : 'العربية',
      icon: Languages,
      action: () => window.location.href = isRTL ? '/language/fr' : '/language/ar'
    },
    {
      title: t('toggle_theme'),
      description: t('switch_dark_light'),
      icon: Moon,
      action: () => {
        // Toggle theme logic would go here
        console.log('Toggle theme');
      }
    },
    {
      title: t('logout'),
      description: t('sign_out_account'),
      icon: Lock,
      action: () => window.location.href = '/logout',
      danger: true
    }
  ];

  return (
    <KwdDashboardLayout title={t('settings')}>
      <Head title={t('settings')} />
      
      <div className="space-y-8">
        {/* Header */}
        <div className="text-center">
          <div className="flex items-center justify-center mb-4">
            <div className="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center shadow-lg">
              <SettingsIcon className="w-8 h-8 text-white" />
            </div>
          </div>
          <h1 className="text-4xl font-bold bg-gradient-to-r from-purple-600 via-pink-600 to-blue-600 bg-clip-text text-transparent mb-4">
            {t('settings')}
          </h1>
          <p className="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
            {t('customize_experience')}
          </p>
        </div>

        {/* Quick Actions */}
        <AnimatedCard>
          <AnimatedCardHeader>
            <AnimatedCardTitle className="flex items-center gap-2">
              <Volume2 className="h-5 w-5 text-orange-500" />
              {t('quick_actions')}
            </AnimatedCardTitle>
            <AnimatedCardDescription>
              {t('frequently_used_settings')}
            </AnimatedCardDescription>
          </AnimatedCardHeader>
          <AnimatedCardContent>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              {quickActions.map((action, index) => (
                <Button
                  key={index}
                  variant="outline"
                  onClick={action.action}
                  className={`h-auto p-4 justify-start ${action.danger ? 'border-red-200 hover:border-red-300 hover:bg-red-50 dark:hover:bg-red-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700'} ${isRTL ? 'flex-row-reverse' : ''}`}
                >
                  <div className={`flex items-center space-x-3 ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}>
                    <div className={`w-10 h-10 rounded-lg flex items-center justify-center ${action.danger ? 'bg-red-100 dark:bg-red-900/30' : 'bg-gray-100 dark:bg-gray-700'}`}>
                      <action.icon className={`w-5 h-5 ${action.danger ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400'}`} />
                    </div>
                    <div className={`text-left ${isRTL ? 'text-right' : ''}`}>
                      <p className={`font-medium ${action.danger ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'}`}>
                        {action.title}
                      </p>
                      <p className="text-sm text-gray-500 dark:text-gray-400">
                        {action.description}
                      </p>
                    </div>
                  </div>
                </Button>
              ))}
            </div>
          </AnimatedCardContent>
        </AnimatedCard>

        {/* Settings Categories */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {settingsCategories.map((category) => (
            <Link key={category.id} href={category.href}>
              <AnimatedCard 
                hover={true}
                glow={true}
                className="h-full cursor-pointer group"
                onMouseEnter={() => setActiveCategory(category.id)}
                onMouseLeave={() => setActiveCategory(null)}
              >
                <AnimatedCardContent className="p-6">
                  <div className={`flex items-start justify-between ${isRTL ? 'flex-row-reverse' : ''}`}>
                    <div className="flex-1">
                      <div className={`flex items-center space-x-3 mb-3 ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}>
                        <div className={`w-12 h-12 bg-gradient-to-r ${category.color} rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl group-hover:scale-110 transition-all duration-300`}>
                          <category.icon className="w-6 h-6 text-white group-hover:scale-110 transition-transform duration-300" />
                        </div>
                        {category.badge && (
                          <Badge className="bg-gradient-to-r from-orange-500 to-red-500 text-white text-xs">
                            {category.badge}
                          </Badge>
                        )}
                      </div>
                      
                      <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                        {category.title}
                      </h3>
                      
                      <p className="text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">
                        {category.description}
                      </p>
                    </div>
                    
                    <div className={`${isRTL ? 'mr-4' : 'ml-4'} opacity-0 group-hover:opacity-100 transition-opacity duration-300`}>
                      {isRTL ? (
                        <ChevronLeft className="w-5 h-5 text-gray-400 group-hover:text-purple-500 transition-colors" />
                      ) : (
                        <ChevronRight className="w-5 h-5 text-gray-400 group-hover:text-purple-500 transition-colors" />
                      )}
                    </div>
                  </div>
                </AnimatedCardContent>
              </AnimatedCard>
            </Link>
          ))}
        </div>

        {/* Help Section */}
        <AnimatedCard className="border-0 shadow-lg bg-white/80 backdrop-blur-sm">
          <AnimatedCardContent className="p-8 text-center">
            <div className="flex items-center justify-center mb-4">
              <div className="w-12 h-12 bg-gradient-to-r from-orange-500 to-red-500 rounded-xl flex items-center justify-center">
                <HelpCircle className="w-6 h-6 text-white" />
              </div>
            </div>
            <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
              {t('need_help')}
            </h3>
            <p className="text-gray-600 dark:text-gray-400 mb-4">
              {t('settings_help_description')}
            </p>
            <div className={`flex justify-center space-x-4 ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}>
              <Button variant="outline" className="border-orange-200 text-orange-600 hover:bg-orange-50">
                {t('contact_support')}
              </Button>
              <Button className="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white">
                {t('view_documentation')}
              </Button>
            </div>
          </AnimatedCardContent>
        </AnimatedCard>
      </div>
    </KwdDashboardLayout>
  );
};

export default Settings;
