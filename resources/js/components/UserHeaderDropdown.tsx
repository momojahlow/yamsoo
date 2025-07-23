import React from 'react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { UserMenuContent } from '@/components/user-menu-content';
import { useInitials } from '@/hooks/use-initials';
import { type User } from '@/types';
import { ChevronDown, User as UserIcon } from 'lucide-react';

interface UserHeaderDropdownProps {
    user: User;
    variant?: 'header' | 'sidebar' | 'compact';
    showEmail?: boolean;
    align?: 'start' | 'center' | 'end';
}

/**
 * Composant dropdown utilisateur réutilisable pour les headers
 * Supporte différentes variantes selon le contexte d'utilisation
 */
export function UserHeaderDropdown({ 
    user, 
    variant = 'header', 
    showEmail = true,
    align = 'end' 
}: UserHeaderDropdownProps) {
    const getInitials = useInitials();

    // Variante header - nom + avatar séparés
    if (variant === 'header') {
        return (
            <div className="flex items-center gap-3">
                {/* Dropdown sur le nom */}
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <div className="hidden md:block text-right cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 rounded-md px-3 py-2 transition-colors group">
                            <div className="flex items-center gap-2">
                                <div>
                                    <p className="text-sm font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                        {user.name}
                                    </p>
                                    {showEmail && (
                                        <p className="text-xs text-gray-500 dark:text-gray-400">
                                            {user.email}
                                        </p>
                                    )}
                                </div>
                                <ChevronDown className="h-3 w-3 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors" />
                            </div>
                        </div>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent className="w-56" align={align}>
                        <UserMenuContent user={user} />
                    </DropdownMenuContent>
                </DropdownMenu>

                {/* Dropdown sur l'avatar */}
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" className="size-10 rounded-full p-1 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <Avatar className="size-8 overflow-hidden rounded-full">
                                <AvatarImage src={user.avatar} alt={user.name} />
                                <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {getInitials(user.name)}
                                </AvatarFallback>
                            </Avatar>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent className="w-56" align={align}>
                        <UserMenuContent user={user} />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        );
    }

    // Variante sidebar - nom + avatar ensemble
    if (variant === 'sidebar') {
        return (
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <div className="flex items-center gap-3 p-3 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group w-full">
                        <Avatar className="size-10 overflow-hidden rounded-full">
                            <AvatarImage src={user.avatar} alt={user.name} />
                            <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                {getInitials(user.name)}
                            </AvatarFallback>
                        </Avatar>
                        <div className="flex-1 text-left">
                            <p className="text-sm font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                {user.name}
                            </p>
                            {showEmail && (
                                <p className="text-xs text-gray-500 dark:text-gray-400">
                                    {user.email}
                                </p>
                            )}
                        </div>
                        <ChevronDown className="h-4 w-4 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors" />
                    </div>
                </DropdownMenuTrigger>
                <DropdownMenuContent className="w-56" align={align}>
                    <UserMenuContent user={user} />
                </DropdownMenuContent>
            </DropdownMenu>
        );
    }

    // Variante compact - style bouton
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button 
                    variant="outline" 
                    className="h-auto p-2 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                >
                    <div className="flex items-center gap-2">
                        <Avatar className="size-6 overflow-hidden rounded-full">
                            <AvatarImage src={user.avatar} alt={user.name} />
                            <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white text-xs">
                                {getInitials(user.name)}
                            </AvatarFallback>
                        </Avatar>
                        <span className="text-sm font-medium hidden sm:inline">
                            {user.name.split(' ')[0]}
                        </span>
                        <ChevronDown className="h-3 w-3" />
                    </div>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent className="w-56" align={align}>
                <UserMenuContent user={user} />
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

/**
 * Composant simple pour juste le nom cliquable avec dropdown
 */
export function UserNameDropdown({ user, className = '' }: { user: User; className?: string }) {
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <button className={`text-left hover:text-blue-600 dark:hover:text-blue-400 transition-colors cursor-pointer ${className}`}>
                    <span className="font-medium">{user.name}</span>
                </button>
            </DropdownMenuTrigger>
            <DropdownMenuContent className="w-56" align="start">
                <UserMenuContent user={user} />
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

/**
 * Composant pour avatar seul avec dropdown
 */
export function UserAvatarDropdown({ user, size = 8 }: { user: User; size?: number }) {
    const getInitials = useInitials();
    
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" className={`size-${size + 2} rounded-full p-1 hover:bg-gray-100 dark:hover:bg-gray-800`}>
                    <Avatar className={`size-${size} overflow-hidden rounded-full`}>
                        <AvatarImage src={user.avatar} alt={user.name} />
                        <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                            {getInitials(user.name)}
                        </AvatarFallback>
                    </Avatar>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent className="w-56" align="end">
                <UserMenuContent user={user} />
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
