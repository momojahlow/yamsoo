import React, { useState, useEffect } from 'react';
import { BarChart3, MessageCircle, Users, Clock, TrendingUp, Calendar } from 'lucide-react';
import axios from 'axios';

interface MessageStatsProps {
    isOpen: boolean;
    onClose: () => void;
}

interface StatsData {
    totalMessages: number;
    totalConversations: number;
    activeUsers: number;
    averageResponseTime: number;
    messagesThisWeek: number;
    messagesThisMonth: number;
    topContacts: Array<{
        name: string;
        messageCount: number;
        avatar?: string;
    }>;
    dailyActivity: Array<{
        date: string;
        count: number;
    }>;
}

export default function MessageStats({ isOpen, onClose }: MessageStatsProps) {
    const [stats, setStats] = useState<StatsData | null>(null);
    const [loading, setLoading] = useState(false);
    const [timeRange, setTimeRange] = useState<'week' | 'month' | 'year'>('month');

    useEffect(() => {
        if (isOpen) {
            loadStats();
        }
    }, [isOpen, timeRange]);

    const loadStats = async () => {
        setLoading(true);
        try {
            const response = await axios.get('/api/messages/stats', {
                params: { range: timeRange }
            });
            setStats(response.data);
        } catch (error) {
            console.error('Erreur lors du chargement des statistiques:', error);
        } finally {
            setLoading(false);
        }
    };

    const formatResponseTime = (minutes: number) => {
        if (minutes < 60) {
            return `${Math.round(minutes)} min`;
        } else if (minutes < 1440) {
            return `${Math.round(minutes / 60)} h`;
        } else {
            return `${Math.round(minutes / 1440)} j`;
        }
    };

    const getInitials = (name: string) => {
        return name
            .split(' ')
            .map(word => word.charAt(0))
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
                {/* Header */}
                <div className="flex items-center justify-between p-6 border-b border-gray-200">
                    <h2 className="text-lg font-semibold text-gray-900 flex items-center">
                        <BarChart3 className="w-5 h-5 mr-2 text-orange-500" />
                        Statistiques de messagerie
                    </h2>
                    <div className="flex items-center space-x-3">
                        <select
                            value={timeRange}
                            onChange={(e) => setTimeRange(e.target.value as 'week' | 'month' | 'year')}
                            className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        >
                            <option value="week">Cette semaine</option>
                            <option value="month">Ce mois</option>
                            <option value="year">Cette année</option>
                        </select>
                        <button
                            onClick={onClose}
                            className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            ×
                        </button>
                    </div>
                </div>

                {/* Contenu */}
                <div className="overflow-y-auto max-h-[calc(90vh-140px)]">
                    {loading ? (
                        <div className="flex justify-center items-center p-12">
                            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500"></div>
                        </div>
                    ) : stats ? (
                        <div className="p-6 space-y-6">
                            {/* Métriques principales */}
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                <div className="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-lg">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm font-medium text-blue-600">Messages totaux</p>
                                            <p className="text-2xl font-bold text-blue-900">{stats.totalMessages}</p>
                                        </div>
                                        <MessageCircle className="w-8 h-8 text-blue-500" />
                                    </div>
                                </div>

                                <div className="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-lg">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm font-medium text-green-600">Conversations</p>
                                            <p className="text-2xl font-bold text-green-900">{stats.totalConversations}</p>
                                        </div>
                                        <Users className="w-8 h-8 text-green-500" />
                                    </div>
                                </div>

                                <div className="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-lg">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm font-medium text-purple-600">Utilisateurs actifs</p>
                                            <p className="text-2xl font-bold text-purple-900">{stats.activeUsers}</p>
                                        </div>
                                        <TrendingUp className="w-8 h-8 text-purple-500" />
                                    </div>
                                </div>

                                <div className="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-lg">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm font-medium text-orange-600">Temps de réponse</p>
                                            <p className="text-2xl font-bold text-orange-900">
                                                {formatResponseTime(stats.averageResponseTime)}
                                            </p>
                                        </div>
                                        <Clock className="w-8 h-8 text-orange-500" />
                                    </div>
                                </div>
                            </div>

                            {/* Activité récente */}
                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div className="bg-white border border-gray-200 rounded-lg p-6">
                                    <h3 className="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                        <Calendar className="w-5 h-5 mr-2 text-blue-500" />
                                        Activité récente
                                    </h3>
                                    
                                    <div className="space-y-3">
                                        <div className="flex items-center justify-between">
                                            <span className="text-sm text-gray-600">Cette semaine</span>
                                            <span className="font-medium text-gray-900">{stats.messagesThisWeek} messages</span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-sm text-gray-600">Ce mois</span>
                                            <span className="font-medium text-gray-900">{stats.messagesThisMonth} messages</span>
                                        </div>
                                        
                                        {/* Graphique simple d'activité */}
                                        <div className="mt-4">
                                            <p className="text-sm text-gray-600 mb-2">Activité quotidienne</p>
                                            <div className="flex items-end space-x-1 h-20">
                                                {stats.dailyActivity.map((day, index) => {
                                                    const maxCount = Math.max(...stats.dailyActivity.map(d => d.count));
                                                    const height = maxCount > 0 ? (day.count / maxCount) * 100 : 0;
                                                    
                                                    return (
                                                        <div
                                                            key={index}
                                                            className="flex-1 bg-gradient-to-t from-orange-500 to-orange-300 rounded-t"
                                                            style={{ height: `${height}%` }}
                                                            title={`${day.date}: ${day.count} messages`}
                                                        />
                                                    );
                                                })}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Top contacts */}
                                <div className="bg-white border border-gray-200 rounded-lg p-6">
                                    <h3 className="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                        <Users className="w-5 h-5 mr-2 text-green-500" />
                                        Contacts les plus actifs
                                    </h3>
                                    
                                    <div className="space-y-3">
                                        {stats.topContacts.map((contact, index) => (
                                            <div key={index} className="flex items-center justify-between">
                                                <div className="flex items-center space-x-3">
                                                    <div className="flex-shrink-0">
                                                        {contact.avatar ? (
                                                            <img
                                                                src={contact.avatar}
                                                                alt={contact.name}
                                                                className="w-8 h-8 rounded-full object-cover"
                                                            />
                                                        ) : (
                                                            <div className="w-8 h-8 rounded-full bg-gradient-to-br from-orange-100 to-red-100 flex items-center justify-center text-orange-600 font-medium text-xs">
                                                                {getInitials(contact.name)}
                                                            </div>
                                                        )}
                                                    </div>
                                                    <span className="text-sm font-medium text-gray-900">
                                                        {contact.name}
                                                    </span>
                                                </div>
                                                <span className="text-sm text-gray-600">
                                                    {contact.messageCount} messages
                                                </span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        </div>
                    ) : (
                        <div className="p-12 text-center text-gray-500">
                            <BarChart3 className="w-12 h-12 text-gray-300 mx-auto mb-4" />
                            <p>Aucune donnée disponible</p>
                        </div>
                    )}
                </div>

                {/* Footer */}
                <div className="p-6 border-t border-gray-200 bg-gray-50">
                    <p className="text-xs text-gray-500 text-center">
                        Les statistiques sont mises à jour en temps réel et reflètent votre activité de messagerie.
                    </p>
                </div>
            </div>
        </div>
    );
}
