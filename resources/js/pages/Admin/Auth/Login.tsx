import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Shield, Eye, EyeOff } from 'lucide-react';

interface Props {
  errors: Record<string, string>;
  message?: string;
}

export default function AdminLogin({ errors, message }: Props) {
  const [showPassword, setShowPassword] = useState(false);
  
  const { data, setData, post, processing } = useForm({
    email: '',
    password: '',
    remember: false,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post('/admin/login');
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
      <Head title="Connexion Administration" />
      
      <Card className="w-full max-w-md shadow-xl">
        <CardHeader className="text-center space-y-4">
          <div className="mx-auto w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center">
            <Shield className="w-8 h-8 text-white" />
          </div>
          <div>
            <CardTitle className="text-2xl font-bold text-gray-900">
              Administration Yamsoo
            </CardTitle>
            <p className="text-gray-600 mt-2">
              Connectez-vous à votre espace d'administration
            </p>
          </div>
        </CardHeader>
        
        <CardContent>
          {message && (
            <Alert className="mb-6">
              <AlertDescription>{message}</AlertDescription>
            </Alert>
          )}
          
          <form onSubmit={handleSubmit} className="space-y-6">
            <div className="space-y-2">
              <Label htmlFor="email">Adresse email</Label>
              <Input
                id="email"
                type="email"
                value={data.email}
                onChange={(e) => setData('email', e.target.value)}
                placeholder="admin@exemple.com"
                className={errors.email ? 'border-red-500' : ''}
                required
              />
              {errors.email && (
                <p className="text-sm text-red-600">{errors.email}</p>
              )}
            </div>

            <div className="space-y-2">
              <Label htmlFor="password">Mot de passe</Label>
              <div className="relative">
                <Input
                  id="password"
                  type={showPassword ? 'text' : 'password'}
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                  placeholder="Votre mot de passe"
                  className={errors.password ? 'border-red-500' : ''}
                  required
                />
                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  className="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                  onClick={() => setShowPassword(!showPassword)}
                >
                  {showPassword ? (
                    <EyeOff className="h-4 w-4 text-gray-400" />
                  ) : (
                    <Eye className="h-4 w-4 text-gray-400" />
                  )}
                </Button>
              </div>
              {errors.password && (
                <p className="text-sm text-red-600">{errors.password}</p>
              )}
            </div>

            <div className="flex items-center space-x-2">
              <Checkbox
                id="remember"
                checked={data.remember}
                onCheckedChange={(checked) => setData('remember', checked as boolean)}
              />
              <Label htmlFor="remember" className="text-sm text-gray-600">
                Se souvenir de moi
              </Label>
            </div>

            <Button
              type="submit"
              className="w-full bg-blue-600 hover:bg-blue-700"
              disabled={processing}
            >
              {processing ? 'Connexion...' : 'Se connecter'}
            </Button>
          </form>

          <div className="mt-6 text-center">
            <p className="text-xs text-gray-500">
              Accès réservé aux administrateurs autorisés
            </p>
          </div>
        </CardContent>
      </Card>

      {/* Informations de test en développement */}
      {process.env.NODE_ENV === 'development' && (
        <div className="fixed bottom-4 right-4 bg-yellow-100 border border-yellow-400 rounded-lg p-4 max-w-sm">
          <h4 className="font-semibold text-yellow-800 mb-2">Comptes de test :</h4>
          <div className="text-sm text-yellow-700 space-y-1">
            <p><strong>Email :</strong> admin@exemple.com</p>
            <p><strong>Mot de passe :</strong> password</p>
          </div>
        </div>
      )}
    </div>
  );
}
