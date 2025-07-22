import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { 
  Dialog, 
  DialogContent, 
  DialogHeader, 
  DialogTitle, 
  DialogTrigger 
} from '@/components/ui/dialog';
import { 
  Sparkles, 
  Users, 
  Heart, 
  AlertCircle, 
  CheckCircle, 
  Info,
  Loader2,
  UserCheck
} from 'lucide-react';
import axios from 'axios';

interface YamsooAnalysis {
  has_relation: boolean;
  relation_type: 'direct' | 'indirect' | 'none' | 'self';
  relation_name: string;
  relation_description: string;
  relation_path: string[];
  confidence: number;
  yamsoo_message: string;
  suggestion?: string;
  intermediate_users?: Array<{
    id: number;
    name: string;
  }>;
}

interface TargetUser {
  id: number;
  name: string;
  profile?: {
    first_name?: string;
    last_name?: string;
    avatar_url?: string;
  };
}

interface Props {
  targetUserId: number;
  targetUserName: string;
  className?: string;
  variant?: 'default' | 'outline' | 'ghost';
  size?: 'sm' | 'default' | 'lg';
}

export default function YamsooButton({ 
  targetUserId, 
  targetUserName, 
  className = '',
  variant = 'outline',
  size = 'sm'
}: Props) {
  const [isOpen, setIsOpen] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [analysis, setAnalysis] = useState<YamsooAnalysis | null>(null);
  const [error, setError] = useState<string | null>(null);

  const analyzeRelation = async () => {
    if (analysis) {
      setIsOpen(true);
      return;
    }

    setIsLoading(true);
    setError(null);

    try {
      const response = await axios.post('/yamsoo/analyze-relation', {
        target_user_id: targetUserId,
      });

      if (response.data.success) {
        setAnalysis(response.data.analysis);
        setIsOpen(true);
      } else {
        setError(response.data.message || 'Erreur lors de l\'analyse');
      }
    } catch (err: any) {
      setError(err.response?.data?.message || 'Erreur de connexion');
    } finally {
      setIsLoading(false);
    }
  };

  const getRelationIcon = (relationType: string) => {
    switch (relationType) {
      case 'direct':
        return <Heart className="w-4 h-4 text-red-500" />;
      case 'indirect':
        return <Users className="w-4 h-4 text-blue-500" />;
      case 'self':
        return <UserCheck className="w-4 h-4 text-green-500" />;
      default:
        return <AlertCircle className="w-4 h-4 text-gray-500" />;
    }
  };

  const getRelationBadgeColor = (relationType: string) => {
    switch (relationType) {
      case 'direct':
        return 'relation-badge-direct';
      case 'indirect':
        return 'relation-badge-indirect';
      case 'self':
        return 'relation-badge-self';
      default:
        return 'relation-badge-none';
    }
  };

  const getConfidenceColor = (confidence: number) => {
    if (confidence >= 90) return 'text-green-600';
    if (confidence >= 70) return 'text-blue-600';
    if (confidence >= 50) return 'text-orange-600';
    return 'text-red-600';
  };

  return (
    <Dialog open={isOpen} onOpenChange={setIsOpen}>
      <DialogTrigger asChild>
        <Button
          variant={variant}
          size={size}
          onClick={analyzeRelation}
          disabled={isLoading}
          className={`${className} yamsoo-button transition-all duration-200 hover:scale-105`}
        >
          {isLoading ? (
            <Loader2 className="w-4 h-4 yamsoo-loading mr-2" />
          ) : (
            <Sparkles className="w-4 h-4 yamsoo-icon mr-2" />
          )}
          {isLoading ? 'Analyse...' : 'Yamsoo'}
        </Button>
      </DialogTrigger>

      <DialogContent className="max-w-md yamsoo-dialog">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <Sparkles className="w-5 h-5 text-purple-600 yamsoo-icon" />
            Analyse Yamsoo
          </DialogTitle>
        </DialogHeader>

        {error && (
          <Card className="border-red-200 bg-red-50">
            <CardContent className="pt-4">
              <div className="flex items-center gap-2 text-red-700">
                <AlertCircle className="w-4 h-4" />
                <span className="text-sm">{error}</span>
              </div>
            </CardContent>
          </Card>
        )}

        {analysis && (
          <div className="space-y-4">
            {/* Message principal */}
            <Card className={`yamsoo-info-card ${analysis.relation_type}`}>
              <CardContent className="pt-4">
                <div className="flex items-start gap-3">
                  {getRelationIcon(analysis.relation_type)}
                  <div className="flex-1">
                    <p className="font-medium text-gray-900 mb-1">
                      {analysis.yamsoo_message}
                    </p>
                    <p className="text-sm text-gray-600">
                      {analysis.relation_description}
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Détails de la relation */}
            <div className="grid grid-cols-2 gap-3">
              <div className="text-center">
                <Badge className={getRelationBadgeColor(analysis.relation_type)}>
                  {analysis.relation_name}
                </Badge>
                <p className="text-xs text-gray-500 mt-1">Type de relation</p>
              </div>
              
              {analysis.confidence > 0 && (
                <div className="text-center">
                  <span className={`font-bold ${getConfidenceColor(analysis.confidence)}`}>
                    {analysis.confidence}%
                  </span>
                  <p className="text-xs text-gray-500 mt-1">Confiance</p>
                </div>
              )}
            </div>

            {/* Chemin de relation pour les relations indirectes */}
            {analysis.relation_type === 'indirect' && analysis.relation_path.length > 0 && (
              <Card className="bg-blue-50 border-blue-200">
                <CardHeader className="pb-2">
                  <CardTitle className="text-sm flex items-center gap-2">
                    <Info className="w-4 h-4 text-blue-600" />
                    Chemin de relation
                  </CardTitle>
                </CardHeader>
                <CardContent className="pt-0">
                  <div className="relation-path">
                    <span className="relation-path-step font-medium">Vous</span>
                    {analysis.relation_path.map((step, index) => (
                      <React.Fragment key={index}>
                        <span className="relation-path-arrow">→</span>
                        <span className={`relation-path-step ${index === 1 ? 'intermediate' : ''}`}>
                          {step}
                        </span>
                      </React.Fragment>
                    ))}
                    <span className="relation-path-arrow">→</span>
                    <span className="relation-path-step font-medium">{targetUserName}</span>
                  </div>
                </CardContent>
              </Card>
            )}

            {/* Suggestion pour les relations non trouvées */}
            {analysis.relation_type === 'none' && analysis.suggestion && (
              <Card className="yamsoo-suggestion">
                <CardContent className="pt-4">
                  <div className="flex items-start gap-2">
                    <Info className="w-4 h-4 text-yellow-600 mt-0.5" />
                    <p className="text-sm text-yellow-800">
                      {analysis.suggestion}
                    </p>
                  </div>
                </CardContent>
              </Card>
            )}

            {/* Actions */}
            <div className="flex gap-2 pt-2">
              <Button
                variant="outline"
                size="sm"
                onClick={() => setIsOpen(false)}
                className="flex-1"
              >
                Fermer
              </Button>
              
              {analysis.relation_type === 'none' && (
                <Button
                  size="sm"
                  className="flex-1"
                  onClick={() => {
                    // Rediriger vers la page de demande de relation
                    window.location.href = `/family-relations?user=${targetUserId}`;
                  }}
                >
                  Demander relation
                </Button>
              )}
            </div>
          </div>
        )}
      </DialogContent>
    </Dialog>
  );
}
