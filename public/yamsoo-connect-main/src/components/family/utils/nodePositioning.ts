
import { TreeNode } from "../types";

/**
 * Définit un niveau fixe pour un nœud basé uniquement sur le type de relation
 */
export function setNodePositioning(node: TreeNode, relationType: string): void {
  switch (relationType) {
    // Parents (niveau au-dessus de l'utilisateur)
    case 'father':
    case 'mother':
      node.level = 1;
      node.generation = -1;
      break;
      
    // Grands-parents (niveau au-dessus des parents)
    case 'grandfather':
    case 'grandmother':
      node.level = 0;
      node.generation = -2;
      break;
      
    // Frères et sœurs (même niveau que l'utilisateur)
    case 'brother':
    case 'sister':
    case 'half_brother_maternal':
    case 'half_brother_paternal':
    case 'half_sister_maternal':
    case 'half_sister_paternal':
      node.level = 2;
      node.generation = 0;
      break;
      
    // Conjoints (même niveau que l'utilisateur - assuré au même niveau)
    case 'husband':
    case 'wife':
      node.level = 2; // Même niveau que l'utilisateur
      node.generation = 0;
      break;
      
    // Enfants (niveau en-dessous de l'utilisateur)
    case 'son':
    case 'daughter':
      node.level = 3;
      node.generation = 1;
      break;
      
    // Petits-enfants (niveau en-dessous des enfants)
    case 'grandson':
    case 'granddaughter':
      node.level = 4;
      node.generation = 2;
      break;
      
    // Neveux/Nièces (enfants des frères et sœurs)
    case 'nephew':
    case 'niece':
    case 'nephew_brother':
    case 'nephew_sister':
    case 'niece_brother':
    case 'niece_sister':
      node.level = 3;
      node.generation = 1;
      break;
      
    // Oncles/Tantes
    case 'uncle':
    case 'aunt':
    case 'uncle_paternal':
    case 'uncle_maternal':
    case 'aunt_paternal':
    case 'aunt_maternal':
      node.level = 1;
      node.generation = -1;
      break;
      
    // Cousins
    case 'cousin':
    case 'cousin_paternal_m':
    case 'cousin_maternal_m':
    case 'cousin_paternal_f':
    case 'cousin_maternal_f':
      node.level = 2;
      node.generation = 0;
      break;
  }
}
