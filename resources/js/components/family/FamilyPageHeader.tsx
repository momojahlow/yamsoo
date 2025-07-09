
import { Heart } from "lucide-react";
import { Users } from "lucide-react";

export function FamilyPageHeader() {
  return (
    <div className="mb-8 bg-gradient-to-r from-indigo-50 to-blue-50 p-6 rounded-lg border">
      <h1 className="text-3xl font-bold flex items-center gap-2 text-primary">
        <Heart className="h-7 w-7 text-rose-500" /> Yamsoo !
      </h1>
      <p className="text-muted-foreground mt-2 flex items-center">
        <Users className="mr-2 h-4 w-4" />
        Découvrez tous les membres de vos familles
      </p>
    </div>
  );
}
