import React from 'react';

interface OnlineStatusProps {
    isOnline: boolean;
    lastSeen?: string;
    size?: 'sm' | 'md' | 'lg';
    showText?: boolean;
}

export default function OnlineStatus({ 
    isOnline, 
    lastSeen, 
    size = 'md', 
    showText = false 
}: OnlineStatusProps) {
    const sizeClasses = {
        sm: 'w-2 h-2',
        md: 'w-3 h-3',
        lg: 'w-4 h-4'
    };

    const formatLastSeen = (dateString: string) => {
        const date = new Date(dateString);
        const now = new Date();
        const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60));

        if (diffInMinutes < 1) {
            return 'À l\'instant';
        } else if (diffInMinutes < 60) {
            return `Il y a ${diffInMinutes} min`;
        } else if (diffInMinutes < 1440) {
            const hours = Math.floor(diffInMinutes / 60);
            return `Il y a ${hours}h`;
        } else {
            const days = Math.floor(diffInMinutes / 1440);
            return `Il y a ${days}j`;
        }
    };

    if (showText) {
        return (
            <div className="flex items-center space-x-2">
                <div className={`
                    ${sizeClasses[size]} rounded-full border-2 border-white
                    ${isOnline ? 'bg-green-500' : 'bg-gray-400'}
                `} />
                <span className={`text-sm ${isOnline ? 'text-green-600' : 'text-gray-500'}`}>
                    {isOnline ? 'En ligne' : lastSeen ? formatLastSeen(lastSeen) : 'Hors ligne'}
                </span>
            </div>
        );
    }

    return (
        <div 
            className={`
                ${sizeClasses[size]} rounded-full border-2 border-white
                ${isOnline ? 'bg-green-500' : 'bg-gray-400'}
            `}
            title={isOnline ? 'En ligne' : lastSeen ? formatLastSeen(lastSeen) : 'Hors ligne'}
        />
    );
}
