import React, { useState, useRef, useEffect } from 'react';
import { Paperclip, Smile, Send, X, File } from 'lucide-react';
import EmojiPicker from './EmojiPicker';

interface MessageInputProps {
    value: string;
    onChange: (value: string) => void;
    onSend: () => void;
    onFileSelect: (file: File) => void;
    selectedFile: File | null;
    onRemoveFile: () => void;
    disabled?: boolean;
    placeholder?: string;
    replyTo?: {
        id: number;
        content: string;
        user_name: string;
    } | null;
    onCancelReply?: () => void;
}

export default function MessageInput({
    value,
    onChange,
    onSend,
    onFileSelect,
    selectedFile,
    onRemoveFile,
    disabled = false,
    placeholder = "Écrivez votre message...",
    replyTo,
    onCancelReply
}: MessageInputProps) {
    const [showEmojiPicker, setShowEmojiPicker] = useState(false);
    const textareaRef = useRef<HTMLTextAreaElement>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);

    // Auto-resize textarea
    useEffect(() => {
        if (textareaRef.current) {
            textareaRef.current.style.height = 'auto';
            textareaRef.current.style.height = `${textareaRef.current.scrollHeight}px`;
        }
    }, [value]);

    const handleKeyPress = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (!disabled && (value.trim() || selectedFile)) {
                onSend();
            }
        }
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            onFileSelect(file);
        }
    };

    const handleEmojiSelect = (emoji: string) => {
        onChange(value + emoji);
        setShowEmojiPicker(false);
        textareaRef.current?.focus();
    };

    const getFileIcon = (file: File) => {
        if (file.type.startsWith('image/')) return '🖼️';
        if (file.type.startsWith('video/')) return '🎥';
        if (file.type.startsWith('audio/')) return '🎵';
        return '📄';
    };

    return (
        <div className="bg-white border-t border-gray-200">
            {/* Zone de réponse */}
            {replyTo && (
                <div className="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <div className="flex items-center justify-between">
                        <div className="flex-1 min-w-0">
                            <p className="text-sm text-gray-600 mb-1">
                                Répondre à <span className="font-medium text-orange-600">{replyTo.user_name}</span>
                            </p>
                            <p className="text-sm text-gray-800 truncate">{replyTo.content}</p>
                        </div>
                        <button
                            onClick={onCancelReply}
                            className="ml-3 p-1 text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <X className="w-4 h-4" />
                        </button>
                    </div>
                </div>
            )}

            {/* Fichier sélectionné */}
            {selectedFile && (
                <div className="px-4 py-3 bg-blue-50 border-b border-blue-200">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-3">
                            <span className="text-2xl">{getFileIcon(selectedFile)}</span>
                            <div>
                                <p className="text-sm font-medium text-blue-900">{selectedFile.name}</p>
                                <p className="text-xs text-blue-600">
                                    {(selectedFile.size / 1024 / 1024).toFixed(2)} MB
                                </p>
                            </div>
                        </div>
                        <button
                            onClick={onRemoveFile}
                            className="p-1 text-blue-400 hover:text-blue-600 transition-colors"
                        >
                            <X className="w-4 h-4" />
                        </button>
                    </div>
                </div>
            )}

            {/* Zone de saisie */}
            <div className="p-4">
                <div className="flex items-end space-x-3">
                    <div className="flex-1 relative">
                        <textarea
                            ref={textareaRef}
                            value={value}
                            onChange={(e) => onChange(e.target.value)}
                            onKeyPress={handleKeyPress}
                            placeholder={placeholder}
                            disabled={disabled}
                            className="w-full px-4 py-3 pr-20 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none max-h-32 transition-all duration-200"
                            rows={1}
                            style={{ minHeight: '48px' }}
                        />
                        
                        <div className="absolute right-3 bottom-3 flex items-center space-x-1">
                            <button
                                type="button"
                                onClick={() => fileInputRef.current?.click()}
                                disabled={disabled}
                                className="p-1.5 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-all duration-200 disabled:opacity-50"
                            >
                                <Paperclip className="w-5 h-5" />
                            </button>
                            <button
                                type="button"
                                onClick={() => setShowEmojiPicker(!showEmojiPicker)}
                                disabled={disabled}
                                className="p-1.5 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-all duration-200 disabled:opacity-50"
                            >
                                <Smile className="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <button
                        onClick={onSend}
                        disabled={disabled || (!value.trim() && !selectedFile)}
                        className="p-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-2xl hover:from-orange-600 hover:to-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105 active:scale-95"
                    >
                        {disabled ? (
                            <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                        ) : (
                            <Send className="w-5 h-5" />
                        )}
                    </button>
                </div>

                <input
                    ref={fileInputRef}
                    type="file"
                    onChange={handleFileChange}
                    className="hidden"
                    accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.txt"
                />

                {showEmojiPicker && (
                    <div className="relative">
                        <EmojiPicker
                            onEmojiSelect={handleEmojiSelect}
                            onClose={() => setShowEmojiPicker(false)}
                        />
                    </div>
                )}
            </div>
        </div>
    );
}
