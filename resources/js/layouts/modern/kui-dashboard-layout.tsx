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
  Globe
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
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

const KuiDashboardLayout: React.FC<Props> = ({ children, title = 'Dashboard' }) => {
  const { t, isRTL } = useTranslation();
  const { auth } = usePage<PageProps>().props;
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [darkMode, setDarkMode] = useState(false);

  useEffect(() => {
    const isDark = localStorage.getItem('darkMode') === 'true';
    setDarkMode(isDark);
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

  const navigation = [
    { name: t('dashboard'), href: '/dashboard', icon: Home, current: false },
    { name: t('my_family'), href: '/famille', icon: Users, current: false },
    { name: t('networks'), href: '/reseaux', icon: Globe, current: false },
    { name: t('settings'), href: '/settings', icon: Settings, current: false },
  ];

  return (
    <div className={`min-h-screen bg-gray-50 dark:bg-gray-900 ${isRTL ? 'rtl' : 'ltr'}`}>
      <Head title={title} />
      
      {/* Sidebar */}
      <div className={`fixed inset-y-0 z-50 flex w-72 flex-col ${isRTL ? 'right-0' : 'left-0'} ${
        sidebarOpen ? 'translate-x-0' : isRTL ? 'translate-x-full' : '-translate-x-full'
      } lg:translate-x-0 transition-transform duration-300 ease-in-out`}>
        <div className="flex grow flex-col gap-y-5 overflow-y-auto bg-white dark:bg-gray-800 px-6 pb-4 shadow-xl">
          <div className="flex h-16 shrink-0 items-center">
            <img
              className="h-8 w-auto"
              src="/images/logo.svg"
              alt="Yamsoo"
            />
            <span className={`${isRTL ? 'mr-3' : 'ml-3'} text-xl font-bold text-gray-900 dark:text-white`}>
              Yamsoo
            </span>
          </div>
          <nav className="flex flex-1 flex-col">
            <ul role="list" className="flex flex-1 flex-col gap-y-7">
              <li>
                <ul role="list" className="-mx-2 space-y-1">
                  {navigation.map((item) => (
                    <li key={item.name}>
                      <Link
                        href={item.href}
                        className={`group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-colors ${
                          item.current
                            ? 'bg-orange-50 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400'
                            : 'text-gray-700 hover:text-orange-600 hover:bg-orange-50 dark:text-gray-300 dark:hover:text-orange-400 dark:hover:bg-orange-900/20'
                        } ${isRTL ? 'flex-row-reverse' : ''}`}
                      >
                        <item.icon
                          className={`h-6 w-6 shrink-0 ${
                            item.current ? 'text-orange-600 dark:text-orange-400' : 'text-gray-400 group-hover:text-orange-600 dark:group-hover:text-orange-400'
                          }`}
                          aria-hidden="true"
                        />
                        {item.name}
                      </Link>
                    </li>
                  ))}
                </ul>
              </li>
            </ul>
          </nav>
        </div>
      </div>

      {/* Main content */}
      <div className={`${isRTL ? 'lg:pr-72' : 'lg:pl-72'}`}>
        {/* Top bar */}
        <div className="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
          <button
            type="button"
            className="-m-2.5 p-2.5 text-gray-700 dark:text-gray-300 lg:hidden"
            onClick={() => setSidebarOpen(!sidebarOpen)}
          >
            <span className="sr-only">Open sidebar</span>
            <Menu className="h-6 w-6" aria-hidden="true" />
          </button>

          {/* Separator */}
          <div className="h-6 w-px bg-gray-200 dark:bg-gray-700 lg:hidden" aria-hidden="true" />

          <div className="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
            <form className="relative flex flex-1" action="#" method="GET">
              <label htmlFor="search-field" className="sr-only">
                {t('search')}
              </label>
              <Search
                className={`pointer-events-none absolute inset-y-0 h-full w-5 text-gray-400 ${isRTL ? 'right-0 pr-3' : 'left-0 pl-3'}`}
                aria-hidden="true"
              />
              <Input
                id="search-field"
                className={`block h-full w-full border-0 py-0 text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-0 sm:text-sm ${
                  isRTL ? 'pr-10 pl-3' : 'pl-10 pr-3'
                }`}
                placeholder={t('search')}
                type="search"
                name="search"
              />
            </form>
            <div className="flex items-center gap-x-4 lg:gap-x-6">
              {/* Dark mode toggle */}
              <Button
                variant="ghost"
                size="sm"
                onClick={toggleDarkMode}
                className="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
              >
                {darkMode ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />}
              </Button>

              {/* Notifications */}
              <Button
                variant="ghost"
                size="sm"
                className="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
              >
                <Bell className="h-6 w-6" />
              </Button>

              {/* Separator */}
              <div className="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200 dark:lg:bg-gray-700" aria-hidden="true" />

              {/* Profile dropdown */}
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="ghost" className="flex items-center gap-x-2 text-sm font-semibold leading-6 text-gray-900 dark:text-white">
                    <Avatar className="h-8 w-8">
                      <AvatarImage src={auth.user.profile?.avatar_url} />
                      <AvatarFallback>{auth.user.name.charAt(0)}</AvatarFallback>
                    </Avatar>
                    <span className="hidden lg:flex lg:items-center">
                      <span className={`${isRTL ? 'ml-2' : 'mr-2'}`} aria-hidden="true">
                        {auth.user.name}
                      </span>
                      <ChevronDown className="h-5 w-5 text-gray-400" aria-hidden="true" />
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
        <main className="py-10">
          <div className="px-4 sm:px-6 lg:px-8">
            {children}
          </div>
        </main>
      </div>

      {/* Mobile sidebar overlay */}
      {sidebarOpen && (
        <div className="fixed inset-0 z-40 lg:hidden" onClick={() => setSidebarOpen(false)}>
          <div className="fixed inset-0 bg-gray-600 bg-opacity-75" />
        </div>
      )}
    </div>
  );
};

export default KuiDashboardLayout;
