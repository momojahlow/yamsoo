
import { Head, router } from "@inertiajs/react";
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
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

interface User {
  id: number;
  name: string;
  email: string;
}

interface NotificationsProps {
  notifications: Notification[];
  unreadCount: number;
  auth?: {
    user: User | null;
  };
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
    <AppSidebarLayout>
      <Head title="Notifications" />

      <div className="min-h-screen bg-gradient-to-br from-orange-50 via-white to-red-50">
        <div className="w-full max-w-6xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-6 md:py-8 lg:py-12">
          {/* Header responsive */}
          <div className="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 sm:gap-6 mb-6 sm:mb-8 md:mb-12">
            <div className="text-center sm:text-left">
              <h1 className="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent leading-tight">
                Notifications
              </h1>
              <p className="text-gray-600 mt-2 sm:mt-3 text-xs sm:text-sm md:text-base">
                Restez informé des dernières activités
              </p>
            </div>

            {unreadCount > 0 && (
              <div className="flex flex-col sm:flex-row items-center gap-2 sm:gap-4">
                <Badge variant="destructive" className="text-xs px-3 py-1">
                  {unreadCount} non lue{unreadCount > 1 ? 's' : ''}
                </Badge>
                <Button
                  variant="outline"
                  onClick={handleMarkAllAsRead}
                  className="w-full sm:w-auto h-9 sm:h-10 border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-200 text-sm"
                >
                  <span className="hidden sm:inline">Tout marquer comme lu</span>
                  <span className="sm:hidden">Marquer tout</span>
                </Button>
              </div>
            )}
          </div>

          {notifications.length === 0 ? (
            <div className="flex items-center justify-center min-h-[60vh]">
              <div className="text-center max-w-md mx-auto">
                <div className="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-orange-500 to-red-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                  <svg className="w-8 h-8 sm:w-10 sm:h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-5 5-5-5h5v-12" />
                  </svg>
                </div>
                <h3 className="text-lg sm:text-xl md:text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent mb-4">
                  Aucune notification
                </h3>
                <p className="text-gray-600 text-sm sm:text-base leading-relaxed">
                  Vous n'avez aucune notification pour le moment. Nous vous tiendrons informé des nouvelles activités.
                </p>
              </div>
            </div>
          ) : (
            <div className="space-y-2 sm:space-y-3">
                {notifications.map((notification) => {
                  const getNotificationTypeLabel = (type: string) => {
                    switch (type) {
                      case 'relationship_request':
                        return 'Demande de relation';
                      case 'relationship_accepted':
                        return 'Relation acceptée';
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
                      className={`group relative overflow-hidden rounded-xl border-0 shadow-sm transition-all duration-300 hover:shadow-lg ${
                        notification.read_at
                          ? 'bg-gradient-to-br from-gray-50 to-gray-100/50 opacity-75'
                          : 'bg-gradient-to-br from-white to-orange-50/30'
                      }`}
                    >
                      <div className="flex items-start gap-3 sm:gap-4 p-3 sm:p-4 md:p-6">
                        {/* Icon moderne avec gradient */}
                        <div className={`w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center flex-shrink-0 ${
                          notification.read_at
                            ? 'bg-gradient-to-br from-gray-400 to-gray-500'
                            : 'bg-gradient-to-br from-orange-500 to-red-500'
                        } shadow-lg`}>
                          <span className="text-white text-xs sm:text-sm md:text-base font-semibold">
                            {getNotificationTypeLabel(notification.type).charAt(0)}
                          </span>
                        </div>

                        <div className="flex-1 min-w-0">
                          <div className="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                            <div className="flex-1 min-w-0">
                              <h3 className="font-semibold text-gray-900 text-sm sm:text-base truncate">
                                {getNotificationTypeLabel(notification.type)}
                              </h3>
                              <p className="text-xs sm:text-sm text-gray-600 mt-1 leading-relaxed line-clamp-2">
                                {cleanNotificationMessage(notification.message)}
                              </p>
                              <p className="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span className="hidden sm:inline">
                                  {new Date(notification.created_at).toLocaleDateString('fr-FR', {
                                    day: 'numeric',
                                    month: 'long',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                  })}
                                </span>
                                <span className="sm:hidden">
                                  {new Date(notification.created_at).toLocaleDateString('fr-FR', {
                                    day: '2-digit',
                                    month: '2-digit',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                  })}
                                </span>
                              </p>
                            </div>

                            {/* Badge et bouton responsive */}
                            <div className="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                              {!notification.read_at && (
                                <>
                                  <Badge variant="destructive" className="text-xs px-2 py-1">
                                    <span className="hidden sm:inline">Nouveau</span>
                                    <span className="sm:hidden">•</span>
                                  </Badge>
                                  <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => handleMarkAsRead(notification.id)}
                                    className="h-7 sm:h-8 px-2 sm:px-3 text-xs border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-200"
                                  >
                                    <span className="hidden sm:inline">Marquer comme lu</span>
                                    <span className="sm:hidden">✓</span>
                                  </Button>
                                </>
                              )}
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
        </div>
      </div>
    </AppSidebarLayout>
  );
}
