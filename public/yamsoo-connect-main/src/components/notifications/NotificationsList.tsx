
import { NotificationItem } from "./NotificationItem";
import type { Notification } from "@/types/notifications";

interface NotificationsListProps {
  notifications: Notification[];
  onAction: (relationId: string | undefined, accept: boolean) => Promise<void>;
  loading: boolean;
}

export function NotificationsList({ notifications, onAction, loading }: NotificationsListProps) {
  if (loading) {
    return <div className="text-center py-8">Chargement des demandes...</div>;
  }
  
  if (notifications.length === 0) {
    return <div className="text-center py-8">Aucune demande de relation en attente</div>;
  }

  // Log notifications to help debugging
  console.log("Notifications to display:", notifications);

  return (
    <div className="space-y-2 px-4">
      {notifications.map((notification) => (
        <NotificationItem
          key={notification.id}
          notification={notification}
          onAction={onAction}
          loading={loading}
        />
      ))}
    </div>
  );
}
