import React from 'react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { UserMenuContent } from '@/components/user-menu-content';
import { useInitials } from '@/hooks/use-initials';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';

/**
 * Composant de démonstration pour le dropdown utilisateur
 * Montre comment le nom de l'utilisateur peut être cliquable avec un dropdown
 */
export function UserDropdownDemo() {
    const { auth } = usePage<SharedData>().props;
    const getInitials = useInitials();

    return (
        <div className="flex items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-900">
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 max-w-md w-full">
                <h2 className="text-xl font-semibold mb-6 text-center text-gray-900 dark:text-white">
                    Dropdown Utilisateur - Démo
                </h2>
                
                {/* Version 1: Nom + Avatar avec dropdown séparés */}
                <div className="mb-8">
                    <h3 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Version 1: Nom et Avatar séparés
                    </h3>
                    <div className="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        {/* Dropdown sur le nom */}
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <div className="flex-1 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 rounded-md px-2 py-1 transition-colors">
                                    <p className="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        {auth.user.name}
                                    </p>
                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                        {auth.user.email}
                                    </p>
                                </div>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent className="w-56" align="start">
                                <UserMenuContent user={auth.user} />
                            </DropdownMenuContent>
                        </DropdownMenu>

                        {/* Dropdown sur l'avatar */}
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost" className="size-10 rounded-full p-1 hover:bg-gray-200 dark:hover:bg-gray-600">
                                    <Avatar className="size-8 overflow-hidden rounded-full">
                                        <AvatarImage src={auth.user.avatar} alt={auth.user.name} />
                                        <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            {getInitials(auth.user.name)}
                                        </AvatarFallback>
                                    </Avatar>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent className="w-56" align="end">
                                <UserMenuContent user={auth.user} />
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>

                {/* Version 2: Nom + Avatar dans un seul dropdown */}
                <div className="mb-8">
                    <h3 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Version 2: Nom et Avatar ensemble
                    </h3>
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <div className="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                <Avatar className="size-10 overflow-hidden rounded-full">
                                    <AvatarImage src={auth.user.avatar} alt={auth.user.name} />
                                    <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                        {getInitials(auth.user.name)}
                                    </AvatarFallback>
                                </Avatar>
                                <div className="flex-1">
                                    <p className="text-sm font-medium text-gray-900 dark:text-white">
                                        {auth.user.name}
                                    </p>
                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                        {auth.user.email}
                                    </p>
                                </div>
                                <ChevronDown className="h-4 w-4 text-gray-400" />
                            </div>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent className="w-56" align="end">
                            <UserMenuContent user={auth.user} />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>

                {/* Version 3: Style bouton avec nom */}
                <div>
                    <h3 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Version 3: Style bouton
                    </h3>
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button 
                                variant="outline" 
                                className="w-full justify-between h-auto p-3 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <div className="flex items-center gap-3">
                                    <Avatar className="size-8 overflow-hidden rounded-full">
                                        <AvatarImage src={auth.user.avatar} alt={auth.user.name} />
                                        <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            {getInitials(auth.user.name)}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div className="text-left">
                                        <p className="text-sm font-medium">
                                            {auth.user.name}
                                        </p>
                                        <p className="text-xs text-gray-500 dark:text-gray-400">
                                            {auth.user.email}
                                        </p>
                                    </div>
                                </div>
                                <ChevronDown className="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent className="w-56" align="end">
                            <UserMenuContent user={auth.user} />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>

                <div className="mt-6 text-center">
                    <p className="text-xs text-gray-500 dark:text-gray-400">
                        Cliquez sur n'importe quel élément pour voir le menu utilisateur
                    </p>
                </div>
            </div>
        </div>
    );
}
