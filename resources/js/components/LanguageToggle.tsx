import React from 'react';
import { Button } from '@/components/ui/button';
import { Globe, Languages } from 'lucide-react';
import { useTranslation } from '@/hooks/useTranslation';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface LanguageToggleProps {
  variant?: 'button' | 'dropdown' | 'simple';
  size?: 'sm' | 'md' | 'lg';
  showIcon?: boolean;
  showText?: boolean;
  className?: string;
}

export function LanguageToggle({ 
  variant = 'dropdown', 
  size = 'md', 
  showIcon = true, 
  showText = true,
  className = ''
}: LanguageToggleProps) {
  const { 
    currentLocale, 
    availableLocales, 
    switchLanguage, 
    getCurrentLanguageName,
    getOppositeLanguage,
    isRTL 
  } = useTranslation();

  // Variante simple : toggle entre FR et AR
  if (variant === 'simple') {
    const opposite = getOppositeLanguage();
    
    return (
      <Button
        variant="ghost"
        size={size}
        onClick={() => switchLanguage(opposite.code)}
        className={`${className} ${isRTL ? 'flex-row-reverse' : ''}`}
        title={`Changer vers ${opposite.name}`}
      >
        {showIcon && <Languages className={`${size === 'sm' ? 'w-4 h-4' : 'w-5 h-5'} ${showText ? (isRTL ? 'ml-2' : 'mr-2') : ''}`} />}
        {showText && (
          <span className={size === 'sm' ? 'text-sm' : ''}>
            {opposite.code.toUpperCase()}
          </span>
        )}
      </Button>
    );
  }

  // Variante bouton : affiche la langue actuelle
  if (variant === 'button') {
    return (
      <div className={`flex items-center gap-2 ${className}`}>
        {showIcon && <Globe className={size === 'sm' ? 'w-4 h-4' : 'w-5 h-5'} />}
        {showText && (
          <span className={`font-medium ${size === 'sm' ? 'text-sm' : ''}`}>
            {getCurrentLanguageName()}
          </span>
        )}
      </div>
    );
  }

  // Variante dropdown : menu déroulant avec toutes les langues
  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button
          variant="ghost"
          size={size}
          className={`${className} ${isRTL ? 'flex-row-reverse' : ''}`}
        >
          {showIcon && <Globe className={`${size === 'sm' ? 'w-4 h-4' : 'w-5 h-5'} ${showText ? (isRTL ? 'ml-2' : 'mr-2') : ''}`} />}
          {showText && (
            <span className={size === 'sm' ? 'text-sm' : ''}>
              {currentLocale.toUpperCase()}
            </span>
          )}
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align={isRTL ? 'start' : 'end'} className="min-w-[150px]">
        {Object.entries(availableLocales).map(([code, name]) => (
          <DropdownMenuItem
            key={code}
            onClick={() => switchLanguage(code)}
            className={`${isRTL ? 'text-right' : 'text-left'} ${
              currentLocale === code ? 'bg-orange-50 text-orange-600 font-medium' : ''
            }`}
          >
            <div className={`flex items-center gap-2 w-full ${isRTL ? 'flex-row-reverse' : ''}`}>
              <span className="text-sm font-mono">{code.toUpperCase()}</span>
              <span className="flex-1">{name}</span>
              {currentLocale === code && (
                <div className="w-2 h-2 bg-orange-500 rounded-full"></div>
              )}
            </div>
          </DropdownMenuItem>
        ))}
      </DropdownMenuContent>
    </DropdownMenu>
  );
}

// Composant simple pour un toggle rapide FR/AR
export function QuickLanguageToggle({ className = '' }: { className?: string }) {
  const { getOppositeLanguage, switchLanguage, isRTL } = useTranslation();
  const opposite = getOppositeLanguage();

  return (
    <button
      onClick={() => switchLanguage(opposite.code)}
      className={`
        inline-flex items-center justify-center
        w-8 h-8 rounded-lg
        bg-orange-100 hover:bg-orange-200
        text-orange-600 hover:text-orange-700
        transition-colors duration-200
        text-sm font-medium
        ${className}
      `}
      title={`Switch to ${opposite.name}`}
    >
      {opposite.code.toUpperCase()}
    </button>
  );
}

// Composant pour afficher la langue actuelle avec icône
export function CurrentLanguageDisplay({ className = '' }: { className?: string }) {
  const { getCurrentLanguageName, currentLocale, isRTL } = useTranslation();

  return (
    <div className={`flex items-center gap-2 ${isRTL ? 'flex-row-reverse' : ''} ${className}`}>
      <Globe className="w-4 h-4 text-gray-500" />
      <span className="text-sm text-gray-600">
        {getCurrentLanguageName()}
      </span>
    </div>
  );
}
