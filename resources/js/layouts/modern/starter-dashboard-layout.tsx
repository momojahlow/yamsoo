import React, { useState } from 'react';
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
  User,
  LogOut,
  Globe,
  Heart,
  TreePine,
  MessageSquare,
  Activity
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';

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

const StarterDashboardLayout: React.FC<Props> = ({ children, title = 'Dashboard' }) => {
  const { t, isRTL } = useTranslation();
  const { auth } = usePage<PageProps>().props;
  const [sidebarOpen, setSidebarOpen] = useState(false);

  const navigation = [
    { name: t('dashboard'), href: '/dashboard', icon: Home, badge: null },
    { name: t('my_family'), href: '/famille', icon: Users, badge: '3' },
    { name: t('family_tree'), href: '/famille/arbre', icon: TreePine, badge: null },
    { name: t('networks'), href: '/reseaux', icon: Globe, badge: '12' },
    { name: t('messages'), href: '/messagerie', icon: MessageSquare, badge: '2' },
    { name: t('suggestions'), href: '/suggestions', icon: Activity, badge: null },
    { name: t('settings'), href: '/settings', icon: Settings, badge: null },
  ];

  return (
    <div className={`min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 ${isRTL ? 'rtl' : 'ltr'}`}>
      <Head title={title} />
      
      {/* Sidebar */}
      <div className={`fixed inset-y-0 z-50 w-64 transform ${isRTL ? 'right-0' : 'left-0'} ${
        sidebarOpen ? 'translate-x-0' : isRTL ? 'translate-x-full' : '-translate-x-full'
      } lg:translate-x-0 transition-transform duration-300 ease-in-out`}>
        <div className="flex h-full flex-col bg-white dark:bg-gray-800 shadow-xl border-r border-gray-200 dark:border-gray-700">
          {/* Logo */}
          <div className="flex h-16 items-center justify-center border-b border-gray-200 dark:border-gray-700">
            <div className="flex items-center">
              <div className="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center">
                <Heart className="w-5 h-5 text-white" />
              </div>
              <span className={`${isRTL ? 'mr-3' : 'ml-3'} text-xl font-bold bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent`}>
                Yamsoo
              </span>
            </div>
          </div>

          {/* Navigation */}
          <nav className="flex-1 px-4 py-6 space-y-2">
            {navigation.map((item) => (
              <Link
                key={item.name}
                href={item.href}
                className={`group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 ${
                  window.location.pathname === item.href
                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg'
                    : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white'
                } ${isRTL ? 'flex-row-reverse' : ''}`}
              >
                <item.icon
                  className={`h-5 w-5 ${isRTL ? 'ml-3' : 'mr-3'} ${
                    window.location.pathname === item.href
                      ? 'text-white'
                      : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300'
                  }`}
                />
                <span className="flex-1">{item.name}</span>
                {item.badge && (
                  <Badge variant="secondary" className="ml-auto text-xs">
                    {item.badge}
                  </Badge>
                )}
              </Link>
            ))}
          </nav>

          {/* User info */}
          <div className="border-t border-gray-200 dark:border-gray-700 p-4">
            <div className="flex items-center">
              <Avatar className="h-10 w-10">
                <AvatarImage src={auth.user.profile?.avatar_url} />
                <AvatarFallback className="bg-gradient-to-r from-orange-500 to-red-500 text-white">
                  {auth.user.name.charAt(0)}
                </AvatarFallback>
              </Avatar>
              <div className={`${isRTL ? 'mr-3' : 'ml-3'} flex-1 min-w-0`}>
                <p className="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {auth.user.name}
                </p>
                <p className="text-xs text-gray-500 dark:text-gray-400 truncate">
                  {auth.user.email}
                </p>
              </div>
              <Link
                href="/logout"
                method="post"
                className="p-2 text-gray-400 hover:text-red-500 transition-colors"
              >
                <LogOut className="h-4 w-4" />
              </Link>
            </div>
          </div>
        </div>
      </div>

      {/* Main content */}
      <div className={`${isRTL ? 'lg:pr-64' : 'lg:pl-64'}`}>
        {/* Top bar */}
        <header className="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
          <div className="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
            <div className="flex items-center">
              <Button
                variant="ghost"
                size="sm"
                className="lg:hidden"
                onClick={() => setSidebarOpen(!sidebarOpen)}
              >
                <Menu className="h-6 w-6" />
              </Button>
              
              <div className={`${isRTL ? 'mr-4' : 'ml-4'} lg:ml-0`}>
                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
                  {title}
                </h1>
              </div>
            </div>

            <div className="flex items-center space-x-4">
              {/* Search */}
              <div className="relative hidden md:block">
                <Search className={`absolute top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400 ${isRTL ? 'right-3' : 'left-3'}`} />
                <Input
                  type="search"
                  placeholder={t('search')}
                  className={`w-64 ${isRTL ? 'pr-10' : 'pl-10'} bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600`}
                />
              </div>

              {/* Notifications */}
              <Button variant="ghost" size="sm" className="relative">
                <Bell className="h-5 w-5" />
                <span className="absolute -top-1 -right-1 h-4 w-4 bg-red-500 rounded-full text-xs text-white flex items-center justify-center">
                  3
                </span>
              </Button>

              {/* Profile */}
              <Link href="/profil">
                <Avatar className="h-8 w-8 cursor-pointer ring-2 ring-transparent hover:ring-orange-500 transition-all">
                  <AvatarImage src={auth.user.profile?.avatar_url} />
                  <AvatarFallback className="bg-gradient-to-r from-orange-500 to-red-500 text-white">
                    {auth.user.name.charAt(0)}
                  </AvatarFallback>
                </Avatar>
              </Link>
            </div>
          </div>
        </header>

        {/* Page content */}
        <main className="flex-1">
          <div className="p-6 lg:p-8">
            {children}
          </div>
        </main>
      </div>

      {/* Mobile sidebar overlay */}
      {sidebarOpen && (
        <div 
          className="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}
    </div>
  );
};

export default StarterDashboardLayout;
