
import { useEffect, useRef, useState } from "react";
import { ScrollArea } from "@/components/ui/scroll-area";
import { ChatMessages } from "./ChatMessages";
import { ChatInput } from "./ChatInput";
import { ChatHeader } from "./ChatHeader";
import { ChatEmptyState } from "./ChatEmptyState";
import { markMessageAsRead, markAllMessagesAsRead } from "@/services/messages";
import type { DatabaseProfile, Message } from "@/types/chat";
import { Button } from "@/components/ui/button";
import { ChevronLeft, ChevronRight } from "lucide-react";

interface ChatMainContentProps {
  selectedConversation: DatabaseProfile | null;
  currentUserId: string | undefined;
  messages: Message[];
  conversations: Array<{ profile: DatabaseProfile, lastMessage: any, unreadCount: number }>;
  onSendMessage: (content: string, file?: File, audioBlob?: Blob) => Promise<void>;
  onAddReaction: (messageId: string, emoji: string) => Promise<void>;
  onTyping: (isTyping: boolean) => void;
  loadMessages: () => Promise<void>;
  loadConversations: () => Promise<void>;
  toggleSidebar?: () => void;
  isMobile?: boolean;
  onSelectConversation: (profile: DatabaseProfile | null) => void;
}

export function ChatMainContent({
  selectedConversation,
  currentUserId,
  messages,
  conversations,
  onSendMessage,
  onAddReaction,
  onTyping,
  loadMessages,
  loadConversations,
  isMobile,
  onSelectConversation
}: ChatMainContentProps) {
  const messagesEndRef = useRef<HTMLDivElement>(null);
  const scrollContainerRef = useRef<HTMLDivElement>(null);
  const [scrollPosition, setScrollPosition] = useState(0);

  // Filter messages for the selected conversation
  const conversationMessages = messages.filter(msg => 
    (selectedConversation && 
     ((msg.sender_id === selectedConversation.id && msg.receiver_id === currentUserId) || 
      (msg.sender_id === currentUserId && msg.receiver_id === selectedConversation.id)))
  );

  // Mark messages as read when user views them
  useEffect(() => {
    if (selectedConversation && currentUserId) {
      const unreadMessages = conversationMessages.filter(
        msg => !msg.read_at && msg.sender_id === selectedConversation.id
      );
      
      if (unreadMessages.length > 0) {
        markAllMessagesAsRead(selectedConversation.id, currentUserId)
          .then(() => {
            loadMessages();
            loadConversations();
          })
          .catch(error => {
            console.error("Erreur lors du marquage des messages comme lus:", error);
          });
      }
    }
  }, [conversationMessages, selectedConversation, currentUserId, loadMessages, loadConversations]);

  // Scroll to latest message when new messages are added
  useEffect(() => {
    if (messagesEndRef.current) {
      messagesEndRef.current.scrollIntoView({ behavior: 'smooth' });
    }
  }, [conversationMessages]);

  // Handle horizontal scroll navigation
  const scrollLeft = () => {
    if (scrollContainerRef.current) {
      scrollContainerRef.current.scrollBy({ left: -100, behavior: 'smooth' });
    }
  };

  const scrollRight = () => {
    if (scrollContainerRef.current) {
      scrollContainerRef.current.scrollBy({ left: 100, behavior: 'smooth' });
    }
  };

  // Update scroll position
  const handleScroll = () => {
    if (scrollContainerRef.current) {
      setScrollPosition(scrollContainerRef.current.scrollLeft);
    }
  };

  // Get previous and next conversations for navigation
  const currentIndex = selectedConversation 
    ? conversations.findIndex(conv => conv.profile.id === selectedConversation.id)
    : -1;

  const handleSelectConversation = (index: number) => {
    if (index >= 0 && index < conversations.length) {
      onSelectConversation(conversations[index].profile);
    }
  };

  if (!selectedConversation) {
    return <ChatEmptyState conversations={conversations} onSelectConversation={onSelectConversation} />;
  }

  const showBackButton = isMobile ? () => onSelectConversation(null) : undefined;

  return (
    <div className="flex flex-col h-full">
      {selectedConversation && (
        <ChatHeader 
          selectedConversation={selectedConversation}
          showConversationList={showBackButton}
        />
      )}
      
      <ScrollArea className="flex-1 p-4 bg-muted/20">
        <div className="space-y-4 pb-4">
          {conversationMessages.length === 0 ? (
            <div className="text-center text-muted-foreground py-8">
              Aucun message. Commencez une conversation !
            </div>
          ) : (
            <ChatMessages 
              messages={conversationMessages}
              currentUserId={currentUserId}
              onMarkAsRead={markMessageAsRead}
              onAddReaction={onAddReaction}
            />
          )}
          <div ref={messagesEndRef} />
        </div>
      </ScrollArea>
      
      <div className="p-3 bg-background border-t">
        <ChatInput 
          onSendMessage={onSendMessage}
          onTyping={onTyping}
          disabled={!selectedConversation}
        />
      </div>

      {/* Improved horizontal scroll navigation to match design */}
      <div className="bg-background border-t py-2">
        <div className="flex items-center px-2">
          <Button 
            variant="ghost" 
            size="icon" 
            onClick={scrollLeft}
            disabled={scrollPosition <= 0}
            className="flex-shrink-0 h-8 w-8"
            aria-label="Previous conversation"
          >
            <ChevronLeft size={18} />
          </Button>

          <div 
            ref={scrollContainerRef}
            className="flex overflow-x-auto py-1 scrollbar-hide"
            onScroll={handleScroll}
          >
            {conversations.map((conv, index) => {
              const isActive = selectedConversation.id === conv.profile.id;
              const firstName = conv.profile.first_name || "";
              const lastInitial = conv.profile.last_name?.charAt(0) || "";
              
              return (
                <Button
                  key={conv.profile.id}
                  variant={isActive ? "default" : "ghost"}
                  className={`
                    flex-shrink-0 mx-1 px-3 py-1 h-10 min-w-[80px]
                    ${isActive ? 'bg-primary text-primary-foreground' : 'bg-muted/30'}
                    rounded-full text-sm font-medium
                  `}
                  onClick={() => handleSelectConversation(index)}
                >
                  <span className="truncate">
                    {firstName} {lastInitial}
                   </span>
                  {conv.unreadCount > 0 && (
                    <span className="ml-1.5 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                      {conv.unreadCount}
                    </span>
                  )}
                </Button>
              );
            })}
          </div>

          <Button 
            variant="ghost" 
            size="icon"
            onClick={scrollRight}
            className="flex-shrink-0 h-8 w-8"
            aria-label="Next conversation"
          >
            <ChevronRight size={18} />
          </Button>
        </div>
      </div>
    </div>
  );
}
