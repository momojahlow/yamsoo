
import { Sparkles } from "lucide-react";

export function EmptySuggestions() {
  return (
    <div className="text-center my-12 p-6 border rounded-lg">
      <Sparkles className="h-10 w-10 text-amber-500 mx-auto mb-4" />
      <h3 className="text-lg font-medium mb-2">Aucune suggestion pour le moment</h3>
      <p className="text-muted-foreground">
        Vous n'avez pas de suggestions en attente. Revenez plus tard !
      </p>
    </div>
  );
}
