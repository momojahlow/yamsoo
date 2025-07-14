
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

import { X, UserPlus } from 'lucide-react';
import { router } from '@inertiajs/react';
import { useToast } from '@/hooks/use-toast';

interface RelationshipType {
  id: number;
  code: string;
  name: string;
  name_fr: string;
  gender: string;
  requires_mother_name: boolean;
}

interface Props {
  relationshipTypes: RelationshipType[];
  onClose: () => void;
}

export function AddFamilyRelation({ relationshipTypes, onClose }: Props) {
  console.log('relationshipTypes', relationshipTypes);
  const [email, setEmail] = useState('');
  const [relationshipTypeId, setRelationshipTypeId] = useState('');
  const [motherName, setMotherName] = useState('');
  const [message, setMessage] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const { toast } = useToast();

  // Add null/undefined check
  const safeRelationshipTypes = relationshipTypes || [];
  const selectedRelationType = safeRelationshipTypes.find(rt => rt.id.toString() === relationshipTypeId);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    if (!email || !relationshipTypeId) {
      toast({
        variant: "destructive",
        title: "Erreur",
        description: "Veuillez remplir tous les champs obligatoires.",
      });
      return;
    }

    if (selectedRelationType?.requires_mother_name && !motherName) {
      toast({
        variant: "destructive",
        title: "Nom de la mère requis",
        description: "Cette relation nécessite le nom de la mère pour validation.",
      });
      return;
    }

    setIsSubmitting(true);

    router.post('/family-relations', {
      email,
      relationship_type_id: relationshipTypeId,
      mother_name: motherName,
      message,
    }, {
      onSuccess: () => {
        toast({
          title: "Demande envoyée !",
          description: "Votre demande de relation familiale a été envoyée avec succès.",
        });
        onClose();
      },
      onError: (errors) => {
        console.error('Erreur lors de l\'envoi de la demande:', errors);
        toast({
          variant: "destructive",
          title: "Erreur",
          description: errors.email || errors.relationship_type_id || "Une erreur s'est produite lors de l'envoi de la demande.",
        });
      },
      onFinish: () => {
        setIsSubmitting(false);
      }
    });
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
          <div className="flex items-center gap-3 mb-6">
            <UserPlus className="w-8 h-8 text-blue-600" />
            <div>
              <h3 className="font-semibold text-gray-900 dark:text-white">Ajouter une relation familiale</h3>
              <p className="text-sm text-gray-600 dark:text-gray-400">
                Créer une demande de relation avec un membre de votre famille
              </p>
            </div>
          </div>

          <form onSubmit={handleSubmit} className="space-y-4">
            {/* Email de la personne */}
            <div>
              <Label htmlFor="email" className="text-gray-700 dark:text-gray-300">Adresse email de la personne *</Label>
              <Input
                id="email"
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="exemple@email.com"
                required
                className="mt-1"
              />
            </div>

            {/* Type de relation */}
            <div>
              <Label htmlFor="relationship_type" className="text-gray-700 dark:text-gray-300">Type de relation *</Label>
              <select
                id="relationship_type"
                value={relationshipTypeId}
                onChange={(e) => setRelationshipTypeId(e.target.value)}
                className="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                required
              >
                <option value="">Sélectionner le type de relation</option>
                {safeRelationshipTypes.map((type) => (
                  <option key={type.id} value={type.id.toString()}>
                    {type.name_fr}
                  </option>
                ))}
              </select>
            </div>

            {/* Nom de la mère (si requis) */}
            {selectedRelationType?.requires_mother_name && (
              <div>
                <Label htmlFor="mother_name" className="text-gray-700 dark:text-gray-300">Nom de la mère *</Label>
                <Input
                  id="mother_name"
                  type="text"
                  value={motherName}
                  onChange={(e) => setMotherName(e.target.value)}
                  placeholder="Nom de la mère (requis pour cette relation)"
                  required
                  className="mt-1"
                />
                <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                  Le nom de la mère est requis pour valider cette relation familiale.
                </p>
              </div>
            )}

            {/* Message */}
            <div>
              <Label htmlFor="message" className="text-gray-700 dark:text-gray-300">Message (optionnel)</Label>
              <Textarea
                id="message"
                value={message}
                onChange={(e) => setMessage(e.target.value)}
                placeholder="Ajoutez un message personnel pour expliquer votre demande..."
                rows={3}
                className="mt-1"
              />
            </div>

            <div className="flex gap-3 pt-4">
              <Button
                type="button"
                variant="outline"
                onClick={onClose}
                className="flex-1"
                disabled={isSubmitting}
              >
                Annuler
              </Button>
              <Button
                type="submit"
                disabled={!email || !relationshipTypeId || isSubmitting}
                className="flex-1 bg-blue-600 hover:bg-blue-700"
              >
                {isSubmitting ? "Envoi..." : "Envoyer la demande"}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
