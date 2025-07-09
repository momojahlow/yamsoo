
import { Button } from "@/components/ui/button";
import { CheckIcon, XIcon } from "lucide-react";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import type { Notification } from "@/types/notifications";

interface NotificationItemProps {
  notification: Notification;
  onAction: (relationId: string | undefined, accept: boolean) => Promise<void>;
  loading: boolean;
}

export function NotificationItem({ notification, onAction, loading }: NotificationItemProps) {
  // Determine if we should show action buttons (only for relation type with an originalId)
  const showActions = notification.type === 'relation' && notification.originalId;

  // Extract initials from sender's name
  const firstInitial = notification.sender?.first_name?.[0] || '';
  const lastInitial = notification.sender?.last_name?.[0] || '';
  const initials = `${firstInitial}${lastInitial}`.toUpperCase();

  return (
    <div className="flex items-center justify-between p-4 rounded-lg bg-white shadow-sm border mb-2">
      <div className="flex items-center gap-4">
        <Avatar className="h-12 w-12">
          <AvatarImage src={notification.sender?.avatar_url || ''} />
          <AvatarFallback className="bg-slate-100 text-slate-500">{initials}</AvatarFallback>
        </Avatar>
        <div>
          <p className="text-sm text-gray-500">
            De: {notification.sender?.first_name} {notification.sender?.last_name}
          </p>
          <p className="font-bold">
            {notification.message}
          </p>
        </div>
      </div>

      {showActions && (
        <div className="flex space-x-2">
          <Button
            onClick={() => onAction(notification.originalId, true)}
            disabled={loading}
            size="sm"
            className="bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 border-0"
          >
            <CheckIcon className="h-4 w-4 mr-1" />
            Accepter
          </Button>
          <Button
            onClick={() => onAction(notification.originalId, false)}
            disabled={loading}
            size="sm"
            variant="outline"
            className="bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700"
          >
            <XIcon className="h-4 w-4 mr-1" />
            Refuser
          </Button>
        </div>
      )}
    </div>
  );
}
