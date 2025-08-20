<?php

namespace App\Services;

/**
 * Service centralisé pour toutes les règles de relations familiales
 * Évite la duplication entre SimpleRelationshipInferenceService et IntelligentRelationshipService
 */
class RelationshipRulesService
{
    /**
     * Règles complètes de déduction des relations familiales
     * Format: relation_intermediaire => [relation_cible => relation_deduite]
     */
    public static function getRelationshipRules(): array
    {
        return [
            // --- Relations directes (sang) ---
            'father' => [
                'son' => 'brother',
                'daughter' => 'sister',
                'wife' => 'mother',
                'husband' => 'stepfather',
                'father' => 'grandfather_paternal',
                'mother' => 'grandmother_paternal',
                'brother' => 'uncle_paternal',
                'sister' => 'aunt_paternal',
            ],
            'mother' => [
                'son' => 'brother',
                'daughter' => 'sister',
                'husband' => 'father',
                'wife' => 'stepmother',
                'father' => 'grandfather_maternal',
                'mother' => 'grandmother_maternal',
                'brother' => 'uncle_maternal',
                'sister' => 'aunt_maternal',
            ],

            // --- Enfants ---
            'son' => [
                'father' => 'father',
                'mother' => 'mother',
                'brother' => 'brother',
                'sister' => 'sister',
                'wife' => 'daughter_in_law',
                'son' => 'grandson',
                'daughter' => 'granddaughter',
            ],
            'daughter' => [
                'father' => 'father',
                'mother' => 'mother',
                'brother' => 'brother',
                'sister' => 'sister',
                'husband' => 'son_in_law',
                'son' => 'grandson',
                'daughter' => 'granddaughter',
            ],

            // --- Fratrie ---
            'brother' => [
                'brother' => 'brother',
                'sister' => 'sister',
                'father' => 'father',
                'mother' => 'mother',
                'wife' => 'sister_in_law',
                'son' => 'nephew',
                'daughter' => 'niece',
            ],
            'sister' => [
                'sister' => 'sister',
                'brother' => 'brother',
                'father' => 'father',
                'mother' => 'mother',
                'husband' => 'brother_in_law',
                'son' => 'nephew',
                'daughter' => 'niece',
            ],

            // --- Grands-parents ---
            'grandfather_paternal' => [
                'son' => 'father',
                'daughter' => 'aunt_paternal',
                'wife' => 'grandmother_paternal',
            ],
            'grandmother_paternal' => [
                'son' => 'father',
                'daughter' => 'aunt_paternal',
                'husband' => 'grandfather_paternal',
            ],
            'grandfather_maternal' => [
                'son' => 'uncle_maternal',
                'daughter' => 'mother',
                'wife' => 'grandmother_maternal',
            ],
            'grandmother_maternal' => [
                'son' => 'uncle_maternal',
                'daughter' => 'mother',
                'husband' => 'grandfather_maternal',
            ],

            // --- Oncles/Tantes ---
            'uncle_paternal' => [
                'son' => 'cousin_paternal_m',
                'daughter' => 'cousin_paternal_f',
                'wife' => 'aunt_paternal',
            ],
            'aunt_paternal' => [
                'son' => 'cousin_paternal_m',
                'daughter' => 'cousin_paternal_f',
                'husband' => 'uncle_paternal',
            ],
            'uncle_maternal' => [
                'son' => 'cousin_maternal_m',
                'daughter' => 'cousin_maternal_f',
                'wife' => 'aunt_maternal',
            ],
            'aunt_maternal' => [
                'son' => 'cousin_maternal_m',
                'daughter' => 'cousin_maternal_f',
                'husband' => 'uncle_maternal',
            ],

            // --- Cousins ---
            'cousin_paternal_m' => [
                'father' => 'uncle_paternal',
                'mother' => 'aunt_paternal',
                'wife' => 'cousin_paternal_in_law',
            ],
            'cousin_paternal_f' => [
                'father' => 'uncle_paternal',
                'mother' => 'aunt_paternal',
                'husband' => 'cousin_paternal_in_law',
            ],
            'cousin_maternal_m' => [
                'father' => 'uncle_maternal',
                'mother' => 'aunt_maternal',
                'wife' => 'cousin_maternal_in_law',
            ],
            'cousin_maternal_f' => [
                'father' => 'uncle_maternal',
                'mother' => 'aunt_maternal',
                'husband' => 'cousin_maternal_in_law',
            ],

            // --- Neveux/Nièces ---
            'nephew' => [
                'father' => 'brother',
                'mother' => 'sister_in_law',
                'wife' => 'niece_in_law',
                'son' => 'grandnephew',
            ],
            'niece' => [
                'father' => 'brother',
                'mother' => 'sister_in_law',
                'husband' => 'nephew_in_law',
                'daughter' => 'grandniece',
            ],

            // --- Belle-famille (relations par alliance) ---
            'husband' => [
                'father' => 'father_in_law',
                'mother' => 'mother_in_law',
                'brother' => 'brother_in_law',
                'sister' => 'sister_in_law',
                'son' => 'stepson',
                'daughter' => 'stepdaughter',
            ],
            'wife' => [
                'father' => 'father_in_law',
                'mother' => 'mother_in_law',
                'brother' => 'brother_in_law',
                'sister' => 'sister_in_law',
                'son' => 'stepson',
                'daughter' => 'stepdaughter',
            ],
            'father_in_law' => [
                'son' => 'husband',
                'daughter' => 'sister_in_law',
            ],
            'mother_in_law' => [
                'son' => 'husband',
                'daughter' => 'sister_in_law',
            ],
            'brother_in_law' => [
                'wife' => 'sister',
                'brother' => 'husband',
            ],
            'sister_in_law' => [
                'husband' => 'brother',
                'sister' => 'wife',
            ],
            'stepfather' => [
                'son' => 'stepbrother',
                'daughter' => 'stepsister',
            ],
            'stepmother' => [
                'son' => 'stepbrother',
                'daughter' => 'stepsister',
            ],
            'stepson' => [
                'father' => 'husband',
                'mother' => 'wife',
            ],
            'stepdaughter' => [
                'father' => 'husband',
                'mother' => 'wife',
            ],
            'son_in_law' => [
                'father' => 'father_in_law',
                'mother' => 'mother_in_law',
            ],
            'daughter_in_law' => [
                'father' => 'father_in_law',
                'mother' => 'mother_in_law',
            ],

            // --- Relations spéciales ---
            'grandson' => [
                'father' => 'son',
                'mother' => 'daughter',
            ],
            'granddaughter' => [
                'father' => 'son',
                'mother' => 'daughter',
            ],

            // --- Relations génériques ---
            'spouse' => [
                'father' => 'father_in_law',
                'mother' => 'mother_in_law',
                'brother' => 'brother_in_law',
                'sister' => 'sister_in_law',
                'son' => 'stepson',
                'daughter' => 'stepdaughter',
            ],
            'sibling' => [
                'spouse' => 'brother_in_law', // Sera adapté selon le genre
                'son' => 'nephew',
                'daughter' => 'niece',
                'father' => 'father',
                'mother' => 'mother',
            ],
            'child' => [
                'father' => 'father',
                'mother' => 'mother',
                'spouse' => 'son_in_law', // Sera adapté selon le genre
            ],
            'parent' => [
                'son' => 'sibling', // Sera adapté selon le genre
                'daughter' => 'sibling', // Sera adapté selon le genre
                'spouse' => 'parent', // Sera adapté selon le genre
            ],

            // --- Oncles/Tantes génériques ---
            'uncle' => [
                'son' => 'cousin',
                'daughter' => 'cousin',
                'wife' => 'aunt',
            ],
            'aunt' => [
                'son' => 'cousin',
                'daughter' => 'cousin',
                'husband' => 'uncle',
            ],
        ];
    }

    /**
     * Obtenir les règles de relations inverses
     */
    public static function getInverseRelationMap(): array
    {
        return [
            'father' => 'child',
            'mother' => 'child',
            'son' => 'parent',
            'daughter' => 'parent',
            'brother' => 'sibling',
            'sister' => 'sibling',
            'husband' => 'wife',
            'wife' => 'husband',
            'uncle' => 'nephew',
            'aunt' => 'niece',
            'nephew' => 'uncle',
            'niece' => 'aunt',
            'cousin' => 'cousin',
            'grandfather' => 'grandchild',
            'grandmother' => 'grandchild',
            'grandson' => 'grandparent',
            'granddaughter' => 'grandparent',
            'brother_in_law' => 'brother_in_law',
            'sister_in_law' => 'sister_in_law',
            'father_in_law' => 'son_in_law',
            'mother_in_law' => 'daughter_in_law',
            'son_in_law' => 'father_in_law',
            'daughter_in_law' => 'mother_in_law',
            'spouse' => 'spouse',
            'sibling' => 'sibling',
            'child' => 'parent',
            'parent' => 'child',
        ];
    }
}
