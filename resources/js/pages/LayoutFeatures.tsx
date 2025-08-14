import React from 'react';
import { Head } from '@inertiajs/react';
import { useTranslation } from '@/hooks/useTranslation';
import { KwdDashboardLayout } from '@/Layouts/modern';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
  Languages,
  PanelLeftClose,
  PanelLeftOpen,
  Moon,
  Sun,
  Heart,
  Sparkles,
  Zap,
  Star,
  CheckCircle,
  ArrowRight,
  ArrowLeft
} from 'lucide-react';

const LayoutFeatures: React.FC = () => {
  const { t, isRTL } = useTranslation();

  const features = [
    {
      icon: Languages,
      title: t('language_toggle'),
      description: t('instant_language_switching'),
      status: 'active',
      color: 'from-blue-500 to-indigo-500'
    },
    {
      icon: PanelLeftClose,
      title: t('collapsible_sidebar'),
      description: t('space_saving_sidebar'),
      status: 'active',
      color: 'from-green-500 to-emerald-500'
    },
    {
      icon: Moon,
      title: t('dark_mode_toggle'),
      description: t('seamless_theme_switching'),
      status: 'active',
      color: 'from-purple-500 to-pink-500'
    },
    {
      icon: Heart,
      title: t('modern_logo'),
      description: t('interactive_logo_design'),
      status: 'active',
      color: 'from-orange-500 to-red-500'
    },
    {
      icon: Sparkles,
      title: t('glassmorphism_effects'),
      description: t('modern_glass_effects'),
      status: 'active',
      color: 'from-cyan-500 to-blue-500'
    },
    {
      icon: Zap,
      title: t('performance_optimized'),
      description: t('fast_smooth_animations'),
      status: 'active',
      color: 'from-yellow-500 to-orange-500'
    }
  ];

  const improvements = [
    {
      title: t('enhanced_navigation'),
      description: t('improved_sidebar_with_descriptions'),
      icon: Star
    },
    {
      title: t('better_accessibility'),
      description: t('full_rtl_support_tooltips'),
      icon: CheckCircle
    },
    {
      title: t('modern_interactions'),
      description: t('hover_effects_micro_animations'),
      icon: Sparkles
    }
  ];

  return (
    <KwdDashboardLayout title={t('layout_features')}>
      <Head title={t('layout_features')} />
      
      <div className="space-y-8">
        {/* Header */}
        <div className="text-center">
          <h1 className="text-4xl font-bold bg-gradient-to-r from-purple-600 via-pink-600 to-blue-600 bg-clip-text text-transparent mb-4">
            ✨ {t('kwd_layout_features')}
          </h1>
          <p className="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
            {t('discover_enhanced_features')}
          </p>
          <div className="mt-6 flex justify-center space-x-4">
            <Badge className="bg-gradient-to-r from-green-500 to-emerald-500 text-white">
              {t('fully_functional')}
            </Badge>
            <Badge className="bg-gradient-to-r from-blue-500 to-indigo-500 text-white">
              {t('rtl_ready')}
            </Badge>
            <Badge className="bg-gradient-to-r from-purple-500 to-pink-500 text-white">
              {t('modern_design')}
            </Badge>
          </div>
        </div>

        {/* Features Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {features.map((feature, index) => (
            <Card key={index} className="hover:shadow-xl transition-all duration-300 transform hover:scale-105 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm">
              <CardHeader>
                <div className="flex items-center justify-between">
                  <div className={`w-12 h-12 bg-gradient-to-r ${feature.color} rounded-xl flex items-center justify-center shadow-lg`}>
                    <feature.icon className="w-6 h-6 text-white" />
                  </div>
                  <Badge className="bg-green-500 text-white">
                    {t('active')}
                  </Badge>
                </div>
                <CardTitle className="text-lg">{feature.title}</CardTitle>
                <CardDescription>{feature.description}</CardDescription>
              </CardHeader>
            </Card>
          ))}
        </div>

        {/* How to Use */}
        <Card className="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Zap className="h-6 w-6 text-blue-500" />
              {t('how_to_use')}
            </CardTitle>
            <CardDescription>
              {t('guide_to_new_features')}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="space-y-4">
                <h4 className="font-semibold text-gray-900 dark:text-white">
                  {t('sidebar_controls')}
                </h4>
                <div className="space-y-3">
                  <div className={`flex items-center space-x-3 ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}>
                    <div className="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                      <PanelLeftClose className="w-4 h-4" />
                    </div>
                    <span className="text-sm">{t('click_to_collapse_sidebar')}</span>
                  </div>
                  <div className={`flex items-center space-x-3 ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}>
                    <div className="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                      <Languages className="w-4 h-4" />
                    </div>
                    <span className="text-sm">{t('click_to_change_language')}</span>
                  </div>
                  <div className={`flex items-center space-x-3 ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}>
                    <div className="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                      <Moon className="w-4 h-4" />
                    </div>
                    <span className="text-sm">{t('toggle_dark_light_mode')}</span>
                  </div>
                </div>
              </div>
              
              <div className="space-y-4">
                <h4 className="font-semibold text-gray-900 dark:text-white">
                  {t('interactive_elements')}
                </h4>
                <div className="space-y-3">
                  <div className={`flex items-center space-x-3 ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}>
                    <div className="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center">
                      <Heart className="w-4 h-4 text-white" />
                    </div>
                    <span className="text-sm">{t('hover_logo_for_effects')}</span>
                  </div>
                  <div className={`flex items-center space-x-3 ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}>
                    <div className="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                      <Star className="w-4 h-4" />
                    </div>
                    <span className="text-sm">{t('navigation_hover_effects')}</span>
                  </div>
                  <div className={`flex items-center space-x-3 ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}>
                    <div className="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                      <Sparkles className="w-4 h-4" />
                    </div>
                    <span className="text-sm">{t('glassmorphism_backgrounds')}</span>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Improvements */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Star className="h-6 w-6 text-purple-500" />
              {t('key_improvements')}
            </CardTitle>
            <CardDescription>
              {t('what_makes_this_layout_special')}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {improvements.map((improvement, index) => (
                <div key={index} className={`flex items-start space-x-4 p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50 ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}>
                  <div className="flex-shrink-0">
                    <div className="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                      <improvement.icon className="w-5 h-5 text-white" />
                    </div>
                  </div>
                  <div>
                    <h4 className="text-lg font-medium text-gray-900 dark:text-white">
                      {improvement.title}
                    </h4>
                    <p className="text-gray-600 dark:text-gray-400">
                      {improvement.description}
                    </p>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Call to Action */}
        <Card className="bg-gradient-to-r from-orange-500 to-red-500 text-white">
          <CardContent className="p-8 text-center">
            <h3 className="text-2xl font-bold mb-4">
              {t('experience_the_difference')}
            </h3>
            <p className="text-orange-100 mb-6">
              {t('try_all_features_now')}
            </p>
            <div className="flex justify-center space-x-4">
              <div className={`flex items-center space-x-2 ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}>
                <span className="text-sm">{t('try_sidebar_toggle')}</span>
                {isRTL ? <ArrowLeft className="w-4 h-4" /> : <ArrowRight className="w-4 h-4" />}
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </KwdDashboardLayout>
  );
};

export default LayoutFeatures;
