import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import { KwdDashboardLayout } from '@/layouts/modern';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { DynamicBadge, NotificationBadge, ActionBadge, SidebarBadge } from '@/components/ui/dynamic-badge';
import { 
  Bell, MessageSquare, Users, Camera, Settings, Plus, Minus,
  RefreshCw, CheckCircle, AlertTriangle, Info, Zap
} from 'lucide-react';

export default function TestBadges() {
  const [badges, setBadges] = useState({
    notifications: 5,
    suggestions: 3,
    new_suggestions: 2,
    pending_requests: 1,
    albums: 12,
    unread_messages: 4,
    active_conversations: 8,
    upcoming_events: 2,
    total_badges: 15,
  });

  const updateBadge = (key: keyof typeof badges, increment: boolean) => {
    setBadges(prev => ({
      ...prev,
      [key]: Math.max(0, prev[key] + (increment ? 1 : -1)),
      total_badges: key === 'total_badges' ? prev.total_badges : 
        Math.max(0, prev.total_badges + (increment ? 1 : -1))
    }));
  };

  const resetBadges = () => {
    setBadges({
      notifications: 0,
      suggestions: 0,
      new_suggestions: 0,
      pending_requests: 0,
      albums: 0,
      unread_messages: 0,
      active_conversations: 0,
      upcoming_events: 0,
      total_badges: 0,
    });
  };

  const randomizeBadges = () => {
    const newBadges = {
      notifications: Math.floor(Math.random() * 10),
      suggestions: Math.floor(Math.random() * 8),
      new_suggestions: Math.floor(Math.random() * 5),
      pending_requests: Math.floor(Math.random() * 3),
      albums: Math.floor(Math.random() * 20),
      unread_messages: Math.floor(Math.random() * 15),
      active_conversations: Math.floor(Math.random() * 10),
      upcoming_events: Math.floor(Math.random() * 5),
      total_badges: 0,
    };
    newBadges.total_badges = newBadges.notifications + newBadges.suggestions + 
                             newBadges.pending_requests + newBadges.unread_messages;
    setBadges(newBadges);
  };

  return (
    <KwdDashboardLayout title="Test Badges" badges={badges}>
      <Head title="Test des Badges Dynamiques" />
      
      <div className="space-y-8">
        {/* Header */}
        <div className="bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 rounded-2xl p-6 md:p-8 border border-blue-100 shadow-sm">
          <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
              <h1 className="text-3xl md:text-4xl font-bold text-gray-900 mb-3">
                Test des Badges Dynamiques
              </h1>
              <p className="text-gray-600 max-w-2xl leading-relaxed">
                Testez et visualisez les badges dynamiques dans la sidebar et le top bar. 
                Les badges se mettent à jour en temps réel selon les données.
              </p>
            </div>
            
            <div className="flex flex-col sm:flex-row gap-3">
              <Button onClick={randomizeBadges} className="bg-gradient-to-r from-blue-500 to-purple-500">
                <RefreshCw className="w-4 h-4 mr-2" />
                Randomiser
              </Button>
              <Button onClick={resetBadges} variant="outline">
                <CheckCircle className="w-4 h-4 mr-2" />
                Reset
              </Button>
            </div>
          </div>
        </div>

        {/* Contrôles des badges */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {Object.entries(badges).map(([key, value]) => (
            <Card key={key} className="border-0 shadow-lg">
              <CardHeader className="pb-3">
                <CardTitle className="text-sm font-medium text-gray-600 capitalize">
                  {key.replace(/_/g, ' ')}
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="text-center">
                  <div className="text-3xl font-bold text-gray-900 mb-2">
                    {value}
                  </div>
                  <DynamicBadge
                    count={value}
                    type={value > 5 ? 'urgent' : value > 0 ? 'info' : 'default'}
                    animate={value > 0}
                    showZero={true}
                  />
                </div>
                
                <div className="flex gap-2">
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() => updateBadge(key as keyof typeof badges, false)}
                    className="flex-1"
                  >
                    <Minus className="w-4 h-4" />
                  </Button>
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() => updateBadge(key as keyof typeof badges, true)}
                    className="flex-1"
                  >
                    <Plus className="w-4 h-4" />
                  </Button>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        {/* Exemples de badges */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {/* Types de badges */}
          <Card className="border-0 shadow-lg">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Zap className="w-5 h-5 text-yellow-500" />
                Types de Badges
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
              {/* Badge de notification */}
              <div>
                <h3 className="font-semibold mb-3">Badge de Notification</h3>
                <div className="flex items-center gap-4">
                  <NotificationBadge count={badges.notifications}>
                    <Button variant="outline">
                      <Bell className="w-4 h-4 mr-2" />
                      Notifications
                    </Button>
                  </NotificationBadge>
                  
                  <NotificationBadge count={badges.unread_messages}>
                    <Button variant="outline">
                      <MessageSquare className="w-4 h-4 mr-2" />
                      Messages
                    </Button>
                  </NotificationBadge>
                </div>
              </div>

              {/* Badge d'action */}
              <div>
                <h3 className="font-semibold mb-3">Badge d'Action</h3>
                <div className="flex items-center gap-4">
                  <div className="flex items-center gap-2">
                    <Users className="w-5 h-5 text-blue-500" />
                    <span>Réseau</span>
                    <ActionBadge
                      normalCount={badges.suggestions}
                      urgentCount={badges.pending_requests}
                    />
                  </div>
                  
                  <div className="flex items-center gap-2">
                    <Camera className="w-5 h-5 text-orange-500" />
                    <span>Albums</span>
                    <ActionBadge normalCount={badges.albums} />
                  </div>
                </div>
              </div>

              {/* Badge de sidebar */}
              <div>
                <h3 className="font-semibold mb-3">Badge de Sidebar</h3>
                <div className="space-y-2">
                  <SidebarBadge
                    title="Suggestions"
                    count={badges.suggestions}
                    newCount={badges.new_suggestions}
                  />
                  <SidebarBadge
                    title="Événements"
                    count={badges.upcoming_events}
                  />
                </div>
              </div>
            </CardContent>
          </Card>

          {/* États des badges */}
          <Card className="border-0 shadow-lg">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Settings className="w-5 h-5 text-gray-500" />
                États des Badges
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
              {/* Types visuels */}
              <div>
                <h3 className="font-semibold mb-3">Types Visuels</h3>
                <div className="flex flex-wrap gap-2">
                  <DynamicBadge count={5} type="default">Default</DynamicBadge>
                  <DynamicBadge count={3} type="urgent" animate>Urgent</DynamicBadge>
                  <DynamicBadge count={8} type="success">Success</DynamicBadge>
                  <DynamicBadge count={2} type="warning">Warning</DynamicBadge>
                  <DynamicBadge count={12} type="info">Info</DynamicBadge>
                </div>
              </div>

              {/* Tailles */}
              <div>
                <h3 className="font-semibold mb-3">Tailles</h3>
                <div className="flex items-center gap-3">
                  <DynamicBadge count={5} size="sm" type="info">Small</DynamicBadge>
                  <DynamicBadge count={5} size="md" type="info">Medium</DynamicBadge>
                  <DynamicBadge count={5} size="lg" type="info">Large</DynamicBadge>
                </div>
              </div>

              {/* Animations */}
              <div>
                <h3 className="font-semibold mb-3">Animations</h3>
                <div className="flex flex-wrap gap-2">
                  <DynamicBadge count={3} type="urgent" animate>Pulse</DynamicBadge>
                  <DynamicBadge count={99} type="warning" maxCount={50}>Max Count</DynamicBadge>
                  <DynamicBadge count={0} type="default" showZero>Show Zero</DynamicBadge>
                </div>
              </div>

              {/* État actuel */}
              <div className="p-4 bg-gray-50 rounded-lg">
                <h4 className="font-medium mb-2">État Actuel du Système</h4>
                <div className="grid grid-cols-2 gap-2 text-sm">
                  <div className="flex justify-between">
                    <span>Total badges:</span>
                    <Badge className={badges.total_badges > 0 ? 'bg-red-500' : 'bg-gray-500'}>
                      {badges.total_badges}
                    </Badge>
                  </div>
                  <div className="flex justify-between">
                    <span>Urgents:</span>
                    <Badge className="bg-orange-500">
                      {badges.pending_requests + badges.unread_messages}
                    </Badge>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Instructions */}
        <Card className="border-0 shadow-lg">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Info className="w-5 h-5 text-blue-500" />
              Instructions de Test
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <h3 className="font-semibold mb-3 flex items-center gap-2">
                  <CheckCircle className="w-4 h-4 text-green-500" />
                  1. Sidebar
                </h3>
                <ul className="text-sm text-gray-600 space-y-1">
                  <li>• Regardez les badges dans la navigation</li>
                  <li>• Testez les badges "Réseaux" et "Messages"</li>
                  <li>• Vérifiez les animations</li>
                </ul>
              </div>
              
              <div>
                <h3 className="font-semibold mb-3 flex items-center gap-2">
                  <AlertTriangle className="w-4 h-4 text-orange-500" />
                  2. Top Bar
                </h3>
                <ul className="text-sm text-gray-600 space-y-1">
                  <li>• Badge de notifications en haut à droite</li>
                  <li>• Point rouge qui pulse si notifications</li>
                  <li>• Nombre total affiché</li>
                </ul>
              </div>
              
              <div>
                <h3 className="font-semibold mb-3 flex items-center gap-2">
                  <Zap className="w-4 h-4 text-purple-500" />
                  3. Contrôles
                </h3>
                <ul className="text-sm text-gray-600 space-y-1">
                  <li>• Utilisez +/- pour modifier les valeurs</li>
                  <li>• "Randomiser" pour tester différents états</li>
                  <li>• "Reset" pour remettre à zéro</li>
                </ul>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </KwdDashboardLayout>
  );
}
