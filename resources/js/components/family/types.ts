
export interface TreeNode {
  id: string;
  name: string;
  avatarUrl?: string;
  relation: string;
  relationId: string;
  children: TreeNode[];
  level: number;
  generation: number;
  x?: number;
  y?: number;
  birthDate?: string; // Ajouter la date de naissance
}
