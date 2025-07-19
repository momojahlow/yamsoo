import React from 'react';

interface EmojiPickerProps {
    onEmojiSelect: (emoji: string) => void;
    onClose: () => void;
}

export default function EmojiPicker({ onEmojiSelect, onClose }: EmojiPickerProps) {
    const emojis = [
        '😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣',
        '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰',
        '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜',
        '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳', '😏',
        '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣',
        '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠',
        '😡', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨',
        '😰', '😥', '😓', '🤗', '🤔', '🤭', '🤫', '🤥',
        '😶', '😐', '😑', '😬', '🙄', '😯', '😦', '😧',
        '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐',
        '🥴', '🤢', '🤮', '🤧', '😷', '🤒', '🤕', '🤑',
        '🤠', '😈', '👿', '👹', '👺', '🤡', '💩', '👻',
        '💀', '☠️', '👽', '👾', '🤖', '🎃', '😺', '😸',
        '😹', '😻', '😼', '😽', '🙀', '😿', '😾', '❤️',
        '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎',
        '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘',
        '💝', '💟', '👍', '👎', '👌', '🤌', '🤏', '✌️',
        '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕',
        '👇', '☝️', '👋', '🤚', '🖐️', '✋', '🖖', '👏',
        '🙌', '🤝', '🙏', '✍️', '💪', '🦾', '🦿', '🦵'
    ];

    return (
        <div className="absolute bottom-full right-0 mb-2 bg-white border border-gray-200 rounded-lg shadow-lg p-4 w-80 max-h-60 overflow-y-auto z-50">
            <div className="flex items-center justify-between mb-3">
                <h3 className="text-sm font-medium text-gray-900">Émojis</h3>
                <button
                    onClick={onClose}
                    className="text-gray-400 hover:text-gray-600"
                >
                    ×
                </button>
            </div>
            
            <div className="grid grid-cols-8 gap-2">
                {emojis.map((emoji, index) => (
                    <button
                        key={index}
                        onClick={() => onEmojiSelect(emoji)}
                        className="p-2 text-lg hover:bg-gray-100 rounded transition-colors"
                    >
                        {emoji}
                    </button>
                ))}
            </div>
        </div>
    );
}
