import React, { useState, useRef, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import AppSidebarLayout from '@/Layouts/app/app-sidebar-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Users, Heart, Baby, Crown, TreePine, ArrowLeft, ZoomIn, ZoomOut, RotateCcw } from 'lucide-react';
import { Link } from '@inertiajs/react';
import TreeNode from '@/components/FamilyTree/TreeNode';
import TreeConnections from '@/components/FamilyTree/TreeConnections';
import { getRelationLabel } from '@/utils/familyUtils';

interface User {
    id: number;
    name: string;
    profile?: {
        first_name?: string;
        last_name?: string;
        gender?: 'male' | 'female';
        birth_date?: string;
    };
    relationship_type?: string;
    relationship_code?: string;
}

interface Relationship {
    id: number;
    type: string;
    type_code: string;
    related_user: User;
    created_automatically: boolean;
    created_at: string;
}

interface TreeData {
    center: User & { isCurrentUser: boolean };
    parents: User[];
    spouse: User | null;
    children: User[];
    siblings: User[];
    grandparents: {
        paternal: User[];
        maternal: User[];
    };
    uncles_aunts: {
        paternal: User[];
        maternal: User[];
    };
    grandchildren: User[];
    cousins: User[];
}

interface Statistics {
    total_relatives: number;
    by_type: Record<string, number>;
}

interface Props {
    user: User;
    treeData: TreeData;
    relationships: Relationship[];
    statistics: Statistics;
}

const FamilyTreeIndex: React.FC<Props> = ({ user, treeData, relationships, statistics }) => {
    const [zoom, setZoom] = useState(1);
    const [pan, setPan] = useState({ x: 0, y: 0 });
    const [isDragging, setIsDragging] = useState(false);
    const [dragStart, setDragStart] = useState({ x: 0, y: 0 });
    const containerRef = useRef<HTMLDivElement>(null);

    // Configuration de l'arbre
    const nodeWidth = 160;
    const nodeHeight = 120;
    const levelHeight = 180;
    const siblingSpacing = 200;

    // Calculer les positions des nœuds
    const calculateNodePositions = () => {
        const positions: Array<{
            person: User;
            x: number;
            y: number;
            level: number;
            relationshipType?: string;
            relationshipCode?: string;
            isCenter?: boolean;
        }> = [];

        const centerX = 800; // Centre horizontal
        const centerY = 400; // Centre vertical

        // Utilisateur central
        positions.push({
            person: treeData.center,
            x: centerX,
            y: centerY,
            level: 0,
            isCenter: true,
        });

        // Parents (niveau -1)
        if (treeData.parents.length > 0) {
            const parentY = centerY - levelHeight;
            treeData.parents.forEach((parent, index) => {
                const parentX = centerX + (index - (treeData.parents.length - 1) / 2) * siblingSpacing;
                positions.push({
                    person: parent,
                    x: parentX,
                    y: parentY,
                    level: -1,
                    relationshipType: getRelationLabel(parent.relationship_code || ''),
                    relationshipCode: parent.relationship_code,
                });
            });
        }

        // Conjoint (même niveau, à droite)
        if (treeData.spouse) {
            positions.push({
                person: treeData.spouse,
                x: centerX + siblingSpacing,
                y: centerY,
                level: 0,
                relationshipType: getRelationLabel(treeData.spouse.relationship_code || ''),
                relationshipCode: treeData.spouse.relationship_code,
            });
        }

        // Frères et sœurs (même niveau, à gauche)
        if (treeData.siblings.length > 0) {
            treeData.siblings.forEach((sibling, index) => {
                const siblingX = centerX - siblingSpacing * (index + 1);
                positions.push({
                    person: sibling,
                    x: siblingX,
                    y: centerY,
                    level: 0,
                    relationshipType: getRelationLabel(sibling.relationship_code || ''),
                    relationshipCode: sibling.relationship_code,
                });
            });
        }

        // Enfants (niveau +1)
        if (treeData.children.length > 0) {
            const childY = centerY + levelHeight;
            treeData.children.forEach((child, index) => {
                const childX = centerX + (index - (treeData.children.length - 1) / 2) * siblingSpacing;
                positions.push({
                    person: child,
                    x: childX,
                    y: childY,
                    level: 1,
                    relationshipType: getRelationLabel(child.relationship_code || ''),
                    relationshipCode: child.relationship_code,
                });
            });
        }

        // Grands-parents (niveau -2)
        const allGrandparents = [...treeData.grandparents.paternal, ...treeData.grandparents.maternal];
        if (allGrandparents.length > 0) {
            const grandparentY = centerY - levelHeight * 2;
            allGrandparents.forEach((grandparent, index) => {
                const grandparentX = centerX + (index - (allGrandparents.length - 1) / 2) * siblingSpacing;
                positions.push({
                    person: grandparent,
                    x: grandparentX,
                    y: grandparentY,
                    level: -2,
                    relationshipType: getRelationLabel(grandparent.relationship_code || ''),
                    relationshipCode: grandparent.relationship_code,
                });
            });
        }

        return positions;
    };

    const nodePositions = calculateNodePositions();

    // Calculer les connexions
    const calculateConnections = () => {
        const connections: Array<{
            from: { x: number; y: number };
            to: { x: number; y: number };
            type: 'parent' | 'child' | 'spouse' | 'sibling';
        }> = [];

        const centerNode = nodePositions.find(n => n.isCenter);
        if (!centerNode) return connections;

        // Connexions avec les parents
        nodePositions
            .filter(n => n.level === -1)
            .forEach(parent => {
                connections.push({
                    from: { x: parent.x, y: parent.y + nodeHeight / 2 },
                    to: { x: centerNode.x, y: centerNode.y - nodeHeight / 2 },
                    type: 'parent'
                });
            });

        // Connexions avec les enfants
        nodePositions
            .filter(n => n.level === 1)
            .forEach(child => {
                connections.push({
                    from: { x: centerNode.x, y: centerNode.y + nodeHeight / 2 },
                    to: { x: child.x, y: child.y - nodeHeight / 2 },
                    type: 'child'
                });
            });

        // Connexion avec le conjoint
        const spouse = nodePositions.find(n => n.level === 0 && !n.isCenter && n.relationshipCode?.includes('wife') || n.relationshipCode?.includes('husband'));
        if (spouse) {
            connections.push({
                from: { x: centerNode.x + nodeWidth / 2, y: centerNode.y },
                to: { x: spouse.x - nodeWidth / 2, y: spouse.y },
                type: 'spouse'
            });
        }

        // Connexions avec les frères et sœurs
        nodePositions
            .filter(n => n.level === 0 && !n.isCenter && (n.relationshipCode?.includes('brother') || n.relationshipCode?.includes('sister')))
            .forEach(sibling => {
                connections.push({
                    from: { x: centerNode.x - nodeWidth / 2, y: centerNode.y },
                    to: { x: sibling.x + nodeWidth / 2, y: sibling.y },
                    type: 'sibling'
                });
            });

        return connections;
    };

    const connections = calculateConnections();

    // Fonctions de contrôle
    const handleZoomIn = () => setZoom(prev => Math.min(prev + 0.2, 2));
    const handleZoomOut = () => setZoom(prev => Math.max(prev - 0.2, 0.5));
    const handleReset = () => {
        setZoom(1);
        setPan({ x: 0, y: 0 });
    };

    // Gestion du drag
    const handleMouseDown = (e: React.MouseEvent) => {
        setIsDragging(true);
        setDragStart({ x: e.clientX - pan.x, y: e.clientY - pan.y });
    };

    const handleMouseMove = (e: React.MouseEvent) => {
        if (!isDragging) return;
        setPan({
            x: e.clientX - dragStart.x,
            y: e.clientY - dragStart.y,
        });
    };

    const handleMouseUp = () => {
        setIsDragging(false);
    };

    // Composant de l'arbre hiérarchique
    const HierarchicalTree = () => (
        <div className="relative">
            {/* Contrôles de zoom */}
            <div className="absolute top-4 right-4 z-10 flex gap-2">
                <Button variant="outline" size="sm" onClick={handleZoomOut}>
                    <ZoomOut className="h-4 w-4" />
                </Button>
                <Button variant="outline" size="sm" onClick={handleZoomIn}>
                    <ZoomIn className="h-4 w-4" />
                </Button>
                <Button variant="outline" size="sm" onClick={handleReset}>
                    <RotateCcw className="h-4 w-4" />
                </Button>
            </div>

            {/* Instructions */}
            <div className="absolute top-4 left-4 z-10">
                <Card className="bg-white/90 backdrop-blur-sm">
                    <CardContent className="p-3">
                        <p className="text-sm text-muted-foreground">
                            🖱️ Cliquez et glissez pour déplacer • 🔍 Utilisez les boutons pour zoomer
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Container de l'arbre */}
            <div
                ref={containerRef}
                className="w-full h-[800px] bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg border overflow-hidden cursor-move"
                onMouseDown={handleMouseDown}
                onMouseMove={handleMouseMove}
                onMouseUp={handleMouseUp}
                onMouseLeave={handleMouseUp}
            >
                <div
                    className="relative w-full h-full"
                    style={{
                        transform: `translate(${pan.x}px, ${pan.y}px) scale(${zoom})`,
                        transformOrigin: 'center center',
                        transition: isDragging ? 'none' : 'transform 0.2s ease-out',
                    }}
                >
                    {/* Connexions SVG */}
                    <TreeConnections
                        connections={connections}
                        containerWidth={1600}
                        containerHeight={1200}
                    />

                    {/* Nœuds de l'arbre */}
                    {nodePositions.map((node, index) => (
                        <div
                            key={`${node.person.id}-${index}`}
                            className="absolute"
                            style={{
                                left: node.x - nodeWidth / 2,
                                top: node.y - nodeHeight / 2,
                                zIndex: node.isCenter ? 10 : 5,
                            }}
                        >
                            <TreeNode
                                person={node.person}
                                relationshipType={node.relationshipType}
                                relationshipCode={node.relationshipCode}
                                isCenter={node.isCenter}
                                level={Math.abs(node.level)}
                                onClick={() => console.log('Clicked:', node.person.name)}
                            />
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );

    return (
        <AppSidebarLayout>
            <Head title="Arbre Familial" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Statistiques */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total des Relations</CardTitle>
                                <Users className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{statistics.total_relatives}</div>
                                <p className="text-xs text-muted-foreground">
                                    membres de la famille
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Générations</CardTitle>
                                <TreePine className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {(treeData.grandparents.paternal.length + treeData.grandparents.maternal.length > 0 ? 1 : 0) +
                                     (treeData.parents.length > 0 ? 1 : 0) +
                                     1 + // L'utilisateur actuel
                                     (treeData.children.length > 0 ? 1 : 0) +
                                     (treeData.grandchildren.length > 0 ? 1 : 0)}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    générations représentées
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Relations Automatiques</CardTitle>
                                <Crown className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {Array.isArray(relationships) ? relationships.filter(r => r.created_automatically).length : 0}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    déduites intelligemment
                                </p>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Arbre hiérarchique */}
                    {statistics.total_relatives > 0 ? (
                        <HierarchicalTree />
                    ) : (
                        <Card className="text-center py-12">
                            <CardContent>
                                <TreePine className="h-16 w-16 text-muted-foreground mx-auto mb-4" />
                                <h3 className="text-lg font-semibold mb-2">Aucune relation familiale</h3>
                                <p className="text-muted-foreground mb-4">
                                    Commencez à construire votre arbre familial en ajoutant des relations.
                                </p>
                                <Link href="/family-relations">
                                    <Button>
                                        Ajouter des relations
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </AppSidebarLayout>
    );
};

export default FamilyTreeIndex;
