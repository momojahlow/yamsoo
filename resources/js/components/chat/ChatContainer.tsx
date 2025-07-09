import { useEffect, useState } from "react";
import { User } from "@supabase/supabase-js";
import { useToast } from "@/hooks/use-toast";
import { ChatSidebar } from "./ChatSidebar";
import { ChatMainContent } from "./ChatMainContent";
import { MobileConversationList } from "./mobile/MobileConversationList";
import { useTypingStatus } from "@/hooks/useTypingStatus";
import { useMessages } from "@/hooks/useMessages";
import { useProfiles } from "@/hooks/useProfiles";
import { useChat } from "@/hooks/useChat";
import { supabase } from "@/integrations/supabase/client";
import { useSidebar } from "@/components/ui/sidebar";
import { 
  sendMessage, 
  addReaction
} from "@/services/messages";
import { 
  createGroup,
  addMemberToGroup,
  getGroupsByUserId as getGroups
} from "@/services/messages/groupApi";
import type { DatabaseProfile, ChatGroup } from "@/types/chat";

interface ChatContainerProps {
  currentUser: User | null;
  initialSelectedContactId?: string | null;
}

export function ChatContainer({ currentUser, initialSelectedContactId }: ChatContainerProps) {
  const { toast } = useToast();
  const { 
    selectedConversation, 
    setSelectedConversation,
    selectedGroup, 
    setSelectedGroup,
    showSidebar,
    toggleSidebar,
    showConversationList,
    isMobile
  } = useChat();
  
  const { state } = useSidebar();
  const isCollapsed = state === "collapsed";
  
  const [groups, setGroups] = useState<ChatGroup[]>([]);
  const { messages, conversations, isLoadingConversations, loadMessages, loadConversations } = useMessages(currentUser);
  const profiles = useProfiles(currentUser);

  const handleTyping = useTypingStatus(currentUser, selectedConversation);

  // Auto-select initial contact if provided
  useEffect(() => {
    if (initialSelectedContactId && profiles.length > 0 && !selectedConversation) {
      const contact = profiles.find(profile => profile.id === initialSelectedContactId);
      if (contact) {
        setSelectedConversation(contact);
      }
    }
  }, [initialSelectedContactId, profiles, selectedConversation, setSelectedConversation]);

  // Load user groups
  useEffect(() => {
    if (currentUser) {
      getGroups(currentUser.id).then(setGroups).catch(console.error);
    }
  }, [currentUser]);

  // Function to upload a file to Supabase Storage
  const uploadFile = async (file: File, userId: string): Promise<{ url: string, name: string }> => {
    const fileExt = file.name.split('.').pop();
    const fileName = `${userId}-${Math.random()}.${fileExt}`;
    const filePath = `message_attachments/${fileName}`;
    
    const { error: uploadError } = await supabase.storage
      .from('messages')
      .upload(filePath, file);

    if (uploadError) {
      console.error("Error uploading file:", uploadError);
      throw new Error(`Error uploading file: ${uploadError.message}`);
    }

    const { data } = supabase.storage
      .from('messages')
      .getPublicUrl(filePath);

    return { 
      url: data.publicUrl, 
      name: file.name 
    };
  };

  // Function to upload audio blob
  const uploadAudio = async (audioBlob: Blob, userId: string): Promise<{ url: string, duration: number }> => {
    // Create a file from the blob
    const audioFile = new File([audioBlob], `audio-${Date.now()}.webm`, { 
      type: audioBlob.type || 'audio/webm' 
    });
    
    const fileName = `${userId}-${Math.random()}.webm`;
    const filePath = `voice_messages/${fileName}`;
    
    const { error: uploadError } = await supabase.storage
      .from('messages')
      .upload(filePath, audioFile);

    if (uploadError) {
      console.error("Error uploading audio:", uploadError);
      throw new Error(`Error uploading audio: ${uploadError.message}`);
    }

    const { data } = supabase.storage
      .from('messages')
      .getPublicUrl(filePath);

    // Calculate audio duration (approximate)
    const duration = 0; // You may implement actual duration calculation if needed

    return { 
      url: data.publicUrl, 
      duration 
    };
  };

  const handleSendMessage = async (content: string, file?: File, audioBlob?: Blob) => {
    if (!currentUser || !selectedConversation) return;

    try {
      let attachmentUrl = null;
      let attachmentName = null;
      let audioUrl = null;
      let audioDuration = null;

      // Handle file upload if file is provided
      if (file) {
        const uploadResult = await uploadFile(file, currentUser.id);
        attachmentUrl = uploadResult.url;
        attachmentName = uploadResult.name;
      }

      // Handle audio upload if audioBlob is provided
      if (audioBlob) {
        const audioResult = await uploadAudio(audioBlob, currentUser.id);
        audioUrl = audioResult.url;
        audioDuration = audioResult.duration;
      }

      // Send message with attachment or audio info
      await sendMessage(
        content, 
        currentUser.id, 
        selectedConversation.id, 
        attachmentUrl, 
        attachmentName,
        audioUrl,
        audioDuration
      );
      
      await loadMessages();
      await loadConversations();
      
      toast({
        title: "Message envoyé",
        description: "Votre message a été envoyé avec succès",
        duration: 2000
      });
    } catch (error) {
      console.error("Error in handleSendMessage:", error);
      toast({
        title: "Erreur",
        description: "Impossible d'envoyer le message",
        variant: "destructive",
      });
    }
  };

  const handleCreateGroup = async (name: string, description: string, memberIds: string[]) => {
    if (!currentUser) return;

    try {
      await createGroup(name, description, memberIds, currentUser.id);
      const updatedGroups = await getGroups(currentUser.id);
      setGroups(updatedGroups);
      toast({
        title: "Groupe créé",
        description: "Le groupe a été créé avec succès",
      });
    } catch (error) {
      toast({
        title: "Erreur",
        description: "Impossible de créer le groupe",
        variant: "destructive",
      });
    }
  };

  const handleAddGroupMember = async (groupId: string, userId: string) => {
    try {
      await addMemberToGroup(groupId, userId);
      if (currentUser) {
        const updatedGroups = await getGroups(currentUser.id);
        setGroups(updatedGroups);
      }
      toast({
        title: "Membre ajouté",
        description: "Le membre a été ajouté au groupe",
      });
    } catch (error) {
      toast({
        title: "Erreur",
        description: "Impossible d'ajouter le membre",
        variant: "destructive",
      });
    }
  };

  const handleAddReaction = async (messageId: string, emoji: string) => {
    if (!currentUser) return;

    try {
      await addReaction(messageId, emoji, currentUser.id);
      await loadMessages();
    } catch (error) {
      toast({
        title: "Erreur",
        description: "Impossible d'ajouter la réaction",
        variant: "destructive",
      });
    }
  };

  return (
    <div className="flex flex-col h-[100dvh] bg-background overflow-hidden">
      <div className="flex flex-1 overflow-hidden">
        {/* For mobile: show conversation list when in list mode */}
        {isMobile && !selectedConversation && (
          <div className="w-full h-full">
            <MobileConversationList
              conversations={conversations}
              isLoading={isLoadingConversations}
              onSelectConversation={setSelectedConversation}
              currentUserId={currentUser?.id}
            />
          </div>
        )}
        
        {/* For desktop or mobile with sidebar */}
        {(showSidebar || (!isMobile && !selectedConversation)) && (
          <div className={`${isMobile ? 'w-full' : 'w-72 md:w-80'} flex-shrink-0 border-r h-full overflow-y-auto safe-area`}>
            <ChatSidebar 
              profiles={profiles}
              conversations={conversations}
              isLoadingConversations={isLoadingConversations}
              selectedConversation={selectedConversation}
              onSelectConversation={setSelectedConversation}
              groups={groups}
              currentUserId={currentUser?.id}
              onCreateGroup={handleCreateGroup}
              onAddGroupMember={handleAddGroupMember}
              onSelectGroup={setSelectedGroup}
              selectedGroup={selectedGroup}
              isCollapsed={isCollapsed}
            />
          </div>
        )}
        
        {/* Show chat view when conversation is selected */}
        {(selectedConversation || !isMobile) && (
          <div className="flex-1 flex flex-col h-full">
            <ChatMainContent
              selectedConversation={selectedConversation}
              currentUserId={currentUser?.id}
              messages={messages}
              conversations={conversations}
              onSendMessage={handleSendMessage}
              onAddReaction={handleAddReaction}
              onTyping={handleTyping}
              loadMessages={loadMessages}
              loadConversations={loadConversations}
              isMobile={isMobile}
              onSelectConversation={setSelectedConversation}
            />
          </div>
        )}
      </div>
    </div>
  );
}
