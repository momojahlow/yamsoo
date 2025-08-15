import React, { useState, useEffect } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { useTranslation } from '@/hooks/useTranslation';
import {
  Menu,
  X,
  Home,
  Users,
  Settings,
  Bell,
  Search,
  ChevronDown,
  User,
  LogOut,
  Moon,
  Sun,
  Globe,
  Heart,
  TreePine,
  MessageSquare,
  Activity,
  ChevronLeft,
  ChevronRight,
  Languages,
  PanelLeftClose,
  PanelLeftOpen,
  Camera,
  Image
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface Props {
  children: React.ReactNode;
  title?: string;
}

interface User {
  id: number;
  name: string;
  email: string;
  profile?: {
    avatar_url?: string;
  };
}

interface PageProps {
  auth: {
    user: User;
  };
}

const KwdDashboardLayout: React.FC<Props> = ({ children, title = 'Dashboard' }) => {
  const { t, isRTL } = useTranslation();
  const { auth } = usePage<PageProps>().props;
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [sidebarCollapsed, setSidebarCollapsed] = useState(false);
  const [sidebarHovered, setSidebarHovered] = useState(false);
  const [darkMode, setDarkMode] = useState(false);

  useEffect(() => {
    const isDark = localStorage.getItem('darkMode') === 'true';
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    setDarkMode(isDark);
    setSidebarCollapsed(isCollapsed);
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

  const toggleSidebar = () => {
    const newCollapsed = !sidebarCollapsed;
    setSidebarCollapsed(newCollapsed);
    localStorage.setItem('sidebarCollapsed', newCollapsed.toString());
  };

  const navigation = [
    { 
      name: t('dashboard'), 
      href: '/dashboard', 
      icon: Home, 
      badge: null,
      description: t('overview_stats')
    },
    { 
      name: t('my_family'), 
      href: '/famille', 
      icon: Users, 
      badge: '3',
      description: t('family_members')
    },
    { 
      name: t('family_tree'), 
      href: '/famille/arbre', 
      icon: TreePine, 
      badge: null,
      description: t('family_tree_view')
    },
    { 
      name: t('networks'), 
      href: '/reseaux', 
      icon: Globe, 
      badge: '12',
      description: t('discover_connect')
    },
    { 
      name: t('messages'), 
      href: '/messagerie', 
      icon: MessageSquare, 
      badge: '2',
      description: t('family_chat')
    },
    {
      name: t('suggestions'),
      href: '/suggestions',
      icon: Activity,
      badge: null,
      description: t('relation_suggestions')
    },
    {
      name: t('photo_albums'),
      href: '/photo-albums',
      icon: Image,
      badge: null,
      description: t('family_photos')
    },
    {
      name: t('settings'),
      href: '/settings',
      icon: Settings,
      badge: null,
      description: t('app_preferences')
    },
  ];

  // Sidebar est étendu si pas collapsed OU si collapsed mais hovered
  const sidebarExpanded = !sidebarCollapsed || sidebarHovered;
  const sidebarWidth = sidebarExpanded ? 'w-72' : 'w-16';
  const contentMargin = sidebarCollapsed ? (isRTL ? 'lg:pr-16' : 'lg:pl-16') : (isRTL ? 'lg:pr-72' : 'lg:pl-72');

  return (
    <div className={`min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 ${isRTL ? 'rtl' : 'ltr'}`}>
      <Head title={title} />
      
      {/* Sidebar */}
      <div
        className={`fixed inset-y-0 z-50 flex ${sidebarWidth} flex-col ${isRTL ? 'right-0' : 'left-0'} ${
          sidebarOpen ? 'translate-x-0' : isRTL ? 'translate-x-full' : '-translate-x-full'
        } lg:translate-x-0 transition-all duration-300 ease-in-out`}
        onMouseEnter={() => sidebarCollapsed && setSidebarHovered(true)}
        onMouseLeave={() => sidebarCollapsed && setSidebarHovered(false)}
      >
        <div className="flex grow flex-col h-full bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl border-r border-gray-200/50 dark:border-gray-700/50 shadow-2xl">
          {/* Logo */}
          <div className="flex h-16 shrink-0 items-center justify-between px-6 border-b border-gray-200/50 dark:border-gray-700/50">
            <Link href="/dashboard" className="flex items-center group">
              {sidebarExpanded ? (
                <>
                  <div className="w-10 h-10 bg-gradient-to-br from-orange-500 via-red-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-110">
                    <span className="text-white font-bold text-xl group-hover:scale-110 transition-transform duration-300">Y</span>
                  </div>
                  <div className={`${isRTL ? 'mr-3' : 'ml-3'}`}>
                    <h1 className="text-xl font-bold bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent group-hover:from-orange-600 group-hover:to-red-600 transition-all duration-300">
                      Yamsoo
                    </h1>
                    <p className="text-xs text-gray-500 dark:text-gray-400 -mt-1 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors duration-300">
                      {t('family_platform')}
                    </p>
                  </div>
                </>
              ) : (
                <div className="w-10 h-10 bg-gradient-to-br from-orange-500 via-red-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-110">
                  <span className="text-white font-bold text-xl group-hover:scale-110 transition-transform duration-300">Y</span>
                </div>
              )}
            </Link>

            {/* Sidebar Toggle Button - Visible seulement quand étendu */}
            {sidebarExpanded && (
              <Button
                variant="ghost"
                size="sm"
                onClick={toggleSidebar}
                className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                title={sidebarCollapsed ? t('expand_sidebar') : t('collapse_sidebar')}
              >
                {sidebarCollapsed ? (
                  <PanelLeftOpen className="h-4 w-4 text-gray-600 dark:text-gray-400" />
                ) : (
                  <PanelLeftClose className="h-4 w-4 text-gray-600 dark:text-gray-400" />
                )}
              </Button>
            )}
          </div>

          {/* Navigation */}
          <div className="flex flex-1 flex-col px-4 py-2">
            <nav className="flex-1">
              <ul role="list" className="flex flex-col gap-y-2">
              {navigation.map((item) => {
                const isActive = window.location.pathname === item.href;
                return (
                  <li key={item.name}>
                    <Link
                      href={item.href}
                      className={`group relative flex items-center gap-x-3 rounded-xl p-3 text-sm leading-6 font-medium transition-all duration-200 ${
                        isActive
                          ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg transform scale-105'
                          : 'text-gray-700 hover:text-orange-600 hover:bg-orange-50/50 dark:text-gray-300 dark:hover:text-orange-400 dark:hover:bg-orange-900/20'
                      } ${isRTL ? 'flex-row-reverse' : ''} ${!sidebarExpanded ? 'justify-center' : ''}`}
                      title={!sidebarExpanded ? item.name : ''}
                    >
                      <item.icon
                        className={`h-6 w-6 shrink-0 transition-transform duration-200 ${
                          isActive
                            ? 'text-white scale-110'
                            : 'text-gray-400 group-hover:text-orange-600 dark:group-hover:text-orange-400 group-hover:scale-110'
                        }`}
                        aria-hidden="true"
                      />
                      {sidebarExpanded && (
                        <>
                          <div className="flex-1 min-w-0">
                            <div className="truncate">{item.name}</div>
                            <div className={`text-xs opacity-75 truncate ${isActive ? 'text-orange-100' : 'text-gray-500 dark:text-gray-400'}`}>
                              {item.description}
                            </div>
                          </div>
                          {item.badge && (
                            <Badge 
                              variant={isActive ? "secondary" : "outline"} 
                              className={`text-xs ${isActive ? 'bg-white/20 text-white border-white/30' : ''}`}
                            >
                              {item.badge}
                            </Badge>
                          )}
                        </>
                      )}
                      {!sidebarExpanded && item.badge && (
                        <div className="absolute -top-1 -right-1 h-4 w-4 bg-red-500 rounded-full text-xs text-white flex items-center justify-center">
                          {item.badge}
                        </div>
                      )}
                    </Link>
                  </li>
                );
              })}
              </ul>
            </nav>

            {/* Logout Button */}
            <div className="mt-auto pt-4 border-t border-gray-200/50 dark:border-gray-700/50">
            <Link
              href="/logout"
              method="post"
              className={`group flex items-center gap-x-3 rounded-lg p-3 text-sm font-semibold leading-6 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 ${
                isRTL ? 'flex-row-reverse' : ''
              } ${!sidebarExpanded ? 'justify-center' : ''}`}
              title={!sidebarExpanded ? t('logout') : ''}
            >
              <LogOut
                className={`h-6 w-6 shrink-0 transition-transform duration-200 group-hover:scale-110`}
                aria-hidden="true"
              />
              {sidebarExpanded && (
                <span className="transition-colors duration-200">
                  {t('logout')}
                </span>
              )}
            </Link>
            </div>
          </div>
        </div>
      </div>

      {/* Main content */}
      <div className={contentMargin}>
        {/* Top bar */}
        <div className="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl border-b border-gray-200/50 dark:border-gray-700/50 px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
          <button
            type="button"
            className="-m-2.5 p-2.5 text-gray-700 dark:text-gray-300 lg:hidden"
            onClick={() => setSidebarOpen(!sidebarOpen)}
          >
            <span className="sr-only">Open sidebar</span>
            <Menu className="h-6 w-6" aria-hidden="true" />
          </button>

          <div className="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
            {/* Search */}
            <form className="relative flex flex-1 max-w-md" action="#" method="GET">
              <label htmlFor="search-field" className="sr-only">
                {t('search')}
              </label>
              <Search
                className={`pointer-events-none absolute inset-y-0 h-full w-5 text-gray-400 ${isRTL ? 'right-0 pr-3' : 'left-0 pl-3'}`}
                aria-hidden="true"
              />
              <Input
                id="search-field"
                className={`block h-full w-full border-0 bg-gray-50/50 dark:bg-gray-700/50 py-0 text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-2 focus:ring-orange-500 sm:text-sm rounded-lg ${
                  isRTL ? 'pr-10 pl-3' : 'pl-10 pr-3'
                }`}
                placeholder={t('search')}
                type="search"
                name="search"
              />
            </form>

            {/* Spacer pour pousser les éléments à droite */}
            <div className="flex-1"></div>

            <div className={`flex items-center gap-x-3 ${isRTL ? 'flex-row-reverse' : ''}`}>
              {/* Notifications */}
              <Button
                variant="ghost"
                size="sm"
                className="relative text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
              >
                <Bell className="h-5 w-5" />
                <span className="absolute -top-1 -right-1 h-3 w-3 bg-red-500 rounded-full text-xs text-white flex items-center justify-center">
                  3
                </span>
              </Button>

              {/* Dark mode toggle */}
              <Button
                variant="ghost"
                size="sm"
                onClick={toggleDarkMode}
                className="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                title={darkMode ? t('light_mode') : t('dark_mode')}
              >
                {darkMode ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />}
              </Button>

              {/* Notifications */}
              <Link
                href="/notifications"
                className="relative p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors flex items-center justify-center"
                title={t('notifications')}
              >
                <Bell className="h-5 w-5" />
                {/* Badge avec nombre exact de notifications */}
                <span className="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
                  5
                </span>
              </Link>

              {/* Language toggle */}
              <Link
                href={isRTL ? '/language/fr' : '/language/ar'}
                className="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors flex items-center justify-center"
                title={isRTL ? 'Français' : 'العربية'}
              >
                <span className="text-sm font-semibold">
                  {isRTL ? 'FR' : 'عر'}
                </span>
              </Link>

              {/* Separator */}
              <div className="h-6 w-px bg-gray-300 dark:bg-gray-600"></div>

              {/* Profile dropdown */}
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="ghost" className={`flex items-center gap-x-2 text-sm font-semibold leading-6 text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg ${isRTL ? 'flex-row-reverse' : ''}`}>
                    <Avatar className="h-9 w-9 ring-2 ring-orange-500/30 hover:ring-orange-500/50 transition-all duration-200">
                      <AvatarImage src={auth.user.profile?.avatar_url} />
                      <AvatarFallback className="bg-gradient-to-r from-orange-500 to-red-500 text-white font-semibold">
                        {auth.user.name.charAt(0)}
                      </AvatarFallback>
                    </Avatar>
                    <span className="hidden lg:flex lg:items-center">
                      <span className={`${isRTL ? 'ml-2' : 'mr-2'} font-medium`} aria-hidden="true">
                        {auth.user.name}
                      </span>
                      <ChevronDown className="h-4 w-4 text-gray-400" aria-hidden="true" />
                    </span>
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align={isRTL ? "start" : "end"} className="w-56">
                  <DropdownMenuItem asChild>
                    <Link href="/profil" className="flex items-center">
                      <User className={`h-4 w-4 ${isRTL ? 'ml-2' : 'mr-2'}`} />
                      {t('profile')}
                    </Link>
                  </DropdownMenuItem>
                  <DropdownMenuItem asChild>
                    <Link href="/settings" className="flex items-center">
                      <Settings className={`h-4 w-4 ${isRTL ? 'ml-2' : 'mr-2'}`} />
                      {t('settings')}
                    </Link>
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem asChild>
                    <Link href="/logout" method="post" className="flex items-center text-red-600">
                      <LogOut className={`h-4 w-4 ${isRTL ? 'ml-2' : 'mr-2'}`} />
                      {t('logout')}
                    </Link>
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>
          </div>
        </div>

        {/* Page content */}
        <main className="py-8">
          <div className="px-4 sm:px-6 lg:px-8">
            {children}
          </div>
        </main>
      </div>

      {/* Mobile sidebar overlay */}
      {sidebarOpen && (
        <div className="fixed inset-0 z-40 lg:hidden" onClick={() => setSidebarOpen(false)}>
          <div className="fixed inset-0 bg-gray-600/75 backdrop-blur-sm" />
        </div>
      )}
    </div>
  );
};

export default KwdDashboardLayout;
