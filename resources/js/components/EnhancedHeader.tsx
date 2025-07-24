import React from 'react';
import { UserHeaderDropdown } from '@/components/UserHeaderDropdown';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { Search, Bell, Settings, Menu } from 'lucide-react';
import { Badge } from '@/components/ui/badge';

/**
 * Header amélioré avec dropdown utilisateur sur le nom
 * Exemple d'implémentation pour montrer l'utilisation du composant UserHeaderDropdown
 */
export function EnhancedHeader() {
    const { auth } = usePage<SharedData>().props;

    return (
        <header className="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 shadow-sm">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex justify-between items-center h-16">
                    {/* Logo et navigation */}
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="icon" className="md:hidden">
                            <Menu className="h-5 w-5" />
                        </Button>

                        <div className="flex items-center gap-2">
                            <div className="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                <span className="text-white font-bold text-sm">Y</span>
                            </div>
                            <span className="font-bold text-xl text-gray-900 dark:text-white hidden sm:block">
                                Yamsoo
                            </span>
                        </div>
                    </div>

                    {/* Barre de recherche */}
                    <div className="flex-1 max-w-md mx-8 hidden md:block">
                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <Input
                                type="search"
                                placeholder="Rechercher des membres de famille..."
                                className="pl-10 pr-4 w-full"
                            />
                        </div>
                    </div>

                    {/* Actions utilisateur */}
                    <div className="flex items-center gap-4">
                        {/* Bouton de recherche mobile */}
                        <Button variant="ghost" size="icon" className="md:hidden">
                            <Search className="h-5 w-5" />
                        </Button>

                        {/* Notifications */}
                        <div className="relative">
                            <Button variant="ghost" size="icon" className="relative">
                                <Bell className="h-5 w-5" />
                                <Badge
                                    variant="destructive"
                                    className="absolute -top-1 -right-1 h-5 w-5 flex items-center justify-center p-0 text-xs"
                                >
                                    3
                                </Badge>
                            </Button>
                        </div>

                        {/* Paramètres */}
                        <Button variant="ghost" size="icon" className="hidden sm:flex">
                            <Settings className="h-5 w-5" />
                        </Button>

                        {/* Dropdown utilisateur avec nom cliquable */}
                        <UserHeaderDropdown
                            user={auth.user}
                            variant="header"
                            showEmail={true}
                            align="end"
                        />
                    </div>
                </div>
            </div>
        </header>
    );
}

/**
 * Header compact pour mobile
 */
export function CompactHeader() {
    const { auth } = usePage<SharedData>().props;

    return (
        <header className="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 shadow-sm">
            <div className="px-4 py-3">
                <div className="flex justify-between items-center">
                    <div className="flex items-center gap-3">
                        <div className="w-6 h-6 bg-gradient-to-r from-blue-500 to-purple-600 rounded flex items-center justify-center">
                            <span className="text-white font-bold text-xs">Y</span>
                        </div>
                        <span className="font-semibold text-lg text-gray-900 dark:text-white">
                            Yamsoo
                        </span>
                    </div>

                    <div className="flex items-center gap-2">
                        <Button variant="ghost" size="icon" className="h-8 w-8">
                            <Bell className="h-4 w-4" />
                        </Button>

                        <UserHeaderDropdown
                            user={auth.user}
                            variant="compact"
                            showEmail={false}
                            align="end"
                        />
                    </div>
                </div>
            </div>
        </header>
    );
}

/**
 * Header avec sidebar toggle
 */
export function SidebarHeader() {
    const { auth } = usePage<SharedData>().props;

    return (
        <header className="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 shadow-sm">
            <div className="px-6 py-4">
                <div className="flex justify-between items-center">
                    <div className="flex items-center gap-4">
                        <h1 className="text-xl font-semibold text-gray-900 dark:text-white">
                            Dashboard
                        </h1>
                    </div>
                    <div className="flex items-center gap-4">
                        <span className="text-sm text-gray-600 dark:text-gray-400">Bienvenue,</span>
                        <UserHeaderDropdown
                            user={auth.user}
                            variant="header"
                            showEmail={false}
                            align="end"
                        />
                    </div>
                </div>
            </div>
        </header>
    );
}
