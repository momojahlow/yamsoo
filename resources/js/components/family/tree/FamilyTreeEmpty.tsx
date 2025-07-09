
import React from 'react';

export function FamilyTreeEmpty() {
  return (
    <div className="flex flex-col items-center justify-center h-64">
      <p className="text-xl text-muted-foreground">Aucune relation familiale à afficher</p>
      <p className="text-sm text-muted-foreground">Accédez à "Découvrir des profils" pour ajouter des relations</p>
    </div>
  );
}
