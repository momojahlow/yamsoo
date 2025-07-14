
import React from "react";
import { Button } from "@/components/ui/button";
import AppLayout from '@/layouts/app-layout';
import { FamilyMemberCard } from "@/components/family/FamilyMemberCard";

interface Member {
  id: number;
  name: string;
  email: string;
  avatar?: string | null;
  bio?: string | null;
  birth_date?: string | null;
  gender?: string | null;
  phone?: string | null;
  relation: string;
  status: string;
}

interface FamilyProps {
  members: Member[];
}

export default function Family({ members }: FamilyProps) {
  console.log('members', members);
  return (
    <AppLayout>
      <div className="max-w-6xl mx-auto py-8 px-2 md:px-0">
        <div className="flex items-center justify-between mb-8">
          <h2 className="text-3xl font-bold text-center">Ma famille</h2>
          <Button
            variant="outline"
            className="flex items-center gap-2"
            onClick={() => window.location.href = '/famille/arbre'}
          >
            <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Afficher l'arbre familial
          </Button>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          {members.map((member) => (
            <div key={member.id} className="flex flex-col items-center">
              <FamilyMemberCard
                id={member.id.toString()}
                name={member.name}
                avatarUrl={member.avatar || undefined}
                relation={member.relation}
              />
              <div className="mt-2 text-center w-full">
                <div className="font-semibold text-lg">{member.name}</div>
                {member.bio && <div className="text-xs text-gray-700 italic mb-1">{member.bio}</div>}
                {member.birth_date && <div className="text-xs text-gray-500">Né(e) le {member.birth_date}</div>}
                {member.gender && <div className="text-xs text-gray-500">Sexe : {member.gender}</div>}
                {member.phone && <div className="text-xs text-gray-500">📞 {member.phone}</div>}
                <div className="text-xs text-gray-400 mt-1">{member.email}</div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </AppLayout>
  );
}
