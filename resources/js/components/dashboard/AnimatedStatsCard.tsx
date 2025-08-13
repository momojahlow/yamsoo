import React from 'react';
import { LucideIcon } from 'lucide-react';
import { AnimatedCard, AnimatedCardContent } from '@/components/ui/animated-card';
import { Badge } from '@/components/ui/badge';
import { useTranslation } from '@/hooks/useTranslation';

interface AnimatedStatsCardProps {
  title: string;
  value: string | number;
  description?: string;
  icon: LucideIcon;
  trend?: {
    value: number;
    isPositive: boolean;
  };
  badge?: string;
  color?: 'orange' | 'blue' | 'green' | 'purple' | 'pink' | 'indigo';
  href?: string;
}

const colorClasses = {
  orange: {
    gradient: 'from-orange-500 to-red-500',
    bg: 'bg-orange-50 dark:bg-orange-900/20',
    text: 'text-orange-600 dark:text-orange-400',
    ring: 'ring-orange-500/20'
  },
  blue: {
    gradient: 'from-blue-500 to-indigo-500',
    bg: 'bg-blue-50 dark:bg-blue-900/20',
    text: 'text-blue-600 dark:text-blue-400',
    ring: 'ring-blue-500/20'
  },
  green: {
    gradient: 'from-green-500 to-emerald-500',
    bg: 'bg-green-50 dark:bg-green-900/20',
    text: 'text-green-600 dark:text-green-400',
    ring: 'ring-green-500/20'
  },
  purple: {
    gradient: 'from-purple-500 to-pink-500',
    bg: 'bg-purple-50 dark:bg-purple-900/20',
    text: 'text-purple-600 dark:text-purple-400',
    ring: 'ring-purple-500/20'
  },
  pink: {
    gradient: 'from-pink-500 to-rose-500',
    bg: 'bg-pink-50 dark:bg-pink-900/20',
    text: 'text-pink-600 dark:text-pink-400',
    ring: 'ring-pink-500/20'
  },
  indigo: {
    gradient: 'from-indigo-500 to-purple-500',
    bg: 'bg-indigo-50 dark:bg-indigo-900/20',
    text: 'text-indigo-600 dark:text-indigo-400',
    ring: 'ring-indigo-500/20'
  }
};

export const AnimatedStatsCard: React.FC<AnimatedStatsCardProps> = ({
  title,
  value,
  description,
  icon: Icon,
  trend,
  badge,
  color = 'orange',
  href
}) => {
  const { isRTL } = useTranslation();
  const colors = colorClasses[color];

  const CardWrapper = href ? 'a' : 'div';
  const cardProps = href ? { href } : {};

  return (
    <CardWrapper {...cardProps} className={href ? 'block' : ''}>
      <AnimatedCard 
        hover={true} 
        glow={true} 
        gradient={true}
        className={`group cursor-pointer ${colors.bg} border-0 ring-1 ${colors.ring} hover:ring-2 hover:${colors.ring.replace('/20', '/40')}`}
      >
        <AnimatedCardContent className="p-6">
          <div className={`flex items-center justify-between ${isRTL ? 'flex-row-reverse' : ''}`}>
            <div className={`flex-1 ${isRTL ? 'text-right' : 'text-left'}`}>
              <div className="flex items-center justify-between mb-2">
                <p className="text-sm font-medium text-gray-600 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">
                  {title}
                </p>
                {badge && (
                  <Badge className={`bg-gradient-to-r ${colors.gradient} text-white text-xs`}>
                    {badge}
                  </Badge>
                )}
              </div>
              
              <div className="flex items-baseline space-x-2">
                <p className={`text-3xl font-bold ${colors.text} group-hover:scale-105 transition-transform duration-300`}>
                  {value}
                </p>
                {trend && (
                  <span className={`text-sm font-medium ${
                    trend.isPositive 
                      ? 'text-green-600 dark:text-green-400' 
                      : 'text-red-600 dark:text-red-400'
                  }`}>
                    {trend.isPositive ? '+' : ''}{trend.value}%
                  </span>
                )}
              </div>
              
              {description && (
                <p className="text-sm text-gray-500 dark:text-gray-400 mt-1 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">
                  {description}
                </p>
              )}
            </div>
            
            <div className={`${isRTL ? 'mr-4' : 'ml-4'}`}>
              <div className={`w-12 h-12 bg-gradient-to-r ${colors.gradient} rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl group-hover:scale-110 transition-all duration-300`}>
                <Icon className="w-6 h-6 text-white group-hover:scale-110 transition-transform duration-300" />
              </div>
            </div>
          </div>
          
          {/* Effet de brillance au hover */}
          <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent opacity-0 group-hover:opacity-100 group-hover:translate-x-full transition-all duration-1000 transform -translate-x-full"></div>
        </AnimatedCardContent>
      </AnimatedCard>
    </CardWrapper>
  );
};
