
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { FamilyMemberItem } from "./FamilyMemberItem";
import { Users } from "lucide-react";

interface FamilyMember {
  id: string;
  userId: string;
  fullName: string;
  avatarUrl: string | null;
  relationLabel: string;
  email: string;
}

interface FamilyGroupCardProps {
  id: string;
  name: string;
  members: FamilyMember[];
}

export function FamilyGroupCard({ name, members }: FamilyGroupCardProps) {
  return (
    <Card className="overflow-hidden shadow-sm hover:shadow-md transition-shadow">
      <CardHeader className="bg-gradient-to-r from-blue-50 to-indigo-50 border-b">
        <CardTitle className="flex items-center">
          <Users className="mr-2 h-5 w-5 text-primary" />
          {name}
        </CardTitle>
        <CardDescription>
          {members.length} membre{members.length > 1 ? 's' : ''}
        </CardDescription>
      </CardHeader>
      <CardContent className="pt-6">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {members.map((member) => (
            <FamilyMemberItem 
              key={member.id}
              id={member.id}
              fullName={member.fullName}
              avatarUrl={member.avatarUrl}
              relationLabel={member.relationLabel}
              email={member.email}
            />
          ))}
        </div>
      </CardContent>
    </Card>
  );
}
