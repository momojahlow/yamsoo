import React from 'react';

interface TypingIndicatorProps {
    users: Array<{
        id: number;
        name: string;
    }>;
}

export default function TypingIndicator({ users }: TypingIndicatorProps) {
    if (users.length === 0) return null;

    const getTypingText = () => {
        if (users.length === 1) {
            return `${users[0].name} est en train d'écrire...`;
        } else if (users.length === 2) {
            return `${users[0].name} et ${users[1].name} sont en train d'écrire...`;
        } else {
            return `${users.length} personnes sont en train d'écrire...`;
        }
    };

    return (
        <div className="flex items-center px-4 py-2 text-sm text-gray-500">
            <div className="flex space-x-1 mr-2">
                <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '0ms' }}></div>
                <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '150ms' }}></div>
                <div className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style={{ animationDelay: '300ms' }}></div>
            </div>
            <span className="italic">{getTypingText()}</span>
        </div>
    );
}
