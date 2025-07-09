
import { FamilyGroupCard } from "./FamilyGroupCard";
import { EmptyFamilyState } from "./EmptyFamilyState";
import { FamilyLoadingState } from "./FamilyLoadingState";

interface FamilyMember {
  id: string;
  userId: string;
  fullName: string;
  avatarUrl: string | null;
  relationLabel: string;
  email: string;
}

interface FamilyGroup {
  id: string;
  name: string;
  members: FamilyMember[];
}

interface FamilyGroupListProps {
  familyGroups: FamilyGroup[];
  loading: boolean;
}

export function FamilyGroupList({ familyGroups, loading }: FamilyGroupListProps) {
  if (loading) {
    return <FamilyLoadingState />;
  }

  if (familyGroups.length === 0) {
    return <EmptyFamilyState />;
  }

  return (
    <div className="space-y-8">
      {familyGroups.map((group) => (
        <FamilyGroupCard
          key={group.id}
          id={group.id}
          name={group.name}
          members={group.members}
        />
      ))}
    </div>
  );
}
