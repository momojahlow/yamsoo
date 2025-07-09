
import { useNotificationActions } from "@/hooks/useNotificationActions";
import { NotificationsList } from "./NotificationsList";
import type { Notification } from "@/types/notifications";

interface NotificationsTableProps {
  notifications: Notification[];
  onResponseSuccess: () => void;
}

export function NotificationsTable({ notifications, onResponseSuccess }: NotificationsTableProps) {
  const { handleAction, loading } = useNotificationActions(onResponseSuccess);
  
  // Log incoming notifications for debugging
  console.log("NotificationsTable received notifications:", notifications.length);

  return (
    <NotificationsList 
      notifications={notifications} 
      onAction={handleAction}
      loading={loading}
    />
  );
}
