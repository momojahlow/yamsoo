
import { SidebarProvider } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/app/Sidebar";
import { useIsMobile } from "@/hooks/use-mobile";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

interface Message {
  id: number;
  content: string;
  created_at: string;
  sender: {
    id: number;
    name: string;
  };
  recipient: {
    id: number;
    name: string;
  };
}

interface MessagesProps {
  messages: Message[];
}

export default function Messages({ messages }: MessagesProps) {
  const isMobile = useIsMobile();

  const handleSendMessage = (recipientId: number) => {
    window.location.href = `/messagerie/${recipientId}`;
  };

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full bg-background">
        <AppSidebar />
        <main className="flex-1 p-4 md:p-8 md:ml-16 pb-20 md:pb-8">
          <div className="max-w-4xl mx-auto">
            <h1 className="text-3xl font-bold mb-8">Messages</h1>

            {messages.length === 0 ? (
              <div className="text-center py-12">
                <p className="text-gray-600 mb-4">
                  Aucun message pour le moment.
                </p>
                <Button onClick={() => window.location.href = '/reseaux'}>
                  Découvrir des contacts
                </Button>
              </div>
            ) : (
              <div className="space-y-4">
                {messages.map((message) => (
                  <Card key={message.id}>
                    <CardHeader>
                      <CardTitle className="flex items-center justify-between">
                        <span>
                          De: {message.sender.name} → À: {message.recipient.name}
                        </span>
                        <span className="text-sm text-gray-500">
                          {new Date(message.created_at).toLocaleDateString()}
                        </span>
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <p className="text-gray-700 mb-4">{message.content}</p>
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => handleSendMessage(message.sender.id)}
                      >
                        Répondre
                      </Button>
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </div>
        </main>
        {isMobile && <MobileNavBar />}
      </div>
    </SidebarProvider>
  );
}
