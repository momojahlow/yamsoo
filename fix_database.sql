-- Script SQL pour corriger la table relationship_types
-- Exécuter avec: sqlite3 database/database.sqlite < fix_database.sql

-- 1. Supprimer l'ancienne table
DROP TABLE IF EXISTS relationship_types;

-- 2. Créer la nouvelle table avec la structure correcte
CREATE TABLE relationship_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    display_name_fr VARCHAR(255) NOT NULL,
    display_name_ar VARCHAR(255) NOT NULL,
    display_name_en VARCHAR(255) NOT NULL,
    description TEXT,
    reverse_relationship VARCHAR(255),
    category VARCHAR(255) NOT NULL DEFAULT 'direct',
    generation_level INTEGER NOT NULL DEFAULT 0,
    sort_order INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME,
    updated_at DATETIME
);

-- 3. Créer les index
CREATE INDEX idx_relationship_types_category ON relationship_types(category);
CREATE INDEX idx_relationship_types_generation_level ON relationship_types(generation_level);
CREATE INDEX idx_relationship_types_sort_order ON relationship_types(sort_order);

-- 4. Insérer les données essentielles
INSERT INTO relationship_types (name, display_name_fr, display_name_ar, display_name_en, description, reverse_relationship, category, generation_level, sort_order, created_at, updated_at) VALUES
('parent', 'Parent', 'والد/والدة', 'Parent', 'Relation parent-enfant directe', 'child', 'direct', -1, 1, datetime('now'), datetime('now')),
('father', 'Père', 'أب', 'Father', 'Père biologique ou adoptif', 'child', 'direct', -1, 2, datetime('now'), datetime('now')),
('mother', 'Mère', 'أم', 'Mother', 'Mère biologique ou adoptive', 'child', 'direct', -1, 3, datetime('now'), datetime('now')),
('child', 'Enfant', 'طفل/طفلة', 'Child', 'Enfant biologique ou adoptif', 'parent', 'direct', 1, 4, datetime('now'), datetime('now')),
('son', 'Fils', 'ابن', 'Son', 'Fils biologique ou adoptif', 'parent', 'direct', 1, 5, datetime('now'), datetime('now')),
('daughter', 'Fille', 'ابنة', 'Daughter', 'Fille biologique ou adoptive', 'parent', 'direct', 1, 6, datetime('now'), datetime('now')),
('spouse', 'Époux/Épouse', 'زوج/زوجة', 'Spouse', 'Conjoint marié', 'spouse', 'marriage', 0, 7, datetime('now'), datetime('now')),
('husband', 'Mari', 'زوج', 'Husband', 'Époux masculin', 'wife', 'marriage', 0, 8, datetime('now'), datetime('now')),
('wife', 'Épouse', 'زوجة', 'Wife', 'Épouse féminine', 'husband', 'marriage', 0, 9, datetime('now'), datetime('now')),
('sibling', 'Frère/Sœur', 'أخ/أخت', 'Sibling', 'Frère ou sœur', 'sibling', 'direct', 0, 10, datetime('now'), datetime('now')),
('brother', 'Frère', 'أخ', 'Brother', 'Frère biologique ou adoptif', 'sibling', 'direct', 0, 11, datetime('now'), datetime('now')),
('sister', 'Sœur', 'أخت', 'Sister', 'Sœur biologique ou adoptive', 'sibling', 'direct', 0, 12, datetime('now'), datetime('now')),
('grandparent', 'Grand-parent', 'جد/جدة', 'Grandparent', 'Grand-père ou grand-mère', 'grandchild', 'extended', -2, 13, datetime('now'), datetime('now')),
('grandfather', 'Grand-père', 'جد', 'Grandfather', 'Père du père ou de la mère', 'grandchild', 'extended', -2, 14, datetime('now'), datetime('now')),
('grandmother', 'Grand-mère', 'جدة', 'Grandmother', 'Mère du père ou de la mère', 'grandchild', 'extended', -2, 15, datetime('now'), datetime('now')),
('grandchild', 'Petit-enfant', 'حفيد/حفيدة', 'Grandchild', 'Enfant de son enfant', 'grandparent', 'extended', 2, 16, datetime('now'), datetime('now')),
('grandson', 'Petit-fils', 'حفيد', 'Grandson', 'Fils de son enfant', 'grandparent', 'extended', 2, 17, datetime('now'), datetime('now')),
('granddaughter', 'Petite-fille', 'حفيدة', 'Granddaughter', 'Fille de son enfant', 'grandparent', 'extended', 2, 18, datetime('now'), datetime('now')),
('uncle', 'Oncle', 'عم/خال', 'Uncle', 'Frère du père ou de la mère', 'nephew_niece', 'extended', -1, 19, datetime('now'), datetime('now')),
('aunt', 'Tante', 'عمة/خالة', 'Aunt', 'Sœur du père ou de la mère', 'nephew_niece', 'extended', -1, 20, datetime('now'), datetime('now')),
('nephew', 'Neveu', 'ابن أخ/أخت', 'Nephew', 'Fils du frère ou de la sœur', 'uncle_aunt', 'extended', 1, 21, datetime('now'), datetime('now')),
('niece', 'Nièce', 'ابنة أخ/أخت', 'Niece', 'Fille du frère ou de la sœur', 'uncle_aunt', 'extended', 1, 22, datetime('now'), datetime('now')),
('father_in_law', 'Beau-père', 'حمو', 'Father-in-law', 'Père du conjoint', 'son_daughter_in_law', 'marriage', -1, 23, datetime('now'), datetime('now')),
('mother_in_law', 'Belle-mère', 'حماة', 'Mother-in-law', 'Mère du conjoint', 'son_daughter_in_law', 'marriage', -1, 24, datetime('now'), datetime('now')),
('son_in_law', 'Gendre', 'صهر', 'Son-in-law', 'Mari de la fille', 'father_mother_in_law', 'marriage', 1, 25, datetime('now'), datetime('now')),
('daughter_in_law', 'Belle-fille', 'كنة', 'Daughter-in-law', 'Épouse du fils', 'father_mother_in_law', 'marriage', 1, 26, datetime('now'), datetime('now')),
('cousin', 'Cousin/Cousine', 'ابن/ابنة عم/خال', 'Cousin', 'Enfant de l''oncle ou de la tante', 'cousin', 'extended', 0, 27, datetime('now'), datetime('now')),
('adoptive_parent', 'Parent adoptif', 'والد/والدة بالتبني', 'Adoptive parent', 'Parent par adoption légale', 'adopted_child', 'adoption', -1, 28, datetime('now'), datetime('now')),
('adopted_child', 'Enfant adopté', 'طفل/طفلة بالتبني', 'Adopted child', 'Enfant par adoption légale', 'adoptive_parent', 'adoption', 1, 29, datetime('now'), datetime('now')),
('family_member', 'Membre de la famille', 'فرد من العائلة', 'Family member', 'Membre de la famille (relation non spécifiée)', 'family_member', 'extended', 0, 30, datetime('now'), datetime('now'));

-- 5. Vérifier que tout est correct
SELECT COUNT(*) as total_types FROM relationship_types;
SELECT name, display_name_fr, category FROM relationship_types ORDER BY sort_order LIMIT 10;
