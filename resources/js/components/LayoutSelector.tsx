import React from 'react';
import { useTranslation } from '@/hooks/useTranslation';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
  KuiDashboardLayout, 
  StarterDashboardLayout, 
  KwdDashboardLayout,
  MODERN_LAYOUTS,
  ModernLayoutType 
} from '@/Layouts/modern';

interface Props {
  currentLayout: ModernLayoutType;
  onLayoutChange: (layout: ModernLayoutType) => void;
}

const LayoutSelector: React.FC<Props> = ({ currentLayout, onLayoutChange }) => {
  const { t, isRTL } = useTranslation();

  const layoutComponents = {
    kui: KuiDashboardLayout,
    starter: StarterDashboardLayout,
    kwd: KwdDashboardLayout,
  };

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">
          {t('choose_layout')}
        </h2>
        <p className="text-gray-600 dark:text-gray-400">
          {t('select_preferred_layout')}
        </p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {Object.entries(MODERN_LAYOUTS).map(([key, layout]) => {
          const layoutKey = key as ModernLayoutType;
          const isActive = currentLayout === layoutKey;
          
          return (
            <Card 
              key={key} 
              className={`cursor-pointer transition-all duration-200 hover:shadow-lg ${
                isActive 
                  ? 'ring-2 ring-orange-500 shadow-lg' 
                  : 'hover:ring-1 hover:ring-gray-300'
              }`}
              onClick={() => onLayoutChange(layoutKey)}
            >
              <CardHeader>
                <div className="flex items-center justify-between">
                  <CardTitle className="text-lg">{layout.name}</CardTitle>
                  {isActive && (
                    <Badge className="bg-orange-500 text-white">
                      {t('active')}
                    </Badge>
                  )}
                </div>
                <CardDescription>{layout.description}</CardDescription>
              </CardHeader>
              
              <CardContent>
                {/* Preview thumbnail */}
                <div className="w-full h-32 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded-lg mb-4 flex items-center justify-center">
                  <span className="text-gray-500 dark:text-gray-400 text-sm">
                    {t('layout_preview')}
                  </span>
                </div>
                
                {/* Features */}
                <div className="space-y-2">
                  <h4 className="text-sm font-medium text-gray-900 dark:text-white">
                    {t('features')}:
                  </h4>
                  <div className="flex flex-wrap gap-1">
                    {layout.features.map((feature, index) => (
                      <Badge 
                        key={index} 
                        variant="outline" 
                        className="text-xs"
                      >
                        {feature}
                      </Badge>
                    ))}
                  </div>
                </div>
                
                <Button 
                  className={`w-full mt-4 ${
                    isActive 
                      ? 'bg-orange-500 hover:bg-orange-600' 
                      : 'bg-gray-200 hover:bg-gray-300 text-gray-700'
                  }`}
                  onClick={(e) => {
                    e.stopPropagation();
                    onLayoutChange(layoutKey);
                  }}
                >
                  {isActive ? t('current_layout') : t('select_layout')}
                </Button>
              </CardContent>
            </Card>
          );
        })}
      </div>

      {/* Layout comparison */}
      <Card>
        <CardHeader>
          <CardTitle>{t('layout_comparison')}</CardTitle>
          <CardDescription>
            {t('compare_layout_features')}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b">
                  <th className={`${isRTL ? 'text-right' : 'text-left'} py-2`}>
                    {t('feature')}
                  </th>
                  {Object.entries(MODERN_LAYOUTS).map(([key, layout]) => (
                    <th key={key} className="text-center py-2">
                      {layout.name}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                <tr className="border-b">
                  <td className="py-2 font-medium">{t('dark_mode')}</td>
                  <td className="text-center py-2">✅</td>
                  <td className="text-center py-2">❌</td>
                  <td className="text-center py-2">✅</td>
                </tr>
                <tr className="border-b">
                  <td className="py-2 font-medium">{t('collapsible_sidebar')}</td>
                  <td className="text-center py-2">❌</td>
                  <td className="text-center py-2">❌</td>
                  <td className="text-center py-2">✅</td>
                </tr>
                <tr className="border-b">
                  <td className="py-2 font-medium">{t('animations')}</td>
                  <td className="text-center py-2">⚡</td>
                  <td className="text-center py-2">⚡</td>
                  <td className="text-center py-2">✅</td>
                </tr>
                <tr className="border-b">
                  <td className="py-2 font-medium">{t('rtl_support')}</td>
                  <td className="text-center py-2">✅</td>
                  <td className="text-center py-2">✅</td>
                  <td className="text-center py-2">✅</td>
                </tr>
                <tr>
                  <td className="py-2 font-medium">{t('responsive_design')}</td>
                  <td className="text-center py-2">✅</td>
                  <td className="text-center py-2">✅</td>
                  <td className="text-center py-2">✅</td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default LayoutSelector;
