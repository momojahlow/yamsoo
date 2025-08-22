import React, { useState, useEffect, useRef } from 'react';
import { ArrowLeft, Phone, Video, MoreVertical, Paperclip, Smile, Send, Image, File } from 'lucide-react';
import MessageBubble from './MessageBubble';
import EmojiPicker from './EmojiPicker';
import { useForm } from '@inertiajs/react';

interface User {
    id: number;
    name: string;
    avatar?: string;
}

interface Conversation {
    id: number | null;
    name: string;
    type: 'private' | 'group';
    avatar?: string;
    is_online?: boolean;
    other_participant_id?: number;
    is_new?: boolean;
}

interface Message {
    id: number;
    content: string;
    type: 'text' | 'image' | 'file' | 'audio' | 'video';
    file_url?: string;
    file_name?: string;
    file_size?: string;
    created_at: string;
    is_edited: boolean;
    edited_at?: string;
    user: User;
    reply_to?: {
        id: number;
        content: string;
        user_name: string;
    };
    reactions: Array<{
        emoji: string;
        count: number;
        users: string[];
    }>;
}

interface ChatAreaProps {
    conversation: Conversation;
    messages: Message[];
    user: User;
    onBack?: () => void;
}

export default function ChatArea({ conversation, messages, user, onBack }: ChatAreaProps) {
    const [showEmojiPicker, setShowEmojiPicker] = useState(false);
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [replyTo, setReplyTo] = useState<Message | null>(null);

    const messagesEndRef = useRef<HTMLDivElement>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);
    const textareaRef = useRef<HTMLTextAreaElement>(null);

    // Utiliser Inertia pour envoyer des messages
    const { data, setData, post, processing, errors, reset } = useForm({
        conversation_id: conversation.id,
        message: '',
    });

    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    const handleSendMessage = (e: React.FormEvent) => {
        e.preventDefault();

        if (!data.message.trim() || processing) return;

        post('/messagerie/send', {
            onSuccess: () => {
                reset('message');
                setSelectedFile(null);
                setReplyTo(null);

                if (fileInputRef.current) {
                    fileInputRef.current.value = '';
                }

                // Ajuster la hauteur du textarea
                if (textareaRef.current) {
                    textareaRef.current.style.height = '48px';
                }
            },
            onError: (errors) => {
                console.error('Erreur lors de l\'envoi du message:', errors);
            }
        });
    };

    const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setSelectedFile(file);
        }
    };

    const handleEmojiSelect = (emoji: string) => {
        setData('message', data.message + emoji);
        setShowEmojiPicker(false);
        textareaRef.current?.focus();
    };

    const handleKeyPress = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleSendMessage(e);
        }
    };

    const getInitials = (name: string) => {
        return name
            .split(' ')
            .map(word => word.charAt(0))
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };

    return (
        <div className="flex flex-col h-full bg-white">
            {/* Header du chat */}
            <div className="flex items-center justify-between p-4 border-b border-gray-200 bg-white">
                <div className="flex items-center">
                    {onBack && (
                        <button
                            onClick={onBack}
                            className="mr-3 p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            <ArrowLeft className="w-5 h-5" />
                        </button>
                    )}

                    {/* Avatar et info */}
                    <div className="flex items-center">
                        {conversation.avatar ? (
                            <img
                                src={conversation.avatar}
                                alt={conversation.name}
                                className="w-10 h-10 rounded-full object-cover"
                            />
                        ) : (
                            <div className="w-10 h-10 rounded-full bg-gradient-to-br from-orange-100 to-red-100 flex items-center justify-center text-orange-600 font-medium text-sm">
                                {getInitials(conversation.name)}
                            </div>
                        )}

                        <div className="ml-3">
                            <h2 className="font-medium text-gray-900">{conversation.name}</h2>
                            {conversation.is_online && (
                                <p className="text-sm text-green-600">En ligne</p>
                            )}
                        </div>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex items-center space-x-2">
                    <button className="p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors">
                        <Phone className="w-5 h-5" />
                    </button>
                    <button className="p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors">
                        <Video className="w-5 h-5" />
                    </button>
                    <button className="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        <MoreVertical className="w-5 h-5" />
                    </button>
                </div>
            </div>

            {/* Zone des messages */}
            <div className="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
                {messages.length === 0 ? (
                    <div className="flex flex-col items-center justify-center h-full text-gray-500 px-4">
                        <div className="w-20 h-20 bg-gradient-to-br from-orange-100 to-red-100 rounded-full flex items-center justify-center mb-6 shadow-lg">
                            <Send className="w-10 h-10 text-orange-500" />
                        </div>
                        <h3 className="text-xl font-semibold mb-3 text-gray-800">
                            {conversation.is_new ? 'Nouvelle conversation' : 'Aucun message'}
                        </h3>
                        <p className="text-sm text-center max-w-sm leading-relaxed">
                            {conversation.is_new
                                ? `Commencez une nouvelle conversation avec ${conversation.name}. Envoyez votre premier message !`
                                : `Commencez la conversation en envoyant le premier message à ${conversation.name}.`
                            }
                        </p>
                        {conversation.is_new && (
                            <div className="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <p className="text-sm text-blue-700 text-center">
                                    💡 Cette conversation sera créée dès que vous enverrez votre premier message
                                </p>
                            </div>
                        )}
                    </div>
                ) : (
                    <>
                        {messages.map((message) => (
                            <MessageBubble
                                key={message.id}
                                message={message}
                                isOwn={message.user.id === user.id}
                                onReply={() => setReplyTo(message)}
                            />
                        ))}
                        <div ref={messagesEndRef} />
                    </>
                )}
            </div>

            {/* Zone de réponse */}
            {replyTo && (
                <div className="px-4 py-2 bg-gray-100 border-t border-gray-200">
                    <div className="flex items-center justify-between">
                        <div className="flex-1">
                            <p className="text-sm text-gray-600">
                                Répondre à <span className="font-medium">{replyTo.user.name}</span>
                            </p>
                            <p className="text-sm text-gray-800 truncate">{replyTo.content}</p>
                        </div>
                        <button
                            onClick={() => setReplyTo(null)}
                            className="ml-2 text-gray-400 hover:text-gray-600"
                        >
                            ×
                        </button>
                    </div>
                </div>
            )}

            {/* Zone de saisie */}
            <div className="p-4 bg-white border-t border-gray-200">
                {selectedFile && (
                    <div className="mb-3 p-3 bg-gray-50 rounded-lg flex items-center justify-between">
                        <div className="flex items-center">
                            <File className="w-5 h-5 text-gray-500 mr-2" />
                            <span className="text-sm text-gray-700">{selectedFile.name}</span>
                        </div>
                        <button
                            onClick={() => setSelectedFile(null)}
                            className="text-gray-400 hover:text-gray-600"
                        >
                            ×
                        </button>
                    </div>
                )}

                <form onSubmit={handleSendMessage} className="flex items-end space-x-3">
                    <div className="flex-1 relative">
                        <textarea
                            ref={textareaRef}
                            value={data.message}
                            onChange={(e) => setData('message', e.target.value)}
                            onKeyPress={handleKeyPress}
                            placeholder="Écrivez votre message..."
                            className="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none max-h-32"
                            rows={1}
                            style={{ minHeight: '48px' }}
                        />

                        <div className="absolute right-3 bottom-3 flex items-center space-x-1">
                            <button
                                type="button"
                                onClick={() => fileInputRef.current?.click()}
                                className="p-1 text-gray-400 hover:text-orange-600 transition-colors"
                            >
                                <Paperclip className="w-5 h-5" />
                            </button>
                            <button
                                type="button"
                                onClick={() => setShowEmojiPicker(!showEmojiPicker)}
                                className="p-1 text-gray-400 hover:text-orange-600 transition-colors"
                            >
                                <Smile className="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        disabled={(!data.message.trim() && !selectedFile) || processing}
                        className="p-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105"
                    >
                        {processing ? (
                            <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                        ) : (
                            <Send className="w-5 h-5" />
                        )}
                    </button>
                </form>

                <input
                    ref={fileInputRef}
                    type="file"
                    onChange={handleFileSelect}
                    className="hidden"
                    accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.txt"
                />

                {showEmojiPicker && (
                    <EmojiPicker
                        onEmojiSelect={handleEmojiSelect}
                        onClose={() => setShowEmojiPicker(false)}
                    />
                )}
            </div>
        </div>
    );
}
