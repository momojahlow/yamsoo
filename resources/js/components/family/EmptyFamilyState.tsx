
import { Trees } from "lucide-react";

export function EmptyFamilyState() {
  return (
    <div className="flex flex-col items-center justify-center h-64 border rounded-lg p-8 bg-slate-50">
      <Trees className="h-16 w-16 text-slate-400 mb-4" />
      <h2 className="text-xl font-medium">Aucune famille trouvée</h2>
      <p className="text-muted-foreground text-center mt-2">
        Vous n'avez pas encore de relations familiales acceptées.
        <br />
        Ajoutez des membres à votre famille pour les voir ici.
      </p>
    </div>
  );
}
