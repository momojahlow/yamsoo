
import { useEffect } from "react";
import { AlertTriangle, ArrowLeft, Home } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useIsMobile } from "@/hooks/use-mobile";

const NotFound = () => {
  const isMobile = useIsMobile();

  useEffect(() => {
    console.error(
      "404 Error: User attempted to access non-existent route:",
      window.location.pathname
    );
  }, [window.location.pathname]);

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100 safe-area">
      <div className="text-center space-y-6 p-6 max-w-md">
        <div className="flex justify-center">
          <AlertTriangle className={`${isMobile ? 'h-12 w-12' : 'h-16 w-16'} text-yellow-500`} />
        </div>

        <div className="space-y-2">
          <h1 className={`${isMobile ? 'text-3xl' : 'text-4xl'} font-bold text-gray-900`}>404</h1>
          <p className={`${isMobile ? 'text-lg' : 'text-xl'} text-gray-600`}>
            Oups ! Cette page n'existe pas
          </p>
          <p className="text-sm text-gray-500 break-words">
            La page que vous recherchez n'a pas été trouvée :
            <span className="block mt-1">{window.location.pathname}</span>
          </p>
        </div>

        <div className="flex flex-col sm:flex-row gap-4 justify-center pt-4">
          <Button
            variant="outline"
            onClick={() => window.history.back()}
            className="flex items-center gap-2"
            size={isMobile ? "sm" : "default"}
          >
            <ArrowLeft className="h-4 w-4" />
            Retour
          </Button>

          <Button
            variant="default"
            onClick={() => window.location.href = '/'}
            className="flex items-center gap-2"
            size={isMobile ? "sm" : "default"}
          >
            <Home className="h-4 w-4" />
            Retour à l'accueil
          </Button>
        </div>
      </div>
    </div>
  );
};

export default NotFound;
