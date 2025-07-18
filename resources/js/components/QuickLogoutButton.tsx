import React from 'react';
import { Button } from '@/components/ui/button';
import { LogOut } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { route } from 'ziggy-js';
import { cn } from '@/lib/utils';

interface QuickLogoutButtonProps {
  className?: string;
  variant?: 'default' | 'ghost' | 'outline';
  size?: 'sm' | 'default' | 'lg';
  showText?: boolean;
}

export function QuickLogoutButton({ 
  className,
  variant = 'ghost',
  size = 'sm',
  showText = true
}: QuickLogoutButtonProps) {
  return (
    <Link
      method="post"
      href={route('logout')}
      as="button"
      className={cn("inline-flex", className)}
    >
      <Button
        variant={variant}
        size={size}
        className="gap-2 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20"
      >
        <LogOut className="w-4 h-4" />
        {showText && <span>Déconnexion</span>}
      </Button>
    </Link>
  );
}
