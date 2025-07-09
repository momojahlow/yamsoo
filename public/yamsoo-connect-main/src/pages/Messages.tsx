
import { useState, useEffect } from "react";
import { useLocation } from "react-router-dom";
import { useToast } from "@/hooks/use-toast";
import { ChatContainer } from "@/components/chat/ChatContainer";
import { SidebarProvider } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/app/Sidebar";
import { useIsMobile } from "@/hooks/use-mobile";
import { supabase } from "@/integrations/supabase/client";
import { MobileDrawerMenu } from "@/components/mobile/MobileDrawerMenu"; // Import the mobile drawer
import { Menu } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function Messages() {
  const [currentUser, setCurrentUser] = useState<any>(null);
  const { toast } = useToast();
  const location = useLocation();
  const isMobile = useIsMobile();
  
  // Get the selected contact ID if passed via navigation
  const selectedContactId = location.state?.selectedContactId || null;
  
  // State for mobile drawer
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  useEffect(() => {
    const checkUser = async () => {
      const { data: { user } } = await supabase.auth.getUser();
      if (user) {
        setCurrentUser(user);
        // Store the user ID in a global variable for components
        // that don't have access to the auth context
        (window as any).CURRENT_USER_ID = user.id;
      } else {
        toast({
          title: "Authentification requise",
          description: "Veuillez vous connecter pour accéder à cette page",
          variant: "destructive",
        });
      }
    };

    checkUser();
  }, [toast]);

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full bg-background overflow-hidden">
        {!isMobile && <AppSidebar />}
        
        {isMobile && (
          <div className="fixed top-0 left-0 z-50 p-3">
            <Button 
              variant="ghost" 
              size="icon" 
              onClick={() => setMobileMenuOpen(true)}
              aria-label="Menu"
            >
              <Menu size={20} />
            </Button>
          </div>
        )}
        
        {/* Add the mobile drawer menu */}
        {isMobile && (
          <MobileDrawerMenu 
            open={mobileMenuOpen} 
            onOpenChange={setMobileMenuOpen}
          />
        )}
        
        <main className="flex-1 h-screen overflow-hidden">
          <ChatContainer 
            currentUser={currentUser} 
            initialSelectedContactId={selectedContactId}
          />
        </main>
      </div>
    </SidebarProvider>
  );
}
