import React from 'react';
import { cn } from '@/lib/utils';

interface NotificationBadgeProps {
  count: number;
  maxCount?: number;
  size?: 'sm' | 'md' | 'lg';
  variant?: 'default' | 'destructive' | 'warning' | 'success';
  className?: string;
  position?: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left';
}

export function NotificationBadge({
  count,
  maxCount = 99,
  size = 'md',
  variant = 'destructive',
  className,
  position = 'top-right'
}: NotificationBadgeProps) {
  if (count <= 0) return null;

  const displayCount = count > maxCount ? `${maxCount}+` : count.toString();

  const sizeClasses = {
    sm: 'min-w-[16px] h-4 text-[10px]',
    md: 'min-w-[20px] h-5 text-xs',
    lg: 'min-w-[24px] h-6 text-sm'
  };

  const variantClasses = {
    default: 'bg-blue-500 text-white',
    destructive: 'bg-red-500 text-white',
    warning: 'bg-yellow-500 text-black',
    success: 'bg-green-500 text-white'
  };

  const positionClasses = {
    'top-right': '-top-2 -right-2',
    'top-left': '-top-2 -left-2',
    'bottom-right': '-bottom-2 -right-2',
    'bottom-left': '-bottom-2 -left-2'
  };

  return (
    <span
      className={cn(
        'absolute rounded-full flex items-center justify-center font-medium shadow-lg border-2 border-white z-10 px-1',
        sizeClasses[size],
        variantClasses[variant],
        positionClasses[position],
        className
      )}
    >
      {displayCount}
    </span>
  );
}

// Hook pour utiliser le badge avec des données en temps réel
export function useNotificationBadge(apiEndpoint: string, refreshInterval: number = 30000) {
  const [count, setCount] = React.useState(0);
  const [loading, setLoading] = React.useState(true);

  React.useEffect(() => {
    const fetchCount = async () => {
      try {
        const response = await fetch(apiEndpoint);
        const data = await response.json();
        setCount(data.count || 0);
      } catch (error) {
        console.error('Erreur lors de la récupération du compteur:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchCount();
    const interval = setInterval(fetchCount, refreshInterval);

    return () => clearInterval(interval);
  }, [apiEndpoint, refreshInterval]);

  return { count, loading };
}
