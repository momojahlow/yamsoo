import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { type User } from '@/types';
import { Link } from '@inertiajs/react';
import { LogOut, Settings, User as UserIcon } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { route } from 'ziggy-js';

interface UserMenuContentProps {
    user: User;
}

export function UserMenuContent({ user }: UserMenuContentProps) {
    // For demonstration, use user.unreadNotifications or fallback to 0
    const unreadCount = user.unreadNotifications ?? 0;
    const cleanup = useMobileNavigation();

    const handleLogout = () => {
        cleanup();
        // La déconnexion sera gérée par le Link avec method="post"
        // Pas besoin de faire autre chose ici
    };

    return (
        <>
            <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    <UserInfo user={user} showEmail={true} />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                {/* Notifications menu item with badge */}
                <DropdownMenuItem asChild>
                    <Link className="block w-full cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center justify-between" href={route('notifications')} as="button" prefetch onClick={cleanup}>
                        <span className="flex items-center">
                            <svg className="mr-2 h-4 w-4" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                            Notifications
                        </span>
                        {unreadCount > 0 && (
                            <Badge variant="destructive">{unreadCount}</Badge>
                        )}
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <Link className="block w-full cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800" href={route('profiles.edit', user.id)} as="button" prefetch onClick={cleanup}>
                        <UserIcon className="mr-2 h-4 w-4" />
                        Mon Profil
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <Link className="block w-full cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800" href={route('profiles.edit', user.id)} as="button" prefetch onClick={cleanup}>
                        <Settings className="mr-2 h-4 w-4" />
                        Paramètres
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem asChild>
                <Link className="block w-full cursor-pointer hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 hover:text-red-700" method="post" href={route('logout')} as="button" onClick={handleLogout}>
                    <LogOut className="mr-2 h-4 w-4" />
                    Déconnexion
                </Link>
            </DropdownMenuItem>
        </>
    );
}
