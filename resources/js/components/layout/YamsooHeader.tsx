import React from 'react';
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { route } from 'ziggy-js';
import { Menu, X } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from '@/hooks/useTranslation';

interface User {
  id: number;
  name: string;
  email: string;
}

interface YamsooHeaderProps {
  user?: User | null;
  showNavigation?: boolean;
  className?: string;
}

export function YamsooHeader({ user, showNavigation = true, className = "" }: YamsooHeaderProps) {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const { t, isRTL } = useTranslation();

  return (
    <header className={`bg-white/80 backdrop-blur-sm border-b border-gray-200/50 sticky top-0 z-50 ${className}`}>
      <div className="container mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
        <nav className="flex items-center justify-between h-16 sm:h-20">
          {/* Logo - Responsive */}
          <Link href="/" className="flex items-center space-x-2 sm:space-x-3 group">
            {/* Logo thumb pour mobile, complet pour desktop */}
            <div className="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform duration-200">
              <span className="text-white font-bold text-sm sm:text-lg md:text-2xl">Y</span>
            </div>
            {/* Texte Yamsoo caché sur très petit mobile, visible à partir de sm */}
            <span className="hidden sm:block text-xl sm:text-2xl md:text-3xl font-bold bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent">
              Yamsoo!
            </span>
          </Link>

          {/* Navigation Desktop */}
          {showNavigation && (
            <div className="hidden md:flex items-center space-x-6">
              {user ? (
                // Utilisateur connecté
                <>
                  <Link href={route('dashboard')} className="text-gray-600 hover:text-orange-600 font-medium transition-colors duration-200">
                    {t('dashboard')}
                  </Link>
                  <Link href={route('family')} className="text-gray-600 hover:text-orange-600 font-medium transition-colors duration-200">
                    {t('family')}
                  </Link>
                  <Link href={route('profile.index')} className="text-gray-600 hover:text-orange-600 font-medium transition-colors duration-200">
                    {t('profile')}
                  </Link>
                  <div className={`flex items-center space-x-3 ${isRTL ? 'space-x-reverse' : ''}`}>
                    <span className="text-gray-600 font-medium text-sm">
                      {t('hello')}, {user.name}
                    </span>
                    <Link href={route('dashboard')}>
                      <Button className="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 shadow-lg hover:scale-105 transition-all duration-200 text-sm">
                        {t('my_account')}
                      </Button>
                    </Link>
                  </div>
                </>
              ) : (
                // Utilisateur non connecté
                <>
                  <Link href={route('login')}>
                    <Button variant="ghost" className="font-medium text-gray-700 hover:text-orange-600 hover:bg-orange-50 transition-colors duration-200">
                      {t('sign_in')}
                    </Button>
                  </Link>
                  <Link href={route('register')}>
                    <Button className="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 shadow-lg hover:scale-105 transition-all duration-200">
                      {t('sign_up')}
                    </Button>
                  </Link>
                </>
              )}
            </div>
          )}

          {/* Bouton menu mobile */}
          {showNavigation && (
            <button
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="md:hidden p-2 rounded-lg text-gray-600 hover:text-orange-600 hover:bg-orange-50 transition-colors duration-200"
            >
              {mobileMenuOpen ? (
                <X className="w-5 h-5" />
              ) : (
                <Menu className="w-5 h-5" />
              )}
            </button>
          )}
        </nav>

        {/* Menu mobile */}
        {showNavigation && mobileMenuOpen && (
          <div className="md:hidden border-t border-gray-200/50 bg-white/95 backdrop-blur-sm">
            <div className="py-4 space-y-3">
              {user ? (
                // Utilisateur connecté - Mobile
                <>
                  <div className="px-4 py-2 border-b border-gray-200/50">
                    <span className="text-gray-600 font-medium text-sm">
                      Bonjour, {user.name}
                    </span>
                  </div>
                  <Link 
                    href={route('dashboard')} 
                    className="block px-4 py-2 text-gray-600 hover:text-orange-600 hover:bg-orange-50 font-medium transition-colors duration-200"
                    onClick={() => setMobileMenuOpen(false)}
                  >
                    Tableau de bord
                  </Link>
                  <Link
                    href={route('family')}
                    className="block px-4 py-2 text-gray-600 hover:text-orange-600 hover:bg-orange-50 font-medium transition-colors duration-200"
                    onClick={() => setMobileMenuOpen(false)}
                  >
                    Ma famille
                  </Link>
                  <Link 
                    href={route('profile.index')} 
                    className="block px-4 py-2 text-gray-600 hover:text-orange-600 hover:bg-orange-50 font-medium transition-colors duration-200"
                    onClick={() => setMobileMenuOpen(false)}
                  >
                    Mon profil
                  </Link>
                  <div className="px-4 py-2">
                    <Link href={route('dashboard')}>
                      <Button 
                        className="w-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 shadow-lg transition-all duration-200 text-sm"
                        onClick={() => setMobileMenuOpen(false)}
                      >
                        Mon compte
                      </Button>
                    </Link>
                  </div>
                </>
              ) : (
                // Utilisateur non connecté - Mobile
                <>
                  <div className="px-4 py-2 space-y-3">
                    <Link href={route('login')}>
                      <Button 
                        variant="ghost" 
                        className="w-full font-medium text-gray-700 hover:text-orange-600 hover:bg-orange-50 transition-colors duration-200"
                        onClick={() => setMobileMenuOpen(false)}
                      >
                        Se connecter
                      </Button>
                    </Link>
                    <Link href={route('register')}>
                      <Button 
                        className="w-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 shadow-lg transition-all duration-200"
                        onClick={() => setMobileMenuOpen(false)}
                      >
                        S'inscrire
                      </Button>
                    </Link>
                  </div>
                </>
              )}
            </div>
          </div>
        )}
      </div>
    </header>
  );
}
