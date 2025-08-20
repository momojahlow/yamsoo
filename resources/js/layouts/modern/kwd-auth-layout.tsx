import React, { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import { useTranslation } from '@/hooks/useTranslation';
import {
  Moon,
  Sun,
  Globe,
  ArrowLeft,
  ArrowRight,
  Sparkles,
  Shield,
  Users,
  TreePine
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface Props {
  children: React.ReactNode;
  title?: string;
  showBackButton?: boolean;
  backUrl?: string;
}

const KwdAuthLayout: React.FC<Props> = ({ 
  children, 
  title = 'Yamsoo', 
  showBackButton = false,
  backUrl = '/'
}) => {
  const { t, isRTL } = useTranslation();
  const [darkMode, setDarkMode] = useState(false);

  useEffect(() => {
    const isDark = localStorage.getItem('darkMode') === 'true';
    setDarkMode(isDark);
    if (isDark) {
      document.documentElement.classList.add('dark');
    }
  }, []);

  const toggleDarkMode = () => {
    const newDarkMode = !darkMode;
    setDarkMode(newDarkMode);
    localStorage.setItem('darkMode', newDarkMode.toString());
    if (newDarkMode) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  };

  const features = [
    {
      icon: Users,
      title: t('connect_family'),
      description: t('build_family_network')
    },
    {
      icon: TreePine,
      title: t('family_tree'),
      description: t('visualize_relationships')
    },
    {
      icon: Shield,
      title: t('secure_private'),
      description: t('data_protection')
    }
  ];

  return (
    <div className={`min-h-screen bg-gradient-to-br from-orange-50 via-red-50 to-pink-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 ${isRTL ? 'rtl' : 'ltr'}`}>
      <Head title={title} />

      {/* Background Pattern - Adapté au style du site */}
      <div className="absolute inset-0 overflow-hidden">
        <div className="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-r from-orange-400 to-red-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse"></div>
        <div className="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-r from-red-400 to-pink-400 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-pulse delay-1000"></div>
        <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-gradient-to-r from-orange-300 to-red-300 rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-pulse delay-500"></div>
      </div>

      {/* Header */}
      <header className="relative z-10 p-4 lg:p-6">
        <div className="flex items-center justify-between">
          {/* Logo */}
          <Link href="/" className="flex items-center group">
            <div className="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-500 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-110">
              <span className="text-white font-bold text-xl">Y</span>
            </div>
            <span className={`${isRTL ? 'mr-3' : 'ml-3'} text-2xl font-bold bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent`}>
              Yamsoo
            </span>
          </Link>

          {/* Controls */}
          <div className="flex items-center space-x-4">
            {/* Language Toggle */}
            <Link
              href={isRTL ? '/language/fr' : '/language/ar'}
              className="p-2 rounded-lg bg-white/20 dark:bg-gray-800/20 backdrop-blur-sm hover:bg-white/30 dark:hover:bg-gray-800/30 transition-all duration-200"
            >
              <Globe className="w-5 h-5 text-gray-700 dark:text-gray-300" />
            </Link>

            {/* Dark Mode Toggle */}
            <Button
              variant="ghost"
              size="sm"
              onClick={toggleDarkMode}
              className="p-2 rounded-lg bg-white/20 dark:bg-gray-800/20 backdrop-blur-sm hover:bg-white/30 dark:hover:bg-gray-800/30 transition-all duration-200"
            >
              {darkMode ? (
                <Sun className="w-5 h-5 text-yellow-500" />
              ) : (
                <Moon className="w-5 h-5 text-gray-700" />
              )}
            </Button>

            {/* Back Button */}
            {showBackButton && (
              <Link
                href={backUrl}
                className="flex items-center px-3 py-2 rounded-lg bg-white/20 dark:bg-gray-800/20 backdrop-blur-sm hover:bg-white/30 dark:hover:bg-gray-800/30 transition-all duration-200 text-gray-700 dark:text-gray-300"
              >
                {isRTL ? (
                  <ArrowRight className="w-4 h-4 mr-2" />
                ) : (
                  <ArrowLeft className="w-4 h-4 mr-2" />
                )}
                <span className="text-sm font-medium">{t('back')}</span>
              </Link>
            )}
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="relative z-10 flex-1 flex items-center justify-center px-4 py-8 lg:py-12">
        <div className="w-full max-w-6xl mx-auto">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            
            {/* Left Side - Features (Hidden on mobile) */}
            <div className="hidden lg:block space-y-8">
              <div className="space-y-4">
                <div className="flex items-center space-x-2">
                  <Sparkles className="w-6 h-6 text-purple-500" />
                  <Badge className="bg-gradient-to-r from-purple-500 to-pink-500 text-white">
                    {t('family_platform')}
                  </Badge>
                </div>
                <h1 className="text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white leading-tight">
                  {t('welcome_to_yamsoo')}
                </h1>
                <p className="text-xl text-gray-600 dark:text-gray-400 leading-relaxed">
                  {t('connect_discover_celebrate')}
                </p>
              </div>

              {/* Features List */}
              <div className="space-y-6">
                {features.map((feature, index) => (
                  <div 
                    key={index} 
                    className={`flex items-start space-x-4 p-4 rounded-xl bg-white/30 dark:bg-gray-800/30 backdrop-blur-sm hover:bg-white/40 dark:hover:bg-gray-800/40 transition-all duration-300 ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}
                  >
                    <div className="flex-shrink-0">
                      <div className="w-12 h-12 bg-gradient-to-r from-orange-500 to-red-500 rounded-xl flex items-center justify-center shadow-lg">
                        <feature.icon className="w-6 h-6 text-white" />
                      </div>
                    </div>
                    <div>
                      <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                        {feature.title}
                      </h3>
                      <p className="text-gray-600 dark:text-gray-400">
                        {feature.description}
                      </p>
                    </div>
                  </div>
                ))}
              </div>

              {/* Stats */}
              <div className="grid grid-cols-3 gap-4">
                <div className="text-center p-4 rounded-xl bg-white/20 dark:bg-gray-800/20 backdrop-blur-sm">
                  <div className="text-2xl font-bold text-gray-900 dark:text-white">1000+</div>
                  <div className="text-sm text-gray-600 dark:text-gray-400">{t('families')}</div>
                </div>
                <div className="text-center p-4 rounded-xl bg-white/20 dark:bg-gray-800/20 backdrop-blur-sm">
                  <div className="text-2xl font-bold text-gray-900 dark:text-white">5000+</div>
                  <div className="text-sm text-gray-600 dark:text-gray-400">{t('connections')}</div>
                </div>
                <div className="text-center p-4 rounded-xl bg-white/20 dark:bg-gray-800/20 backdrop-blur-sm">
                  <div className="text-2xl font-bold text-gray-900 dark:text-white">24/7</div>
                  <div className="text-sm text-gray-600 dark:text-gray-400">{t('support')}</div>
                </div>
              </div>
            </div>

            {/* Right Side - Auth Form */}
            <div className="w-full max-w-md mx-auto lg:mx-0">
              <Card className="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl border-0 shadow-2xl">
                <CardContent className="p-8">
                  {children}
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </main>

      {/* Footer */}
      <footer className="relative z-10 p-4 lg:p-6">
        <div className="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
          <div className="flex items-center space-x-6 text-sm text-gray-600 dark:text-gray-400">
            <Link href="/terms" className="hover:text-gray-900 dark:hover:text-white transition-colors">
              {t('terms_of_service')}
            </Link>
            <Link href="/privacy" className="hover:text-gray-900 dark:hover:text-white transition-colors">
              {t('privacy_policy')}
            </Link>
            <Link href="/contact" className="hover:text-gray-900 dark:hover:text-white transition-colors">
              {t('contact')}
            </Link>
          </div>
          <div className="text-sm text-gray-600 dark:text-gray-400">
            © 2024 Yamsoo. {t('all_rights_reserved')}
          </div>
        </div>
      </footer>
    </div>
  );
};

export default KwdAuthLayout;
