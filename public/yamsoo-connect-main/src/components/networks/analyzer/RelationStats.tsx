
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { getRelationLabel } from "@/utils/relationUtils";
import { Progress } from "@/components/ui/progress";
import { PieChart, Pie, Cell, ResponsiveContainer, Legend, Tooltip } from "recharts";

interface RelationStatsProps {
  relationStats: {[key: string]: number};
  statusStats: {[key: string]: number};
  totalRelations: number;
}

// Couleurs pour le graphique
const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#9370DB', '#6495ED', '#20B2AA'];

export const RelationStats = ({ relationStats, statusStats, totalRelations }: RelationStatsProps) => {
  // Préparer les données pour le graphique des statuts
  const statusData = Object.entries(statusStats).map(([status, count], index) => ({
    name: status === 'pending' ? 'En attente' : status === 'accepted' ? 'Acceptée' : 'Refusée',
    value: count,
    color: status === 'accepted' ? '#10b981' : status === 'pending' ? '#f59e0b' : '#ef4444'
  }));

  // Préparer les données pour le graphique des relations
  const relationData = Object.entries(relationStats)
    .sort((a, b) => b[1] - a[1]) // Trier par nombre décroissant
    .slice(0, 7) // Prendre les 7 relations les plus fréquentes
    .map(([type, count], index) => ({
      name: getRelationLabel(type),
      value: count,
      color: COLORS[index % COLORS.length]
    }));

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* Carte des statuts */}
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium">Statuts des relations</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-[200px]">
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                  <Pie
                    data={statusData}
                    cx="50%"
                    cy="50%"
                    innerRadius={60}
                    outerRadius={80}
                    paddingAngle={2}
                    dataKey="value"
                    label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                    labelLine={false}
                  >
                    {statusData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                  </Pie>
                  <Tooltip formatter={(value) => [`${value} relation(s)`, 'Nombre']} />
                </PieChart>
              </ResponsiveContainer>
            </div>
            <div className="mt-2 space-y-2">
              {Object.entries(statusStats).map(([status, count]) => (
                <div key={status} className="flex items-center justify-between">
                  <Badge variant={status === 'accepted' ? 'default' : status === 'pending' ? 'secondary' : 'destructive'} className="px-2 py-1">
                    {status === 'pending' ? 'En attente' : status === 'accepted' ? 'Acceptée' : 'Refusée'}
                  </Badge>
                  <span className="text-sm font-medium">{count}</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
        
        {/* Carte des types de relations */}
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium">Types de relations</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-[200px]">
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                  <Pie
                    data={relationData}
                    cx="50%"
                    cy="50%"
                    outerRadius={80}
                    dataKey="value"
                    label={({ name }) => name}
                  >
                    {relationData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                  </Pie>
                  <Tooltip formatter={(value) => [`${value} relation(s)`, 'Nombre']} />
                </PieChart>
              </ResponsiveContainer>
            </div>
            <div className="mt-4 flex flex-wrap gap-2 max-h-[150px] overflow-y-auto">
              {Object.entries(relationStats)
                .sort((a, b) => b[1] - a[1])
                .map(([type, count]) => (
                <Badge key={type} variant="outline" className="px-2 py-1">
                  {getRelationLabel(type)} ({count})
                </Badge>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
      
      {/* Carte des statistiques générales */}
      <Card>
        <CardHeader className="pb-2">
          <CardTitle className="text-sm font-medium">Statistiques générales</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <div>
              <div className="flex justify-between mb-1">
                <span className="text-sm font-medium">Relations acceptées</span>
                <span className="text-sm font-medium">{statusStats['accepted'] || 0} / {totalRelations}</span>
              </div>
              <Progress value={(statusStats['accepted'] || 0) / totalRelations * 100} className="h-2" />
            </div>
            
            <div>
              <div className="flex justify-between mb-1">
                <span className="text-sm font-medium">Relations en attente</span>
                <span className="text-sm font-medium">{statusStats['pending'] || 0} / {totalRelations}</span>
              </div>
              <Progress value={(statusStats['pending'] || 0) / totalRelations * 100} className="h-2" />
            </div>
            
            <div>
              <p className="text-sm mt-4">
                Nombre total de relations: <strong>{totalRelations}</strong>
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};
