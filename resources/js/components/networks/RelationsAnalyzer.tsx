
import { useState, useEffect } from "react";
import { FamilyRelation } from "@/types/family";
import { useFamilyRelation } from "@/hooks/useFamilyRelation";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { RelationStats } from "./analyzer/RelationStats";
import { RelationsList } from "./analyzer/RelationsList";
import { AnalyzerLoading } from "./analyzer/AnalyzerLoading";

export function RelationsAnalyzer() {
  const [relations, setRelations] = useState<FamilyRelation[] | null>(null);
  const [loading, setLoading] = useState(true);
  const [relationStats, setRelationStats] = useState<{[key: string]: number}>({});
  const [statusStats, setStatusStats] = useState<{[key: string]: number}>({});
  const { fetchRelations } = useFamilyRelation();

  useEffect(() => {
    loadRelations();
  }, []);

  const loadRelations = async () => {
    setLoading(true);
    const data = await fetchRelations();
    setRelations(data);
    
    if (data) {
      // Calculer les statistiques
      const typeStats: {[key: string]: number} = {};
      const statStats: {[key: string]: number} = {};
      
      data.forEach(relation => {
        // Compter par type de relation
        if (typeStats[relation.relation_type]) {
          typeStats[relation.relation_type]++;
        } else {
          typeStats[relation.relation_type] = 1;
        }
        
        // Compter par statut
        if (statStats[relation.status]) {
          statStats[relation.status]++;
        } else {
          statStats[relation.status] = 1;
        }
      });
      
      setRelationStats(typeStats);
      setStatusStats(statStats);
    }
    
    setLoading(false);
  };

  if (loading) {
    return <AnalyzerLoading />;
  }

  return (
    <div className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Analyse des relations familiales</CardTitle>
          <CardDescription>
            Aperçu détaillé de vos relations dans la base de données
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Tabs defaultValue="stats">
            <TabsList>
              <TabsTrigger value="stats">Statistiques</TabsTrigger>
              <TabsTrigger value="list">Liste des relations</TabsTrigger>
            </TabsList>
            
            <TabsContent value="stats">
              <RelationStats 
                relationStats={relationStats} 
                statusStats={statusStats} 
                totalRelations={relations?.length || 0} 
              />
            </TabsContent>
            
            <TabsContent value="list">
              <RelationsList relations={relations || []} />
            </TabsContent>
          </Tabs>
        </CardContent>
      </Card>
      
      <div className="flex justify-end">
        <Button variant="outline" onClick={loadRelations}>
          Rafraîchir les données
        </Button>
      </div>
    </div>
  );
}
