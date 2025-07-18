import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { LogOut, X } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { route } from 'ziggy-js';
import { cn } from '@/lib/utils';

interface FloatingLogoutButtonProps {
  className?: string;
  showOnMobile?: boolean;
  showOnDesktop?: boolean;
}

export function FloatingLogoutButton({ 
  className,
  showOnMobile = true,
  showOnDesktop = false 
}: FloatingLogoutButtonProps) {
  const [isExpanded, setIsExpanded] = useState(false);

  const toggleExpanded = () => {
    setIsExpanded(!isExpanded);
  };

  return (
    <div className={cn(
      "fixed bottom-4 right-4 z-50",
      showOnMobile && !showOnDesktop && "md:hidden",
      !showOnMobile && showOnDesktop && "hidden md:block",
      showOnMobile && showOnDesktop && "block",
      className
    )}>
      {isExpanded ? (
        // Version étendue avec confirmation
        <div className="bg-white dark:bg-gray-800 rounded-lg shadow-lg border p-3 min-w-48">
          <div className="flex items-center justify-between mb-2">
            <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
              Se déconnecter ?
            </span>
            <Button
              variant="ghost"
              size="sm"
              onClick={toggleExpanded}
              className="h-6 w-6 p-0"
            >
              <X className="h-4 w-4" />
            </Button>
          </div>
          <div className="flex gap-2">
            <Link
              method="post"
              href={route('logout')}
              as="button"
              className="flex-1"
            >
              <Button
                size="sm"
                variant="destructive"
                className="w-full text-xs"
              >
                <LogOut className="w-3 h-3 mr-1" />
                Confirmer
              </Button>
            </Link>
            <Button
              size="sm"
              variant="outline"
              onClick={toggleExpanded}
              className="text-xs"
            >
              Annuler
            </Button>
          </div>
        </div>
      ) : (
        // Version compacte
        <Button
          onClick={toggleExpanded}
          size="lg"
          className="rounded-full shadow-lg bg-red-500 hover:bg-red-600 text-white border-2 border-white dark:border-gray-800"
        >
          <LogOut className="w-5 h-5" />
          <span className="sr-only">Déconnexion</span>
        </Button>
      )}
    </div>
  );
}
