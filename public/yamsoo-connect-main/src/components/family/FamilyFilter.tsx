
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";

interface FamilyFilterProps {
  onFilterChange: (type: string) => void;
}

export function FamilyFilter({ onFilterChange }: FamilyFilterProps) {
  const relationCategories = [
    { value: "all", label: "Toutes les relations" },
    { value: "spouse", label: "Relations conjugales" },
    { value: "parent", label: "Relations parentales" },
    { value: "child", label: "Relations enfants" },
    { value: "sibling", label: "Relations fratrie" },
    { value: "grandparent", label: "Relations grands-parents" },
    { value: "uncle", label: "Relations oncle/tante" },
    { value: "cousin", label: "Relations cousins" },
    { value: "other", label: "Autres relations" },
  ];

  return (
    <div className="mb-6">
      <Select onValueChange={onFilterChange} defaultValue="all">
        <SelectTrigger className="w-[280px]">
          <SelectValue placeholder="Filtrer par type de relation" />
        </SelectTrigger>
        <SelectContent>
          {relationCategories.map((category) => (
            <SelectItem key={category.value} value={category.value}>
              {category.label}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
    </div>
  );
}
