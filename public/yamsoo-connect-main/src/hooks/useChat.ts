
import { useState, useCallback, useEffect } from "react";
import { useIsMobile } from "./use-mobile";
import type { DatabaseProfile, ChatGroup } from "@/types/chat";

export function useChat() {
  const [selectedConversation, setSelectedConversation] = useState<DatabaseProfile | null>(null);
  const [selectedGroup, setSelectedGroup] = useState<ChatGroup | null>(null);
  const [showSidebar, setShowSidebar] = useState(true);
  const isMobile = useIsMobile();

  // Adjust initial display based on mobile
  useEffect(() => {
    if (isMobile) {
      // On mobile, sidebar visibility depends on whether a conversation is selected
      setShowSidebar(selectedConversation === null);
    } else {
      // On desktop, always show sidebar
      setShowSidebar(true);
    }
  }, [isMobile, selectedConversation]);

  // When screen size changes, adjust display
  useEffect(() => {
    const handleResize = () => {
      if (!isMobile) {
        // On large screens, always show sidebar
        setShowSidebar(true);
      }
    };

    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, [isMobile]);

  const handleSelectConversation = useCallback((conversation: DatabaseProfile | null) => {
    setSelectedConversation(conversation);
    // On mobile, hide sidebar when conversation is selected
    if (isMobile && conversation) {
      setShowSidebar(false);
    }
  }, [isMobile]);

  const handleSelectGroup = useCallback((group: ChatGroup | null) => {
    setSelectedGroup(group);
    if (isMobile && group) {
      setShowSidebar(false);
    }
  }, [isMobile]);

  const toggleSidebar = useCallback(() => {
    setShowSidebar(prev => !prev);
  }, []);

  const showConversationList = useCallback(() => {
    setSelectedConversation(null);
    setShowSidebar(true);
  }, []);

  return {
    selectedConversation,
    setSelectedConversation: handleSelectConversation,
    selectedGroup,
    setSelectedGroup: handleSelectGroup,
    showSidebar,
    toggleSidebar,
    showConversationList,
    isMobile
  };
}
