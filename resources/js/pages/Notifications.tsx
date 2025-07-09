
import { SidebarProvider } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/app/Sidebar";
import { useIsMobile } from "@/hooks/use-mobile";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

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

  const handleMarkAsRead = async (notificationId: number) => {
    try {
      await fetch(`/notifications/${notificationId}/read`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
        },
      });
      window.location.reload();
    } catch (error) {
      console.error('Erreur lors du marquage comme lu:', error);
    }
  };

  const handleMarkAllAsRead = async () => {
    try {
      await fetch('/notifications/read-all', {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
        },
      });
      window.location.reload();
    } catch (error) {
      console.error('Erreur lors du marquage de tous comme lus:', error);
    }
  };

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full bg-background">
        <AppSidebar />
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
              <div className="space-y-4">
                {notifications.map((notification) => (
                  <Card key={notification.id} className={notification.read_at ? 'opacity-75' : ''}>
                    <CardHeader>
                      <CardTitle className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                          <span>{notification.type}</span>
                          {!notification.read_at && (
                            <Badge variant="secondary">Nouveau</Badge>
                          )}
                        </div>
                        <span className="text-sm text-gray-500">
                          {new Date(notification.created_at).toLocaleDateString()}
                        </span>
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <p className="text-gray-700 mb-4">{notification.message}</p>
                      {!notification.read_at && (
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleMarkAsRead(notification.id)}
                        >
                          Marquer comme lu
                        </Button>
                      )}
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </div>
        </main>
        {isMobile && <MobileNavBar />}
      </div>
    </SidebarProvider>
  );
}
