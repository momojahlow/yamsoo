
import { useState, useEffect } from "react";
import { FamilyRelation } from "@/types/family";
import { DeleteRelationDialog } from "./tree/DeleteRelationDialog";
import { useTreeLayout } from "./tree/hooks/useTreeLayout";
import { useRelationDelete } from "./tree/useRelationDelete";
import { FamilyTreeLegend } from "./tree/components/FamilyTreeLegend";
import { FamilyTreeError } from "./tree/components/FamilyTreeError";
import { FamilyTreeInstructions } from "./tree/components/FamilyTreeInstructions";
import { FamilyTreeContent } from "./tree/components/FamilyTreeContent";
import { useFamilyTreePrint } from "./tree/hooks/useFamilyTreePrint";
import { useIsMobile } from "@/hooks/use-mobile";

interface FamilyTreeViewProps {
  relations: FamilyRelation[];
}

export function FamilyTreeView({ relations }: FamilyTreeViewProps) {
  const { treeNodes, layoutComplete, calculateMaxLevel } = useTreeLayout(relations);
  const { selectedRelation, setSelectedRelation, handleDeleteRelation } = useRelationDelete();
  const [isEmpty, setIsEmpty] = useState(false);
  const { printAreaRef, handlePrint } = useFamilyTreePrint();
  const isMobile = useIsMobile();

  useEffect(() => {
    // Check if we have valid relations but no tree nodes after processing
    if (relations.length > 0 && treeNodes.length === 0 && layoutComplete) {
      setIsEmpty(true);
    } else {
      setIsEmpty(false);
    }
  }, [relations, treeNodes, layoutComplete]);

  const handleDeleteClick = (relationId: string, name: string) => {
    setSelectedRelation({ id: relationId, name });
  };

  return (
    <div className={`space-y-${isMobile ? '2' : '4'}`}>
      <FamilyTreeLegend 
        isEmpty={isEmpty} 
        layoutComplete={layoutComplete} 
        treeNodes={treeNodes}
        onPrintClick={handlePrint}
      />

      <FamilyTreeError isEmpty={isEmpty} />

      {!isEmpty && <FamilyTreeInstructions />}

      <FamilyTreeContent
        treeNodes={treeNodes}
        layoutComplete={layoutComplete}
        isEmpty={isEmpty}
        calculateMaxLevel={calculateMaxLevel}
        onDeleteRelation={handleDeleteClick}
        printAreaRef={printAreaRef}
      />

      <DeleteRelationDialog 
        selectedRelation={selectedRelation}
        onOpenChange={() => setSelectedRelation(null)}
        onDelete={handleDeleteRelation}
      />
    </div>
  );
}
