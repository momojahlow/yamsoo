import React from 'react';
import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
  Activity,
  TrendingUp,
  UserCheck,
  Clock,
  Database
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
  admin: Admin;
}

export default function AdminTest({ admin }: Props) {
  const icons = [
    { name: 'LayoutDashboard', icon: LayoutDashboard },
    { name: 'Users', icon: Users },
    { name: 'MessageSquare', icon: MessageSquare },
    { name: 'Image', icon: Image },
    { name: 'Shield', icon: Shield },
    { name: 'Settings', icon: Settings },
    { name: 'LogOut', icon: LogOut },
    { name: 'Menu', icon: Menu },
    { name: 'X', icon: X },
    { name: 'Bell', icon: Bell },
    { name: 'Search', icon: Search },
    { name: 'Activity', icon: Activity },
    { name: 'TrendingUp', icon: TrendingUp },
    { name: 'UserCheck', icon: UserCheck },
    { name: 'Clock', icon: Clock },
    { name: 'Database', icon: Database },
  ];

  return (
    <AdminLayout title="Test des icônes" admin={admin}>
      <Head title="Test - Administration" />
      
      <div className="p-6">
        <Card>
          <CardHeader>
            <CardTitle>Test des icônes Lucide React</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-4">
              {icons.map(({ name, icon: Icon }) => (
                <div key={name} className="flex flex-col items-center p-4 border rounded-lg">
                  <Icon className="w-8 h-8 mb-2 text-blue-600" />
                  <span className="text-xs text-center">{name}</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        <Card className="mt-6">
          <CardHeader>
            <CardTitle>Informations Admin</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-2">
              <p><strong>Nom :</strong> {admin.name}</p>
              <p><strong>Email :</strong> {admin.email}</p>
              <p><strong>Rôle :</strong> {admin.role_name}</p>
              <p><strong>Permissions :</strong> {admin.permissions?.length || 0}</p>
            </div>
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}
