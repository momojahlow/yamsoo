import React, { useState } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
  LayoutDashboard,
  Users,
  MessageSquare,
  Image,
  Shield,
  Settings,
  LogOut,
  Menu,
  X,
  Bell,
  Search,
  ChevronDown
} from 'lucide-react';

interface Admin {
  id: number;
  name: string;
  email: string;
  role: string;
  role_name: string;
  permissions: string[];
}

interface Props {
  children: React.ReactNode;
  title?: string;
  admin?: Admin;
}

export default function AdminLayout({ children, title = 'Administration', admin }: Props) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const { url } = usePage();

  const navigation = [
    {
      name: 'Tableau de bord',
      href: '/admin',
      icon: LayoutDashboard,
      current: url === '/admin',
    },
    {
      name: 'Utilisateurs',
      href: '/admin/users',
      icon: Users,
      current: url.startsWith('/admin/users'),
      permission: 'users.view',
    },
    {
      name: 'Messages',
      href: '/admin/moderation/messages',
      icon: MessageSquare,
      current: url.startsWith('/admin/moderation/messages'),
      permission: 'messages.view',
    },
    {
      name: 'Photos',
      href: '/admin/moderation/photos',
      icon: Image,
      current: url.startsWith('/admin/moderation/photos'),
      permission: 'photos.view',
    },
    {
      name: 'Système',
      href: '/admin/system',
      icon: Settings,
      current: url.startsWith('/admin/system'),
      permission: 'system.settings',
    },
    {
      name: 'Administrateurs',
      href: '/admin/admins',
      icon: Shield,
      current: url.startsWith('/admin/admins'),
      permission: 'admins.manage',
    },
  ];

  const hasPermission = (permission?: string) => {
    if (!permission || !admin) return true;
    return admin.permissions?.includes(permission) || admin.role === 'super_admin';
  };

  const handleLogout = () => {
    if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
      window.location.href = '/admin/logout';
    }
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Head title={title} />
      
      {/* Sidebar mobile */}
      <div className={`fixed inset-0 z-50 lg:hidden ${sidebarOpen ? 'block' : 'hidden'}`}>
        <div className="fixed inset-0 bg-gray-600 bg-opacity-75" onClick={() => setSidebarOpen(false)} />
        <div className="fixed inset-y-0 left-0 flex w-64 flex-col bg-white shadow-xl">
          <div className="flex h-16 items-center justify-between px-4 border-b">
            <h1 className="text-xl font-bold text-gray-900">Admin Yamsoo</h1>
            <Button variant="ghost" size="sm" onClick={() => setSidebarOpen(false)}>
              <X className="h-5 w-5" />
            </Button>
          </div>
          <nav className="flex-1 space-y-1 px-2 py-4">
            {navigation.map((item) => {
              if (!hasPermission(item.permission)) return null;
              
              return (
                <Link
                  key={item.name}
                  href={item.href}
                  className={`group flex items-center px-2 py-2 text-sm font-medium rounded-md ${
                    item.current
                      ? 'bg-blue-100 text-blue-900'
                      : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                  }`}
                >
                  <item.icon className="mr-3 h-5 w-5" />
                  {item.name}
                </Link>
              );
            })}
          </nav>
        </div>
      </div>

      {/* Sidebar desktop */}
      <div className="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
        <div className="flex flex-col flex-grow bg-white border-r border-gray-200 shadow-sm">
          <div className="flex h-16 items-center px-4 border-b">
            <h1 className="text-xl font-bold text-gray-900">Admin Yamsoo</h1>
          </div>
          <nav className="flex-1 space-y-1 px-2 py-4">
            {navigation.map((item) => {
              if (!hasPermission(item.permission)) return null;
              
              return (
                <Link
                  key={item.name}
                  href={item.href}
                  className={`group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors ${
                    item.current
                      ? 'bg-blue-100 text-blue-900'
                      : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                  }`}
                >
                  <item.icon className="mr-3 h-5 w-5" />
                  {item.name}
                </Link>
              );
            })}
          </nav>
          
          {/* Profil admin en bas */}
          {admin && (
            <div className="border-t border-gray-200 p-4">
              <div className="flex items-center">
                <Avatar className="h-8 w-8">
                  <AvatarFallback className="bg-blue-500 text-white">
                    {admin.name.charAt(0).toUpperCase()}
                  </AvatarFallback>
                </Avatar>
                <div className="ml-3 flex-1">
                  <p className="text-sm font-medium text-gray-900">{admin.name}</p>
                  <Badge variant="outline" className="text-xs">
                    {admin.role_name}
                  </Badge>
                </div>
                <Button variant="ghost" size="sm" onClick={handleLogout}>
                  <LogOut className="h-4 w-4" />
                </Button>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Contenu principal */}
      <div className="lg:pl-64">
        {/* Header mobile */}
        <div className="sticky top-0 z-40 flex h-16 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm lg:hidden">
          <Button variant="ghost" size="sm" onClick={() => setSidebarOpen(true)}>
            <Menu className="h-5 w-5" />
          </Button>
          <div className="flex flex-1 items-center justify-between">
            <h1 className="text-lg font-semibold text-gray-900">{title}</h1>
            {admin && (
              <div className="flex items-center gap-2">
                <Badge variant="outline">{admin.role_name}</Badge>
                <Button variant="ghost" size="sm" onClick={handleLogout}>
                  <LogOut className="h-4 w-4" />
                </Button>
              </div>
            )}
          </div>
        </div>

        {/* Header desktop */}
        <div className="hidden lg:flex lg:h-16 lg:items-center lg:justify-between lg:border-b lg:border-gray-200 lg:bg-white lg:px-6 lg:shadow-sm">
          <h1 className="text-xl font-semibold text-gray-900">{title}</h1>
          <div className="flex items-center gap-4">
            <Button variant="ghost" size="sm">
              <Bell className="h-4 w-4" />
            </Button>
            <Button variant="ghost" size="sm">
              <Search className="h-4 w-4" />
            </Button>
            {admin && (
              <div className="flex items-center gap-2">
                <span className="text-sm text-gray-600">Connecté en tant que</span>
                <Badge variant="outline">{admin.role_name}</Badge>
              </div>
            )}
          </div>
        </div>

        {/* Contenu */}
        <main className="flex-1">
          {children}
        </main>
      </div>
    </div>
  );
}
