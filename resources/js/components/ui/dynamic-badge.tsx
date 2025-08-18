import React from 'react';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

interface DynamicBadgeProps {
  count: number;
  type?: 'default' | 'urgent' | 'success' | 'warning' | 'info';
  size?: 'sm' | 'md' | 'lg';
  animate?: boolean;
  showZero?: boolean;
  maxCount?: number;
  className?: string;
  children?: React.ReactNode;
}

const typeStyles = {
  default: 'bg-gray-100 text-gray-800 border-gray-200',
  urgent: 'bg-red-500 text-white border-red-600',
  success: 'bg-green-100 text-green-800 border-green-200',
  warning: 'bg-yellow-100 text-yellow-800 border-yellow-200',
  info: 'bg-blue-100 text-blue-800 border-blue-200',
};

const sizeStyles = {
  sm: 'text-xs px-1.5 py-0.5',
  md: 'text-sm px-2 py-1',
  lg: 'text-base px-3 py-1.5',
};

export function DynamicBadge({
  count,
  type = 'default',
  size = 'sm',
  animate = false,
  showZero = false,
  maxCount = 99,
  className,
  children,
}: DynamicBadgeProps) {
  // Ne pas afficher si count est 0 et showZero est false
  if (count === 0 && !showZero) {
    return null;
  }

  // Formater le count si supérieur à maxCount
  const displayCount = count > maxCount ? `${maxCount}+` : count.toString();

  return (
    <Badge
      className={cn(
        typeStyles[type],
        sizeStyles[size],
        animate && 'animate-pulse',
        'border font-medium',
        className
      )}
    >
      {children ? (
        <>
          {children}
          {count > 0 && (
            <span className="ml-1 font-bold">
              {displayCount}
            </span>
          )}
        </>
      ) : (
        displayCount
      )}
    </Badge>
  );
}

// Badge pour notifications avec point rouge
export function NotificationBadge({
  count,
  className,
  children,
}: {
  count: number;
  className?: string;
  children: React.ReactNode;
}) {
  return (
    <div className={cn('relative', className)}>
      {children}
      {count > 0 && (
        <>
          {/* Badge avec nombre */}
          <DynamicBadge
            count={count}
            type="urgent"
            size="sm"
            animate={true}
            className="absolute -top-2 -right-2 min-w-[1.25rem] h-5 flex items-center justify-center rounded-full"
          />
          {/* Point rouge qui pulse */}
          <div className="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-ping" />
        </>
      )}
    </div>
  );
}

// Badge pour actions rapides
export function ActionBadge({
  normalCount,
  urgentCount,
  className,
}: {
  normalCount?: number;
  urgentCount?: number;
  className?: string;
}) {
  if (!normalCount && !urgentCount) return null;

  return (
    <div className={cn('flex flex-col gap-1', className)}>
      {normalCount && normalCount > 0 && (
        <DynamicBadge
          count={normalCount}
          type="info"
          size="sm"
        />
      )}
      {urgentCount && urgentCount > 0 && (
        <DynamicBadge
          count={urgentCount}
          type="urgent"
          size="sm"
          animate={true}
        >
          Nouveau{urgentCount > 1 ? 'x' : ''}
        </DynamicBadge>
      )}
    </div>
  );
}

// Badge pour sidebar avec icône
export function SidebarBadge({
  count,
  newCount,
  title,
  className,
}: {
  count?: number;
  newCount?: number;
  title: string;
  className?: string;
}) {
  if (!count && !newCount) return null;

  return (
    <div className={cn('flex items-center gap-2', className)}>
      <span className="text-sm font-medium">{title}</span>
      <div className="flex gap-1">
        {count && count > 0 && (
          <DynamicBadge
            count={count}
            type="default"
            size="sm"
          >
            Total
          </DynamicBadge>
        )}
        {newCount && newCount > 0 && (
          <DynamicBadge
            count={newCount}
            type="success"
            size="sm"
            animate={true}
          >
            Nouveau{newCount > 1 ? 'x' : ''}
          </DynamicBadge>
        )}
      </div>
    </div>
  );
}
