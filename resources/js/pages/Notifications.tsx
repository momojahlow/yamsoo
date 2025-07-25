
import { Head, router } from "@inertiajs/react";
import AppLayout from '@/layouts/app-layout';
import { useIsMobile } from "@/hooks/use-mobile";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { useToast } from "@/hooks/use-toast";

interface Notification {
  id: number;
  type: string;
  message: string;
  data: Record<string, any>;
  read_at: string | null;
  created_at: string;
}

interface NotificationsProps {
  notifications: Notification[];
  unreadCount: number;
}

export default function Notifications({ notifications, unreadCount }: NotificationsProps) {
  const isMobile = useIsMobile();
  const { toast } = useToast();

  const handleMarkAsRead = (notificationId: number) => {
    router.patch(`/notifications/${notificationId}/read`, {}, {
      onSuccess: () => {
        toast({
          title: "Notification marquée comme lue",
          description: "La notification a été marquée comme lue.",
        });
      },
      onError: () => {
        toast({
          title: "Erreur",
          description: "Impossible de marquer la notification comme lue.",
          variant: "destructive",
        });
      }
    });
  };

  const handleMarkAllAsRead = () => {
    router.patch('/notifications/read-all', {}, {
      onSuccess: () => {
        toast({
          title: "Toutes les notifications marquées comme lues",
          description: "Toutes vos notifications ont été marquées comme lues.",
        });
      },
      onError: () => {
        toast({
          title: "Erreur",
          description: "Impossible de marquer toutes les notifications comme lues.",
          variant: "destructive",
        });
      }
    });
  };

  return (
    <AppLayout>
      <div className="min-h-screen flex w-full bg-background">
        <main className="flex-1 p-4 md:p-8 md:ml-16 pb-20 md:pb-8">
          <div className="max-w-4xl mx-auto">
            <div className="flex justify-between items-center mb-8">
              <h1 className="text-3xl font-bold">Notifications</h1>
              {unreadCount > 0 && (
                <div className="flex items-center gap-4">
                  <Badge variant="destructive">{unreadCount} non lues</Badge>
                  <Button variant="outline" onClick={handleMarkAllAsRead}>
                    Tout marquer comme lu
                  </Button>
                </div>
              )}
            </div>

            {notifications.length === 0 ? (
              <div className="text-center py-12">
                <p className="text-gray-600">
                  Aucune notification pour le moment.
                </p>
              </div>
            ) : (
              <div className="space-y-3">
                {notifications.map((notification) => {
                  const getNotificationTypeLabel = (type: string) => {
                    switch (type) {
                      case 'relationship_request':
                        return 'Demande de relation';
                      case 'message':
                        return 'Nouveau message';
                      case 'suggestion':
                        return 'Nouvelle suggestion';
                      case 'birthday':
                        return 'Anniversaire';
                      default:
                        return type;
                    }
                  };

                  const cleanNotificationMessage = (message: string) => {
                    // Remove prefixes like "Nouvelle demande de relation :" and "Anniversaire de famille :"
                    return message
                      .replace(/^Nouvelle demande de relation\s*:\s*/i, '')
                      .replace(/^Anniversaire de famille\s*:\s*/i, '')
                      .replace(/^Demande de relation\s*:\s*/i, '')
                      .replace(/birthday/gi, 'anniversaire');
                  };

                  return (
                    <div
                      key={notification.id}
                      className={`flex items-center justify-between p-4 border rounded-lg ${
                        notification.read_at ? 'opacity-75 bg-gray-50' : 'bg-white'
                      }`}
                    >
                      <div className="flex items-center gap-4 flex-1">
                        <div className="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                          <span className="text-blue-600 text-sm font-semibold">
                            {getNotificationTypeLabel(notification.type).charAt(0)}
                          </span>
                        </div>
                        <div className="flex-1">
                          <h3 className="font-semibold text-gray-900">
                            {getNotificationTypeLabel(notification.type)}
                          </h3>
                          <p className="text-sm text-gray-600 mt-1">
                            {cleanNotificationMessage(notification.message)}
                          </p>
                          <p className="text-xs text-gray-500 mt-1">
                            {new Date(notification.created_at).toLocaleDateString('fr-FR', {
                              day: 'numeric',
                              month: 'long',
                              year: 'numeric',
                              hour: '2-digit',
                              minute: '2-digit'
                            })}
                          </p>
                        </div>
                        {!notification.read_at && (
                          <Badge variant="destructive" className="ml-2">Nouveau</Badge>
                        )}
                      </div>
                      {!notification.read_at && (
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleMarkAsRead(notification.id)}
                          className="ml-4"
                        >
                          Marquer comme lu
                        </Button>
                      )}
                    </div>
                  );
                })}
              </div>
            )}
          </div>
        </main>
        {isMobile && <MobileNavBar />}
      </div>
    </AppLayout>
  );
}
