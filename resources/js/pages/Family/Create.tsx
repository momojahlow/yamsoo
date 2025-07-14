import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { SidebarProvider } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/app/Sidebar";
import { useIsMobile } from "@/hooks/use-mobile";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ArrowLeft } from 'lucide-react';

export default function FamilyCreate() {
  const [name, setName] = useState('');
  const [description, setDescription] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const isMobile = useIsMobile();

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);

    router.post('/families', {
      name,
      description,
    }, {
      onSuccess: () => {
        // Redirection vers la page famille après création
        router.visit('/famille');
      },
      onError: (errors) => {
        console.error('Erreur lors de la création de la famille:', errors);
      },
      onFinish: () => {
        setIsSubmitting(false);
      }
    });
  };

  return (
    <SidebarProvider>
      <div className="min-h-screen flex w-full bg-background">
        <AppSidebar />
        <main className="flex-1 p-4 md:p-8 md:ml-16 pb-20 md:pb-8">
          <Head title="Créer une famille" />

          <div className="max-w-2xl mx-auto">
            {/* Header */}
            <div className="mb-8">
              <Button
                variant="ghost"
                onClick={() => router.visit('/famille')}
                className="mb-4"
              >
                <ArrowLeft className="w-4 h-4 mr-2" />
                Retour à la famille
              </Button>
              <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                Créer une nouvelle famille
              </h1>
              <p className="text-gray-600 dark:text-gray-400 mt-2">
                Créez votre espace familial pour connecter vos proches
              </p>
            </div>

            {/* Formulaire */}
            <Card>
              <CardHeader>
                <CardTitle>Informations de la famille</CardTitle>
              </CardHeader>
              <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                  <div>
                    <Label htmlFor="name">Nom de la famille *</Label>
                    <Input
                      id="name"
                      type="text"
                      value={name}
                      onChange={(e) => setName(e.target.value)}
                      placeholder="Ex: Famille Martin"
                      required
                      className="mt-1"
                    />
                  </div>

                  <div>
                    <Label htmlFor="description">Description (optionnel)</Label>
                    <Textarea
                      id="description"
                      value={description}
                      onChange={(e) => setDescription(e.target.value)}
                      placeholder="Décrivez votre famille, ses traditions, etc."
                      rows={4}
                      className="mt-1"
                    />
                  </div>

                  <div className="flex gap-3 pt-4">
                    <Button
                      type="button"
                      variant="outline"
                      onClick={() => router.visit('/famille')}
                      className="flex-1"
                      disabled={isSubmitting}
                    >
                      Annuler
                    </Button>
                    <Button
                      type="submit"
                      disabled={!name.trim() || isSubmitting}
                      className="flex-1"
                    >
                      {isSubmitting ? "Création..." : "Créer la famille"}
                    </Button>
                  </div>
                </form>
              </CardContent>
            </Card>
          </div>
        </main>
        {isMobile && <MobileNavBar />}
      </div>
    </SidebarProvider>
  );
}
