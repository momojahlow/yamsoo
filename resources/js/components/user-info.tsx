// ...existing code...
import { type User } from '@/types';

export function UserInfo({ user, showEmail = false }: { user: User; showEmail?: boolean }) {
    // ...existing code...

    return (
        <div className="grid flex-1 text-left text-sm leading-tight">
            <span className="truncate font-medium">{user.name}</span>
            {showEmail && <span className="truncate text-xs text-muted-foreground">{user.email}</span>}
        </div>
    );
}
