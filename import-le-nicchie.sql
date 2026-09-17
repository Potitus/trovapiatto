-- Import Le Nicchie Ristorante (Bari Vecchia) su trovapiatto
-- Piatti dai menu Sluurpy/TheFork + specialita citate nelle recensioni - Settembre 2026
-- Orari: Lun chiuso, Mar-Dom 11:30-15:30 e 19:30-23:00 (da Restaurant Guru)
-- Eseguire su MySQL: mysql -u USER -p DB_NAME < import-le-nicchie.sql

START TRANSACTION;

INSERT INTO restaurants (id, name, slug, description, address, phone, email, logo_url, latitude, longitude, city, cuisine, website, rating, available_hours) VALUES ('rest_le_nicchie', 'Le Nicchie Ristorante', 'le-nicchie', 'Cucina tipica barese con qualche rivisitazione nel cuore di Bari Vecchia: pesce fresco, tiella, braciola di cavallo e pizze. Consigliato per pranzo e cena.', 'Vico Corsioli, 11/B, 70122 Bari', '080 883 5025', NULL, 'https://www.trovapiatto.it/logo/le-nicchie.png', 41.1286724, 16.8720690, 'Bari', 'Pugliese', 'https://social.quandoo.com/en/groups/Le-Nicchie-Ristorante', NULL, '{"tue":{"lunch":"11:30-15:30","dinner":"19:30-23:00"},"wed":{"lunch":"11:30-15:30","dinner":"19:30-23:00"},"thu":{"lunch":"11:30-15:30","dinner":"19:30-23:00"},"fri":{"lunch":"11:30-15:30","dinner":"19:30-23:00"},"sat":{"lunch":"11:30-15:30","dinner":"19:30-23:00"},"sun":{"lunch":"11:30-15:30","dinner":"19:30-23:00"}}');

INSERT INTO categories (id, restaurant_id, name, description, display_order) VALUES ('cat_nicchie_antipasti', 'rest_le_nicchie', 'Antipasti', '', 0);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_001', 'rest_le_nicchie', 'Antipasti', 'cat_nicchie_antipasti', 'Cozze Gratinate', 'Cozze gratinate, specialita della casa', NULL, true);

INSERT INTO categories (id, restaurant_id, name, description, display_order) VALUES ('cat_nicchie_primi', 'rest_le_nicchie', 'Primi', '', 1);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_002', 'rest_le_nicchie', 'Primi', 'cat_nicchie_primi', 'Cavatelli Zucchine Gamberi e Orata', 'Cavatelli con zucchine, gamberi e orata sfilettata con profumo di arancia e limone', 18, true);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_003', 'rest_le_nicchie', 'Primi', 'cat_nicchie_primi', 'Riso Patate e Cozze', 'Tiella barese: riso, patate e cozze', 14, true);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_004', 'rest_le_nicchie', 'Primi', 'cat_nicchie_primi', 'Spaghetti alle Vongole', 'Spaghetti alle vongole', NULL, true);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_005', 'rest_le_nicchie', 'Primi', 'cat_nicchie_primi', 'Spaghetti all\'Assassina', 'Spaghetti all\'assassina, specialita barese', NULL, true);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_006', 'rest_le_nicchie', 'Primi', 'cat_nicchie_primi', 'Orecchiette', 'Orecchiette della casa', NULL, true);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_007', 'rest_le_nicchie', 'Primi', 'cat_nicchie_primi', 'Lasagna', 'Lasagna della casa', NULL, true);

INSERT INTO categories (id, restaurant_id, name, description, display_order) VALUES ('cat_nicchie_secondi', 'rest_le_nicchie', 'Secondi', '', 2);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_008', 'rest_le_nicchie', 'Secondi', 'cat_nicchie_secondi', 'Orata alla Griglia', 'Orata alla griglia con contorno di patate o insalata', 21, true);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_009', 'rest_le_nicchie', 'Secondi', 'cat_nicchie_secondi', 'Polpo Arrosto Intero', 'Polpo arrosto intero con contorno di patate e insalata', 22, true);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_010', 'rest_le_nicchie', 'Secondi', 'cat_nicchie_secondi', 'Cotoletta di Pollo', 'Cotoletta di pollo con contorno di patate fritte e insalata', 13, true);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_011', 'rest_le_nicchie', 'Secondi', 'cat_nicchie_secondi', 'Zampina di Vitello', 'Zampina di vitello con contorno di peperoni, insalata e patate', 14, true);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_012', 'rest_le_nicchie', 'Secondi', 'cat_nicchie_secondi', 'Entrecote alla Griglia', 'Entrecote alla griglia con insalata e patate', 21, true);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_013', 'rest_le_nicchie', 'Secondi', 'cat_nicchie_secondi', 'Tagliata di Manzo', 'Tagliata di manzo con rucola, grana e pomodorini (350 gr)', 24, true);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_014', 'rest_le_nicchie', 'Secondi', 'cat_nicchie_secondi', 'Braciola di Cavallo', 'Braciola di cavallo cotta al ragu, ripiena di aglio, prezzemolo, pecorino e peperoncino', 13, true);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_015', 'rest_le_nicchie', 'Secondi', 'cat_nicchie_secondi', 'Frittura di Mare Mista', 'Frittura di mare mista', 19, true);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_016', 'rest_le_nicchie', 'Secondi', 'cat_nicchie_secondi', 'Coda di Rospo', 'Coda di rospo', NULL, true);

INSERT INTO categories (id, restaurant_id, name, description, display_order) VALUES ('cat_nicchie_pizze', 'rest_le_nicchie', 'Pizze', '', 3);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_017', 'rest_le_nicchie', 'Pizze', 'cat_nicchie_pizze', 'Pizza Ortolana', 'Pizza ortolana con verdure', NULL, true);

INSERT INTO categories (id, restaurant_id, name, description, display_order) VALUES ('cat_nicchie_dolci', 'rest_le_nicchie', 'Dolci', '', 4);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_018', 'rest_le_nicchie', 'Dolci', 'cat_nicchie_dolci', 'Tiramisu', 'Tiramisu della casa', NULL, true);

INSERT INTO categories (id, restaurant_id, name, description, display_order) VALUES ('cat_nicchie_bevande', 'rest_le_nicchie', 'Bevande', '', 5);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_019', 'rest_le_nicchie', 'Bevande', 'cat_nicchie_bevande', 'Bevande in Lattina', 'Bevande in lattina', 3, true);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_020', 'rest_le_nicchie', 'Bevande', 'cat_nicchie_bevande', 'Acqua da 1L', 'Acqua da 1 litro', 2, true);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_021', 'rest_le_nicchie', 'Bevande', 'cat_nicchie_bevande', 'Calice Vino della Casa', 'Calice di vino della casa', 6, true);
INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, available) VALUES ('dish_nicchie_022', 'rest_le_nicchie', 'Bevande', 'cat_nicchie_bevande', 'Caffe', 'Caffe', 1.2, true);

COMMIT;
