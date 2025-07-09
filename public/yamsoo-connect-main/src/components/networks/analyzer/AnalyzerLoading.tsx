
import { Loader2 } from "lucide-react";

export const AnalyzerLoading = () => {
  return (
    <div className="flex flex-col items-center justify-center h-64">
      <Loader2 className="h-8 w-8 animate-spin mb-4 text-primary" />
      <p className="text-muted-foreground">Analyse des relations familiales...</p>
    </div>
  );
};
