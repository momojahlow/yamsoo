
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Database } from "@/integrations/supabase/types";
import { useTranslation } from "react-i18next";
import { adaptRelationToGender, getInverseRelation, getRelationLabel } from "@/utils/relationUtils";
import { FamilyRelationType } from "@/types/family";

type Profile = Database['public']['Tables']['profiles']['Row'];
type FamilyRelation = Database['public']['Tables']['family_relations']['Row'];

type PendingRelation = {
  relation: FamilyRelation;
  requester: Profile;
};

interface PendingRelationsProps {
  pendingRelations: PendingRelation[];
  onAccept: (relationId: string) => void;
  onReject: (relationId: string) => void;
}

export function PendingRelations({
  pendingRelations,
  onAccept,
  onReject,
}: PendingRelationsProps) {
  const { t } = useTranslation();

  if (!pendingRelations || pendingRelations.length === 0) {
    return (
      <div className="mb-8">
        <h2 className="text-xl font-semibold mb-4">Demandes de Relations en Attente</h2>
        <div className="text-muted-foreground">Aucune demande de relation en attente.</div>
      </div>
    );
  }

  return (
    <div className="mb-8">
      <h2 className="text-xl font-semibold mb-4">Demandes de Relations en Attente</h2>
      <div className="grid gap-4">
        {pendingRelations.map(({ relation, requester }) => {
          // Obtenir la relation inverse
          let inverseRelationType = getInverseRelation(relation.relation_type as FamilyRelationType);

          // Adapter en fonction du genre du demandeur
          inverseRelationType = adaptRelationToGender(inverseRelationType, requester.gender || '');

          // Obtenir le libellé de la relation
          const relationLabel = getRelationLabel(inverseRelationType);

          return (
            <Card key={relation.id} className="p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="font-medium">
                    {requester.first_name} {requester.last_name}
                  </p>
                  <p className="text-sm text-muted-foreground">
                    Vous a ajouté en tant que {relationLabel}
                  </p>
                </div>
                <div className="flex gap-2">
                  <Button
                    variant="default"
                    size="sm"
                    onClick={() => onAccept(relation.id)}
                  >
                    Accepter
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onReject(relation.id)}
                  >
                    Refuser
                  </Button>
                </div>
              </div>
            </Card>
          );
        })}
      </div>
    </div>
  );
}
