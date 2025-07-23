import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { UserDropdownDemo } from '@/components/UserDropdownDemo';
import { UserHeaderDropdown, UserNameDropdown, UserAvatarDropdown } from '@/components/UserHeaderDropdown';
import { EnhancedHeader, CompactHeader, SidebarHeader } from '@/components/EnhancedHeader';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

export default function TestDropdown() {
    const { auth } = usePage<SharedData>().props;

    return (
        <AppLayout>
            <Head title="Test Dropdown Utilisateur" />

            <div className="space-y-8">
                {/* Exemples de headers */}
                <div className="space-y-6">
                    <div className="text-center">
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                            Test des Dropdowns Utilisateur
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400">
                            Différentes variantes de dropdown pour l'utilisateur connecté
                        </p>
                    </div>

                    <div className="space-y-4">
                        <h2 className="text-xl font-semibold text-gray-900 dark:text-white">
                            Exemples de Headers
                        </h2>

                        <div className="space-y-4">
                            <div>
                                <h3 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Header Complet
                                </h3>
                                <EnhancedHeader />
                            </div>

                            <div>
                                <h3 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Header Compact (Mobile)
                                </h3>
                                <CompactHeader />
                            </div>

                            <div>
                                <h3 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Header Sidebar
                                </h3>
                                <SidebarHeader />
                            </div>
                        </div>
                    </div>
                </div>

                <div className="container mx-auto px-4">
                    <div className="max-w-4xl mx-auto space-y-8">

                        {/* Composants réutilisables */}
                        <div className="space-y-4">
                            <h2 className="text-xl font-semibold text-gray-900 dark:text-white">
                                Composants Réutilisables
                            </h2>

                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                                    <h3 className="font-semibold mb-4">Variante Header</h3>
                                    <UserHeaderDropdown user={auth.user} variant="header" />
                                </div>

                                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                                    <h3 className="font-semibold mb-4">Variante Sidebar</h3>
                                    <UserHeaderDropdown user={auth.user} variant="sidebar" />
                                </div>

                                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                                    <h3 className="font-semibold mb-4">Variante Compact</h3>
                                    <UserHeaderDropdown user={auth.user} variant="compact" />
                                </div>

                                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                                    <h3 className="font-semibold mb-4">Nom seul</h3>
                                    <UserNameDropdown user={auth.user} />
                                </div>

                                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                                    <h3 className="font-semibold mb-4">Avatar seul</h3>
                                    <UserAvatarDropdown user={auth.user} />
                                </div>
                            </div>
                        </div>

                        {/* Démo complète */}
                        <UserDropdownDemo />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
