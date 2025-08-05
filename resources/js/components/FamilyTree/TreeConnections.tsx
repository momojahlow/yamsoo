import React from 'react';
import { useTranslation } from '@/hooks/useTranslation';

interface Connection {
    from: { x: number; y: number };
    to: { x: number; y: number };
    type: 'parent' | 'child' | 'spouse' | 'sibling';
}

interface TreeConnectionsProps {
    connections: Connection[];
    containerWidth: number;
    containerHeight: number;
}

const TreeConnections: React.FC<TreeConnectionsProps> = ({ connections, containerWidth, containerHeight }) => {
    const { isRTL } = useTranslation();
    const getConnectionStyle = (type: string) => {
        const styles = {
            parent: { stroke: '#3b82f6', strokeWidth: 2, strokeDasharray: 'none' },
            child: { stroke: '#10b981', strokeWidth: 2, strokeDasharray: 'none' },
            spouse: { stroke: '#ef4444', strokeWidth: 3, strokeDasharray: 'none' },
            sibling: { stroke: '#f59e0b', strokeWidth: 2, strokeDasharray: '5,5' },
        };
        return styles[type as keyof typeof styles] || styles.parent;
    };

    const createPath = (from: { x: number; y: number }, to: { x: number; y: number }, type: string) => {
        // Inverser les coordonnées X pour RTL
        const fromX = isRTL ? containerWidth - from.x : from.x;
        const toX = isRTL ? containerWidth - to.x : to.x;
        const fromY = from.y;
        const toY = to.y;

        const midY = fromY + (toY - fromY) / 2;
        
        if (type === 'spouse') {
            // Ligne droite pour les conjoints
            return `M ${fromX} ${fromY} L ${toX} ${toY}`;
        } else if (type === 'sibling') {
            // Ligne courbe pour les frères et sœurs
            return `M ${fromX} ${fromY} Q ${fromX + (toX - fromX) / 2} ${midY - 20} ${toX} ${toY}`;
        } else {
            // Ligne en L pour parent-enfant
            return `M ${fromX} ${fromY} L ${fromX} ${midY} L ${toX} ${midY} L ${toX} ${toY}`;
        }
    };

    return (
        <svg
            className="absolute inset-0 pointer-events-none"
            width={containerWidth}
            height={containerHeight}
            style={{ zIndex: 1 }}
        >
            <defs>
                {/* Marqueurs de flèche */}
                <marker
                    id="arrowhead-parent"
                    markerWidth="10"
                    markerHeight="7"
                    refX="9"
                    refY="3.5"
                    orient="auto"
                >
                    <polygon
                        points="0 0, 10 3.5, 0 7"
                        fill="#3b82f6"
                    />
                </marker>
                <marker
                    id="arrowhead-child"
                    markerWidth="10"
                    markerHeight="7"
                    refX="9"
                    refY="3.5"
                    orient="auto"
                >
                    <polygon
                        points="0 0, 10 3.5, 0 7"
                        fill="#10b981"
                    />
                </marker>
            </defs>
            
            {connections.map((connection, index) => {
                const style = getConnectionStyle(connection.type);
                const path = createPath(connection.from, connection.to, connection.type);
                
                return (
                    <g key={index}>
                        {/* Ligne de connexion */}
                        <path
                            d={path}
                            fill="none"
                            stroke={style.stroke}
                            strokeWidth={style.strokeWidth}
                            strokeDasharray={style.strokeDasharray}
                            markerEnd={connection.type === 'child' ? 'url(#arrowhead-child)' : 
                                      connection.type === 'parent' ? 'url(#arrowhead-parent)' : 'none'}
                            opacity={0.8}
                        />
                        
                        {/* Point de départ */}
                        <circle
                            cx={connection.from.x}
                            cy={connection.from.y}
                            r="3"
                            fill={style.stroke}
                            opacity={0.6}
                        />
                        
                        {/* Point d'arrivée */}
                        <circle
                            cx={connection.to.x}
                            cy={connection.to.y}
                            r="3"
                            fill={style.stroke}
                            opacity={0.6}
                        />
                    </g>
                );
            })}
        </svg>
    );
};

export default TreeConnections;
