
import { useRef } from 'react';
import { useToast } from "@/hooks/use-toast";

export function useFamilyTreePrint() {
  const printAreaRef = useRef<HTMLDivElement>(null);
  const { toast } = useToast();

  const handlePrint = () => {
    // Notify user that printing is starting
    toast({
      title: "Préparation de l'impression",
      description: "L'arbre généalogique est en cours de préparation pour l'impression...",
    });

    // Use setTimeout to allow the toast to appear before printing
    setTimeout(() => {
      const printContent = printAreaRef.current;
      if (!printContent) return;

      // Create a new window for printing
      const printWindow = window.open('', '_blank');
      if (!printWindow) {
        toast({
          title: "Erreur",
          description: "Impossible d'ouvrir la fenêtre d'impression. Veuillez vérifier les paramètres de votre navigateur.",
          variant: "destructive",
        });
        return;
      }

      // Get the styles from the current document
      const styles = Array.from(document.styleSheets)
        .filter(styleSheet => {
          try {
            return !styleSheet.href || styleSheet.href.startsWith(window.location.origin);
          } catch (e) {
            return false;
          }
        })
        .map(styleSheet => {
          try {
            return Array.from(styleSheet.cssRules)
              .map(rule => rule.cssText)
              .join('\n');
          } catch (e) {
            console.log('Error accessing cssRules', e);
            return '';
          }
        })
        .join('\n');

      // Write the content to the new window
      printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <title>Arbre Généalogique</title>
          <style>${styles}</style>
          <style>
            body {
              margin: 0;
              padding: 20px;
              background: white;
            }
            .print-container {
              width: 100%;
              background: white;
            }
            @media print {
              body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
              }
            }
          </style>
        </head>
        <body>
          <div class="print-container">
            <h1 style="text-align: center; margin-bottom: 20px;">Mon Arbre Généalogique</h1>
            ${printContent.outerHTML}
          </div>
          <script>
            window.onload = function() {
              setTimeout(function() {
                window.print();
                window.close();
              }, 500);
            };
          </script>
        </body>
        </html>
      `);

      printWindow.document.close();
    }, 500);
  };

  return { printAreaRef, handlePrint };
}
