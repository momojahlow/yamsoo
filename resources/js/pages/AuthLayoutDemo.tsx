import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import { useTranslation } from '@/hooks/useTranslation';
import { KwdAuthLayout } from '@/Layouts/modern';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { 
  Heart,
  Mail,
  Lock,
  Eye,
  EyeOff,
  Sparkles,
  Shield,
  Users,
  TreePine,
  Star,
  CheckCircle
} from 'lucide-react';

const AuthLayoutDemo: React.FC = () => {
  const { t, isRTL } = useTranslation();
  const [showPassword, setShowPassword] = useState(false);
  const [formData, setFormData] = useState({
    email: 'demo@yamsoo.com',
    password: 'password123'
  });

  const features = [
    {
      icon: Sparkles,
      title: t('modern_design'),
      description: t('glassmorphism_effects')
    },
    {
      icon: Shield,
      title: t('secure_authentication'),
      description: t('protected_forms')
    },
    {
      icon: Users,
      title: t('responsive_layout'),
      description: t('mobile_desktop_optimized')
    },
    {
      icon: Heart,
      title: t('family_focused'),
      description: t('designed_for_families')
    }
  ];

  const demoContent = (
    <div className="space-y-6">
      {/* Demo Header */}
      <div className="text-center space-y-2">
        <div className="flex items-center justify-center space-x-2 mb-4">
          <div className="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center">
            <Heart className="w-5 h-5 text-white" />
          </div>
          <h1 className="text-2xl font-bold bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent">
            {t('auth_layout_demo')}
          </h1>
        </div>
        <p className="text-gray-600 dark:text-gray-400">
          {t('experience_modern_auth')}
        </p>
        <Badge className="bg-gradient-to-r from-purple-500 to-pink-500 text-white">
          {t('kwd_powered')}
        </Badge>
      </div>

      {/* Demo Form */}
      <form className="space-y-4">
        <div className="space-y-2">
          <Label htmlFor="email" className="text-sm font-medium">
            {t('email_address')}
          </Label>
          <div className="relative">
            <Mail className={`absolute top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400 ${isRTL ? 'right-3' : 'left-3'}`} />
            <Input
              id="email"
              type="email"
              value={formData.email}
              onChange={(e) => setFormData({...formData, email: e.target.value})}
              className={`${isRTL ? 'pr-10' : 'pl-10'} bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600`}
              placeholder={t('enter_email')}
            />
          </div>
        </div>

        <div className="space-y-2">
          <Label htmlFor="password" className="text-sm font-medium">
            {t('password')}
          </Label>
          <div className="relative">
            <Lock className={`absolute top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400 ${isRTL ? 'right-3' : 'left-3'}`} />
            <Input
              id="password"
              type={showPassword ? 'text' : 'password'}
              value={formData.password}
              onChange={(e) => setFormData({...formData, password: e.target.value})}
              className={`${isRTL ? 'pr-10 pl-10' : 'pl-10 pr-10'} bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600`}
              placeholder={t('enter_password')}
            />
            <button
              type="button"
              onClick={() => setShowPassword(!showPassword)}
              className={`absolute top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400 hover:text-gray-600 ${isRTL ? 'left-3' : 'right-3'}`}
            >
              {showPassword ? <EyeOff /> : <Eye />}
            </button>
          </div>
        </div>

        <Button 
          type="button"
          className="w-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-medium h-11"
        >
          {t('demo_login')}
        </Button>
      </form>

      {/* Features List */}
      <div className="space-y-4">
        <h3 className="text-lg font-semibold text-gray-900 dark:text-white text-center">
          {t('layout_features')}
        </h3>
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
          {features.map((feature, index) => (
            <div 
              key={index}
              className={`flex items-center space-x-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 ${isRTL ? 'flex-row-reverse space-x-reverse' : ''}`}
            >
              <div className="flex-shrink-0">
                <div className="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center">
                  <feature.icon className="w-4 h-4 text-white" />
                </div>
              </div>
              <div className="min-w-0">
                <h4 className="text-sm font-medium text-gray-900 dark:text-white">
                  {feature.title}
                </h4>
                <p className="text-xs text-gray-600 dark:text-gray-400">
                  {feature.description}
                </p>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Demo Actions */}
      <div className="space-y-3">
        <div className="flex items-center justify-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
          <CheckCircle className="w-4 h-4 text-green-500" />
          <span>{t('demo_mode_active')}</span>
        </div>
        
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <Button 
            variant="outline"
            onClick={() => window.location.href = '/layout-demo'}
            className="text-sm"
          >
            <TreePine className={`w-4 h-4 ${isRTL ? 'ml-2' : 'mr-2'}`} />
            {t('dashboard_layouts')}
          </Button>
          <Button 
            variant="outline"
            onClick={() => window.location.href = '/login'}
            className="text-sm"
          >
            <Star className={`w-4 h-4 ${isRTL ? 'ml-2' : 'mr-2'}`} />
            {t('real_login')}
          </Button>
        </div>
      </div>

      {/* Technical Info */}
      <div className="text-center space-y-2 pt-4 border-t border-gray-200 dark:border-gray-700">
        <p className="text-xs text-gray-500 dark:text-gray-400">
          {t('powered_by_kwd_layout')}
        </p>
        <div className="flex justify-center space-x-2">
          <Badge variant="outline" className="text-xs">
            React + TypeScript
          </Badge>
          <Badge variant="outline" className="text-xs">
            Tailwind CSS
          </Badge>
          <Badge variant="outline" className="text-xs">
            Glassmorphism
          </Badge>
        </div>
      </div>
    </div>
  );

  return (
    <KwdAuthLayout 
      title={t('auth_layout_demo')}
      showBackButton={true}
      backUrl="/layout-demo"
    >
      <Head title={t('auth_layout_demo')} />
      {demoContent}
    </KwdAuthLayout>
  );
};

export default AuthLayoutDemo;
