import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { SidebarProvider } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/app/Sidebar";
import { ProfileList } from "@/components/networks/ProfileList";
import { SearchBar } from "@/components/networks/SearchBar";
import { NetworksProvider, useNetworks } from "@/components/networks/NetworksProvider";
import { supabase } from "@/integrations/supabase/client";
import { useToast } from "@/hooks/use-toast";
import { useIsMobile } from "@/hooks/use-mobile";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";
import {
  Pagination,
  PaginationContent,
  PaginationItem,
  PaginationLink,
  PaginationNext,
  PaginationPrevious,
} from "@/components/ui/pagination";

const InnerContent = () => {
  const { searchQuery, setSearchQuery, filteredProfiles, refetchProfiles } = useNetworks();
  const navigate = useNavigate();
  const { toast } = useToast();
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 12;
  const isMobile = useIsMobile();
  const [currentUser, setCurrentUser] = useState(null);

  useEffect(() => {
    const checkUser = async () => {
      const { data: { user } } = await supabase.auth.getUser();
      if (!user) {
        navigate("/auth");
      } else {
        setCurrentUser(user);
      }
    };

    checkUser();
  }, [navigate]);

  const handleSendMessage = async (profileId: string) => {
    if (!currentUser) {
      toast({
        title: "Erreur",
        description: "Vous devez être connecté pour envoyer un message",
        variant: "destructive",
      });
      return;
    }

    // Rediriger vers la page de messages avec l'ID du destinataire
    navigate(`/messagerie`, { state: { selectedContactId: profileId } });
  };

  // Calcul pour la pagination
  const totalPages = Math.ceil(filteredProfiles.length / itemsPerPage);
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const currentProfiles = filteredProfiles.slice(startIndex, endIndex);

  // Générer la liste des pages pour la pagination
  const paginationItems = [];
  const maxPagesToShow = 5; // Nombre maximum de pages à afficher

  if (totalPages <= maxPagesToShow) {
    // Afficher toutes les pages si leur nombre est inférieur au maximum
    for (let i = 1; i <= totalPages; i++) {
      paginationItems.push(
        <PaginationItem key={i}>
          <PaginationLink 
            href="#" 
            onClick={(e) => { e.preventDefault(); setCurrentPage(i); }}
            isActive={currentPage === i}
          >
            {i}
          </PaginationLink>
        </PaginationItem>
      );
    }
  } else {
    // Logique pour afficher un sous-ensemble de pages avec des ellipses
    // Toujours afficher la première page
    paginationItems.push(
      <PaginationItem key={1}>
        <PaginationLink 
          href="#" 
          onClick={(e) => { e.preventDefault(); setCurrentPage(1); }}
          isActive={currentPage === 1}
        >
          1
        </PaginationLink>
      </PaginationItem>
    );

    // Calculer le début et la fin de la plage de pages à afficher
    let startPage = Math.max(2, currentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages - 1, startPage + maxPagesToShow - 3);

    if (startPage > 2) {
      paginationItems.push(
        <PaginationItem key="ellipsis-1">
          <span className="px-4 py-2">...</span>
        </PaginationItem>
      );
    }

    // Pages du milieu
    for (let i = startPage; i <= endPage; i++) {
      paginationItems.push(
        <PaginationItem key={i}>
          <PaginationLink 
            href="#" 
            onClick={(e) => { e.preventDefault(); setCurrentPage(i); }}
            isActive={currentPage === i}
          >
            {i}
          </PaginationLink>
        </PaginationItem>
      );
    }

    if (endPage < totalPages - 1) {
      paginationItems.push(
        <PaginationItem key="ellipsis-2">
          <span className="px-4 py-2">...</span>
        </PaginationItem>
      );
    }

    // Toujours afficher la dernière page
    paginationItems.push(
      <PaginationItem key={totalPages}>
        <PaginationLink 
          href="#" 
          onClick={(e) => { e.preventDefault(); setCurrentPage(totalPages); }}
          isActive={currentPage === totalPages}
        >
          {totalPages}
        </PaginationLink>
      </PaginationItem>
    );
  }

  return (
    <div className={`container py-8 ${isMobile ? 'pt-20' : ''}`}>
      {/* Afficher le titre uniquement sur desktop ou tablette */}
      {!isMobile && <h1 className="text-3xl font-bold tracking-tight mb-8 text-brown">Réseaux</h1>}
      
      {/* Sur mobile, utiliser un titre plus grand pour compenser celui manquant dans la navbar */}
      {isMobile && <h1 className="text-2xl font-bold tracking-tight mb-6 text-brown">Réseaux</h1>}
      
      <SearchBar value={searchQuery} onChange={setSearchQuery} />
      <div className="mt-8">
        <ProfileList 
          profiles={currentProfiles}
          onSendMessage={handleSendMessage}
          onRelationAdded={refetchProfiles}
        />
        
        {totalPages > 1 && (
          <Pagination className="mt-8">
            <PaginationContent>
              <PaginationItem>
                <PaginationPrevious 
                  href="#" 
                  onClick={(e) => { 
                    e.preventDefault(); 
                    if (currentPage > 1) setCurrentPage(currentPage - 1); 
                  }} 
                />
              </PaginationItem>
              
              {paginationItems}
              
              <PaginationItem>
                <PaginationNext 
                  href="#" 
                  onClick={(e) => { 
                    e.preventDefault(); 
                    if (currentPage < totalPages) setCurrentPage(currentPage + 1); 
                  }} 
                />
              </PaginationItem>
            </PaginationContent>
          </Pagination>
        )}
      </div>
    </div>
  );
};

export default function Networks() {
  const navigate = useNavigate();
  const [currentUser, setCurrentUser] = useState(null);
  const isMobile = useIsMobile();

  useEffect(() => {
    const checkUser = async () => {
      const { data: { user } } = await supabase.auth.getUser();
      if (!user) {
        navigate("/auth");
      } else {
        setCurrentUser(user);
      }
    };

    checkUser();
  }, [navigate]);

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full bg-background">
        <AppSidebar />
        <main className="flex-1 md:ml-16 pb-20 md:pb-8">
          <NetworksProvider user={currentUser}>
            <InnerContent />
          </NetworksProvider>
        </main>
        
        {/* Navigation mobile en bas de l'écran */}
        {isMobile && <MobileNavBar />}
      </div>
    </SidebarProvider>
  );
}
