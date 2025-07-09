
import React from "react";
import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert";
import { AlertCircle } from "lucide-react";

interface FamilyTreeErrorProps {
  isEmpty: boolean;
}

export function FamilyTreeError({ isEmpty }: FamilyTreeErrorProps) {
  if (!isEmpty) return null;
  
  return (
    <Alert variant="destructive" className="mb-4">
      <AlertCircle className="h-4 w-4" />
      <AlertTitle>Aucun lien familial visible</AlertTitle>
      <AlertDescription>
        Bien que vous ayez des relations familiales enregistrées, aucun lien parent-enfant n'a pu être établi. 
        Essayez d'ajouter des relations comme père, mère, fils ou fille pour visualiser l'arbre.
      </AlertDescription>
    </Alert>
  );
}
