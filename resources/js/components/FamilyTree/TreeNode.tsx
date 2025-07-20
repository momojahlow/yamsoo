import React from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface User {
    id: number;
    name: string;
    profile?: {
        first_name?: string;
        last_name?: string;
        gender?: 'male' | 'female';
        birth_date?: string;
    };
}

interface TreeNodeProps {
    person: User;
    relationshipType?: string;
    relationshipCode?: string;
    isCenter?: boolean;
    level?: number;
    onClick?: () => void;
}

const TreeNode: React.FC<TreeNodeProps> = ({
    person,
    relationshipType,
    relationshipCode,
    isCenter = false,
    level = 0,
    onClick
}) => {
    const getGenderIcon = (gender?: string) => {
        return gender === 'female' ? '👩' : '👨';
    };

    const getRelationshipColor = (relationCode?: string) => {
        const colors: Record<string, string> = {
            'father': 'bg-blue-100 text-blue-800 border-blue-200',
            'mother': 'bg-pink-100 text-pink-800 border-pink-200',
            'son': 'bg-green-100 text-green-800 border-green-200',
            'daughter': 'bg-purple-100 text-purple-800 border-purple-200',
            'brother': 'bg-orange-100 text-orange-800 border-orange-200',
            'sister': 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'husband': 'bg-red-100 text-red-800 border-red-200',
            'wife': 'bg-red-100 text-red-800 border-red-200',
            'grandfather_paternal': 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'grandmother_paternal': 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'grandfather_maternal': 'bg-teal-100 text-teal-800 border-teal-200',
            'grandmother_maternal': 'bg-teal-100 text-teal-800 border-teal-200',
            // Relations par alliance (belle-famille)
            'father_in_law': 'bg-blue-50 text-blue-700 border-blue-300 border-dashed',
            'mother_in_law': 'bg-pink-50 text-pink-700 border-pink-300 border-dashed',
            'brother_in_law': 'bg-orange-50 text-orange-700 border-orange-300 border-dashed',
            'sister_in_law': 'bg-yellow-50 text-yellow-700 border-yellow-300 border-dashed',
            'stepson': 'bg-green-50 text-green-700 border-green-300 border-dashed',
            'stepdaughter': 'bg-purple-50 text-purple-700 border-purple-300 border-dashed',
        };
        return colors[relationCode || ''] || 'bg-gray-100 text-gray-800 border-gray-200';
    };

    const getLevelColor = (level: number) => {
        const colors = [
            'border-primary shadow-lg', // Center (level 0)
            'border-blue-300', // Level 1
            'border-green-300', // Level 2
            'border-purple-300', // Level 3
            'border-orange-300', // Level 4
        ];
        return colors[Math.min(level, colors.length - 1)] || 'border-gray-300';
    };

    return (
        <Card
            className={`
                relative transition-all duration-200 hover:shadow-md cursor-pointer
                ${isCenter ? 'border-2 border-primary shadow-lg bg-primary/5' : `border-2 ${getLevelColor(level)}`}
                min-w-[140px] max-w-[180px]
            `}
            onClick={onClick}
        >
            <CardContent className="p-3 text-center">
                {/* Genre Icon */}
                <div className="text-2xl mb-2">
                    {getGenderIcon(person.profile?.gender)}
                </div>

                {/* Nom */}
                <h3 className={`font-semibold text-sm leading-tight mb-2 ${isCenter ? 'text-primary' : 'text-foreground'}`}>
                    {person.name}
                </h3>

                {/* Badge de relation */}
                {relationshipType && !isCenter && (
                    <div className="space-y-1">
                        <Badge
                            className={`text-xs px-2 py-1 ${getRelationshipColor(relationshipCode)}`}
                            variant="outline"
                        >
                            {relationshipType}
                        </Badge>
                        {/* Indicateur pour relations par alliance */}
                        {relationshipCode?.includes('_in_law') || relationshipCode?.startsWith('step') && (
                            <div className="text-xs text-muted-foreground">
                                💍 Belle-famille
                            </div>
                        )}
                    </div>
                )}

                {/* Badge "Vous" pour le centre */}
                {isCenter && (
                    <Badge className="bg-primary text-primary-foreground text-xs px-2 py-1">
                        Vous
                    </Badge>
                )}

                {/* Date de naissance */}
                {person.profile?.birth_date && (
                    <p className="text-xs text-muted-foreground mt-1">
                        {new Date(person.profile.birth_date).getFullYear()}
                    </p>
                )}
            </CardContent>
        </Card>
    );
};

export default TreeNode;
