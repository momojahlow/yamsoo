
import { createClient } from 'https://esm.sh/@supabase/supabase-js@2.39.7'
import { 
  FamilyRelationType, 
  ProfileWithId, 
  ProcessedRelation,
  generateSuggestions 
} from './suggestions.ts'

const corsHeaders = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
};

Deno.serve(async (req) => {
  // Handle CORS preflight requests
  if (req.method === 'OPTIONS') {
    return new Response(null, { headers: corsHeaders });
  }

  try {
    // Création du client Supabase avec les variables d'environnement
    const supabaseUrl = Deno.env.get('SUPABASE_URL') || '';
    const supabaseAnonKey = Deno.env.get('SUPABASE_ANON_KEY') || '';
    const supabase = createClient(supabaseUrl, supabaseAnonKey);
    
    console.log("🔍 Récupération des relations familiales acceptées...");
    
    // Récupérer toutes les relations familiales avec statut accepté
    const { data: rawRelations, error: relationsError } = await supabase
      .from('family_relations')
      .select(`
        id, 
        user_id, 
        related_user_id, 
        relation_type, 
        status
      `)
      .eq('status', 'accepted');
      
    if (relationsError) {
      throw relationsError;
    }

    console.log(`✅ Récupéré ${rawRelations?.length || 0} relations acceptées`);
    
    // Récupérer tous les profils pertinents en une seule requête
    const uniqueUserIds = new Set<string>();
    (rawRelations || []).forEach(rel => {
      uniqueUserIds.add(rel.user_id);
      uniqueUserIds.add(rel.related_user_id);
    });
    
    console.log(`🧑‍🤝‍🧑 Récupération des profils pour ${uniqueUserIds.size} utilisateurs...`);
    
    const { data: profiles, error: profilesError } = await supabase
      .from('profiles')
      .select('id, first_name, last_name, gender')
      .in('id', Array.from(uniqueUserIds));
      
    if (profilesError) {
      throw profilesError;
    }
    
    console.log(`👤 Récupéré ${profiles?.length || 0} profils`);
    
    // Créer un mapping d'ID à profil pour un accès facile
    const profilesMap = new Map<string, ProfileWithId>();
    (profiles || []).forEach(profile => {
      profilesMap.set(profile.id, profile);
    });
    
    // Convertir les relations brutes en format plus facile à traiter
    const processedRelations: ProcessedRelation[] = (rawRelations || []).map(rel => ({
      userId: rel.user_id,
      relatedUserId: rel.related_user_id,
      relationType: rel.relation_type,
      userProfile: profilesMap.get(rel.user_id),
      relatedProfile: profilesMap.get(rel.related_user_id)
    }));
    
    console.log("🔄 Traitement des relations...");

    // Générer les suggestions de relation basées sur les relations existantes
    const suggestions = generateSuggestions(processedRelations, profilesMap);
    console.log(`✨ Généré ${suggestions.length} suggestions de relation`);

    // Build a comprehensive map of all existing relations for filtering
    // This is a bidirectional map to check relations in both directions
    const existingRelationMap = new Map<string, Set<string>>();
    
    for (const rel of processedRelations) {
      // Add relation from user_id to related_user_id
      if (!existingRelationMap.has(rel.userId)) {
        existingRelationMap.set(rel.userId, new Set<string>());
      }
      existingRelationMap.get(rel.userId)!.add(rel.relatedUserId);
      
      // Also add the inverse relation for easy lookup
      if (!existingRelationMap.has(rel.relatedUserId)) {
        existingRelationMap.set(rel.relatedUserId, new Set<string>());
      }
      existingRelationMap.get(rel.relatedUserId)!.add(rel.userId);
    }
    
    console.log(`🔍 Created relation map with ${existingRelationMap.size} users`);

    // Pour chaque suggestion, vérifier si elle existe déjà dans la table relation_suggestions
    let insertCount = 0;
    for (const suggestion of suggestions) {
      // Check if a relation already exists between these users (in either direction)
      const hasExistingRelation = 
        (existingRelationMap.get(suggestion.user_id)?.has(suggestion.suggested_user_id)) ||
        (existingRelationMap.get(suggestion.suggested_user_id)?.has(suggestion.user_id));
      
      if (hasExistingRelation) {
        console.log(`🚫 Relation déjà existante entre ${suggestion.user_id} et ${suggestion.suggested_user_id} - suggestion ignorée`);
        continue;
      }
      
      // Adapter la relation suggérée en fonction du genre
      if (suggestion.suggested_relation_type === 'father' || suggestion.suggested_relation_type === 'mother') {
        // Récupérer le profil de l'utilisateur suggéré pour déterminer son genre
        const suggestedUserProfile = profilesMap.get(suggestion.suggested_user_id);
        if (suggestedUserProfile && suggestedUserProfile.gender === 'F') {
          suggestion.suggested_relation_type = 'mother';
        } else if (suggestedUserProfile && suggestedUserProfile.gender === 'M') {
          suggestion.suggested_relation_type = 'father';
        }
      }
      
      // Corriger les relations incorrectes basées sur les parents partagés
      if (suggestion.reason && 
         (suggestion.reason.includes("partagez la même") || 
          suggestion.reason.includes("mère →") || 
          suggestion.reason.includes("père →"))) {
        
        // C'est un frère ou une sœur, pas un autre type de relation
        const suggestedUserProfile = profilesMap.get(suggestion.suggested_user_id);
        
        if (suggestedUserProfile) {
          // Déterminer le type de relation en fonction du genre
          if (suggestedUserProfile.gender === 'F') {
            suggestion.suggested_relation_type = 'sister';
            // Mettre à jour la raison pour plus de clarté
            if (suggestion.reason.includes("mère →") || suggestion.reason.includes("même mère")) {
              suggestion.reason = `Vous partagez la même mère ${suggestion.reason.includes("(") ? suggestion.reason.substring(suggestion.reason.indexOf("(")) : ""}`;
            } else if (suggestion.reason.includes("père →") || suggestion.reason.includes("même père")) {
              suggestion.reason = `Vous partagez le même père ${suggestion.reason.includes("(") ? suggestion.reason.substring(suggestion.reason.indexOf("(")) : ""}`;
            }
          } else {
            suggestion.suggested_relation_type = 'brother';
            // Mettre à jour la raison pour plus de clarté
            if (suggestion.reason.includes("mère →") || suggestion.reason.includes("même mère")) {
              suggestion.reason = `Vous partagez la même mère ${suggestion.reason.includes("(") ? suggestion.reason.substring(suggestion.reason.indexOf("(")) : ""}`;
            } else if (suggestion.reason.includes("père →") || suggestion.reason.includes("même père")) {
              suggestion.reason = `Vous partagez le même père ${suggestion.reason.includes("(") ? suggestion.reason.substring(suggestion.reason.indexOf("(")) : ""}`;
            }
          }
        }
      }

      // Vérifie si la suggestion existe déjà dans la table relation_suggestions
      const { data: existingSuggestion, error: suggestionError } = await supabase
        .from('relation_suggestions')
        .select('*')
        .eq('user_id', suggestion.user_id)
        .eq('suggested_user_id', suggestion.suggested_user_id)
        .eq('status', 'pending')
        .maybeSingle();

      if (suggestionError) {
        console.error('❌ Erreur lors de la vérification de suggestion existante:', suggestionError);
        continue;
      }

      // Si la suggestion n'existe pas, l'insérer
      if (!existingSuggestion) {
        const { error: insertError } = await supabase
          .from('relation_suggestions')
          .insert([suggestion]);

        if (insertError) {
          console.error('❌ Erreur lors de l\'insertion de la suggestion:', insertError);
        } else {
          insertCount++;
        }
      }
    }

    console.log(`✅ ${insertCount} nouvelles suggestions insérées avec succès`);

    return new Response(
      JSON.stringify({ success: true, suggestionsCount: insertCount }),
      {
        headers: { ...corsHeaders, 'Content-Type': 'application/json' },
        status: 200,
      },
    );
  } catch (error) {
    console.error('❌ Erreur globale:', error);
    return new Response(
      JSON.stringify({ error: error.message }),
      {
        headers: { ...corsHeaders, 'Content-Type': 'application/json' },
        status: 500,
      },
    );
  }
});
