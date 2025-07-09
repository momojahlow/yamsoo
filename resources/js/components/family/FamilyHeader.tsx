
import { Button } from "@/components/ui/button";
import { Trees } from "lucide-react";
import { useIsMobile } from "@/hooks/use-mobile";

export function FamilyHeader() {
  const isMobile = useIsMobile();

  const handleViewFamilyTree = () => {
    window.location.href = '/famille/arbre';
  };

  return (
    <div className="flex justify-between items-center mb-8">
      <h1 className={`${isMobile ? 'text-2xl' : 'text-3xl'} font-bold`}>Ma Famille</h1>
      <Button
        onClick={handleViewFamilyTree}
        size={isMobile ? "sm" : "default"}
        className="whitespace-nowrap"
      >
        <Trees className={`${isMobile ? 'h-3.5 w-3.5' : 'h-4 w-4'} mr-2`} />
        Arbre familial
      </Button>
    </div>
  );
}
