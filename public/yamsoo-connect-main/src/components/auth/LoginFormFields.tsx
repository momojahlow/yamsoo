
import { useState } from "react";
import { Input } from "@/components/ui/input";
import { Eye, EyeOff } from "lucide-react";
import { Button } from "@/components/ui/button";

interface LoginFormFieldsProps {
  email: string;
  setEmail: (email: string) => void;
  password: string;
  setPassword: (password: string) => void;
  onForgotPassword: () => void;
}

export const LoginFormFields = ({
  email,
  setEmail,
  password,
  setPassword,
  onForgotPassword,
}: LoginFormFieldsProps) => {
  const [showPassword, setShowPassword] = useState(false);

  return (
    <>
      <div className="space-y-1">
        <label htmlFor="email" className="block text-sm font-medium text-gray-700">
          Email
        </label>
        <Input
          id="email"
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
          className="w-full"
          placeholder="exemple@email.com"
          autoComplete="email"
        />
      </div>
      
      <div className="space-y-1">
        <label htmlFor="password" className="block text-sm font-medium text-gray-700">
          Mot de passe
        </label>
        <div className="relative">
          <Input
            id="password"
            type={showPassword ? "text" : "password"}
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            className="w-full pr-10"
            placeholder="••••••••"
            autoComplete="current-password"
          />
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onClick={() => setShowPassword(!showPassword)}
            className="absolute right-2 top-1/2 -translate-y-1/2 h-auto p-1 text-gray-500 hover:text-gray-700 hover:bg-transparent"
            aria-label={showPassword ? "Masquer le mot de passe" : "Afficher le mot de passe"}
          >
            {showPassword ? (
              <EyeOff className="h-4 w-4" />
            ) : (
              <Eye className="h-4 w-4" />
            )}
          </Button>
        </div>
      </div>

      <div className="flex justify-end">
        <Button
          type="button"
          variant="link"
          className="text-sm text-terracotta hover:text-terracotta/90"
          onClick={onForgotPassword}
        >
          Mot de passe oublié ?
        </Button>
      </div>
    </>
  );
};
