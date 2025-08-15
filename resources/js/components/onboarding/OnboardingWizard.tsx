import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
  Users, 
  TreePine, 
  UserPlus, 
  MessageSquare, 
  Camera,
  CheckCircle,
  ArrowRight,
  ArrowLeft,
  X
} from 'lucide-react';
import { useTranslation } from '@/hooks/useTranslation';

interface OnboardingStep {
  id: string;
  title: string;
  description: string;
  icon: React.ComponentType<any>;
  action?: {
    label: string;
    href: string;
  };
  completed?: boolean;
}

interface OnboardingWizardProps {
  onClose: () => void;
  userStats?: {
    familyMembers: number;
    hasProfileComplete: boolean;
    hasAddedRelations: boolean;
  };
}

export const OnboardingWizard: React.FC<OnboardingWizardProps> = ({ 
  onClose, 
  userStats = { familyMembers: 0, hasProfileComplete: false, hasAddedRelations: false }
}) => {
  const { t, isRTL } = useTranslation();
  const [currentStep, setCurrentStep] = useState(0);

  const steps: OnboardingStep[] = [
    {
      id: 'welcome',
      title: t('welcome_to_yamsoo'),
      description: t('onboarding_welcome_desc'),
      icon: Users,
    },
    {
      id: 'profile',
      title: t('complete_your_profile'),
      description: t('onboarding_profile_desc'),
      icon: UserPlus,
      action: {
        label: t('complete_profile'),
        href: '/profile/edit'
      },
      completed: userStats.hasProfileComplete
    },
    {
      id: 'family',
      title: t('add_family_members'),
      description: t('onboarding_family_desc'),
      icon: Users,
      action: {
        label: t('add_first_member'),
        href: '/reseaux'
      },
      completed: userStats.familyMembers > 0
    },
    {
      id: 'tree',
      title: t('explore_family_tree'),
      description: t('onboarding_tree_desc'),
      icon: TreePine,
      action: {
        label: t('view_family_tree'),
        href: '/famille/arbre'
      }
    },
    {
      id: 'features',
      title: t('discover_features'),
      description: t('onboarding_features_desc'),
      icon: Camera,
    }
  ];

  const nextStep = () => {
    if (currentStep < steps.length - 1) {
      setCurrentStep(currentStep + 1);
    }
  };

  const prevStep = () => {
    if (currentStep > 0) {
      setCurrentStep(currentStep - 1);
    }
  };

  const currentStepData = steps[currentStep];

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <Card className="w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <CardHeader className="relative">
          <button
            onClick={onClose}
            className="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors"
          >
            <X className="h-6 w-6" />
          </button>
          
          <div className="flex items-center justify-between mb-4">
            <Badge variant="outline" className="text-orange-600 border-orange-200">
              {t('getting_started')}
            </Badge>
            <span className="text-sm text-gray-500">
              {currentStep + 1} / {steps.length}
            </span>
          </div>

          {/* Progress bar */}
          <div className="w-full bg-gray-200 rounded-full h-2 mb-6">
            <div 
              className="bg-gradient-to-r from-orange-500 to-red-500 h-2 rounded-full transition-all duration-300"
              style={{ width: `${((currentStep + 1) / steps.length) * 100}%` }}
            />
          </div>

          <div className="text-center">
            <div className="w-16 h-16 bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <currentStepData.icon className="h-8 w-8 text-white" />
            </div>
            <CardTitle className="text-2xl font-bold text-gray-900 mb-2">
              {currentStepData.title}
            </CardTitle>
          </div>
        </CardHeader>

        <CardContent className="space-y-6">
          <p className="text-gray-600 text-center text-lg leading-relaxed">
            {currentStepData.description}
          </p>

          {/* Step-specific content */}
          {currentStep === 0 && (
            <div className="grid grid-cols-2 gap-4 mt-6">
              <div className="text-center p-4 bg-orange-50 rounded-lg">
                <Users className="h-8 w-8 text-orange-500 mx-auto mb-2" />
                <h4 className="font-semibold text-gray-900">{t('connect_family')}</h4>
                <p className="text-sm text-gray-600">{t('build_family_network')}</p>
              </div>
              <div className="text-center p-4 bg-blue-50 rounded-lg">
                <TreePine className="h-8 w-8 text-blue-500 mx-auto mb-2" />
                <h4 className="font-semibold text-gray-900">{t('family_tree')}</h4>
                <p className="text-sm text-gray-600">{t('visualize_relationships')}</p>
              </div>
            </div>
          )}

          {currentStep === 4 && (
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
              <div className="p-4 border border-gray-200 rounded-lg hover:border-orange-300 transition-colors">
                <MessageSquare className="h-6 w-6 text-green-500 mb-2" />
                <h4 className="font-semibold text-gray-900">{t('family_messaging')}</h4>
                <p className="text-sm text-gray-600">{t('stay_connected_family')}</p>
              </div>
              <div className="p-4 border border-gray-200 rounded-lg hover:border-orange-300 transition-colors">
                <Camera className="h-6 w-6 text-purple-500 mb-2" />
                <h4 className="font-semibold text-gray-900">{t('photo_albums')}</h4>
                <p className="text-sm text-gray-600">{t('share_family_memories')}</p>
              </div>
            </div>
          )}

          {/* Completion status */}
          {currentStepData.completed && (
            <div className="flex items-center justify-center p-4 bg-green-50 border border-green-200 rounded-lg">
              <CheckCircle className="h-5 w-5 text-green-500 mr-2" />
              <span className="text-green-700 font-medium">{t('step_completed')}</span>
            </div>
          )}

          {/* Action button */}
          {currentStepData.action && !currentStepData.completed && (
            <div className="text-center">
              <Button
                onClick={() => window.location.href = currentStepData.action!.href}
                className="bg-gradient-to-r from-orange-500 to-red-500 text-white px-8 py-3 rounded-lg hover:from-orange-600 hover:to-red-600 transition-all duration-200"
              >
                {currentStepData.action.label}
                <ArrowRight className={`h-4 w-4 ${isRTL ? 'mr-2' : 'ml-2'}`} />
              </Button>
            </div>
          )}

          {/* Navigation */}
          <div className={`flex justify-between items-center pt-6 ${isRTL ? 'flex-row-reverse' : ''}`}>
            <Button
              variant="outline"
              onClick={prevStep}
              disabled={currentStep === 0}
              className={`flex items-center ${isRTL ? 'flex-row-reverse' : ''}`}
            >
              <ArrowLeft className={`h-4 w-4 ${isRTL ? 'ml-2' : 'mr-2'}`} />
              {t('previous')}
            </Button>

            {currentStep === steps.length - 1 ? (
              <Button
                onClick={onClose}
                className="bg-gradient-to-r from-orange-500 to-red-500 text-white"
              >
                {t('get_started')}
              </Button>
            ) : (
              <Button
                onClick={nextStep}
                className={`bg-gradient-to-r from-orange-500 to-red-500 text-white flex items-center ${isRTL ? 'flex-row-reverse' : ''}`}
              >
                {t('next')}
                <ArrowRight className={`h-4 w-4 ${isRTL ? 'mr-2' : 'ml-2'}`} />
              </Button>
            )}
          </div>
        </CardContent>
      </Card>
    </div>
  );
};
