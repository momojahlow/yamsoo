
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { X } from 'lucide-react';

interface User {
  id: number;
  name: string;
  email: string;
  profile?: {
    avatar_url?: string;
  };
}

interface Props {
  user: User;
  onClose: () => void;
}

export function AddFamilyRelation({ user, onClose }: Props) {
  const [relationType, setRelationType] = useState('');
  const [message, setMessage] = useState('');

  const relationTypes = [
    'Parent',
    'Enfant',
    'Frère/Sœur',
    'Grand-parent',
    'Oncle/Tante',
    'Cousin/Cousine',
    'Conjoint',
    'Autre'
  ];

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    // Créer un formulaire et le soumettre
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/suggestions';

    const typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'type';
    typeInput.value = relationType;

    const messageInput = document.createElement('input');
    messageInput.type = 'hidden';
    messageInput.name = 'message';
    messageInput.value = message;

    const userIdInput = document.createElement('input');
    userIdInput.type = 'hidden';
    userIdInput.name = 'suggested_user_id';
    userIdInput.value = user.id.toString();

    form.appendChild(typeInput);
    form.appendChild(messageInput);
    form.appendChild(userIdInput);

    document.body.appendChild(form);
    form.submit();
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <Card className="w-full max-w-md mx-4">
        <CardHeader className="flex flex-row items-center justify-between">
          <CardTitle>Ajouter une Relation</CardTitle>
          <Button variant="ghost" size="sm" onClick={onClose}>
            <X className="w-4 h-4" />
          </Button>
        </CardHeader>
        <CardContent>
          <div className="flex items-center gap-4 mb-6">
            <div className="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
              {user.profile?.avatar_url ? (
                <img
                  src={user.profile.avatar_url}
                  alt={user.name}
                  className="w-12 h-12 rounded-full object-cover"
                />
              ) : (
                <span className="text-lg font-semibold text-gray-600">
                  {user.name.charAt(0).toUpperCase()}
                </span>
              )}
            </div>
            <div>
              <h3 className="font-semibold">{user.name}</h3>
              <p className="text-sm text-gray-600 dark:text-gray-400">
                {user.email}
              </p>
            </div>
          </div>

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-sm font-medium mb-2">
                Type de Relation
              </label>
              <Select value={relationType} onValueChange={setRelationType}>
                <SelectTrigger>
                  <SelectValue placeholder="Sélectionner un type" />
                </SelectTrigger>
                <SelectContent>
                  {relationTypes.map((type) => (
                    <SelectItem key={type} value={type}>
                      {type}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div>
              <label className="block text-sm font-medium mb-2">
                Message (optionnel)
              </label>
              <Textarea
                value={message}
                onChange={(e) => setMessage(e.target.value)}
                placeholder="Ajoutez un message personnel..."
                rows={3}
              />
            </div>

            <div className="flex gap-3 pt-4">
              <Button
                type="button"
                variant="outline"
                onClick={onClose}
                className="flex-1"
              >
                Annuler
              </Button>
              <Button
                type="submit"
                disabled={!relationType}
                className="flex-1"
              >
                Envoyer la Suggestion
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
