-- Menu Zia Teresa (79 voci, da foto menu) - ESEGUIRE UNA SOLA VOLTA
-- Richiede riga ristorante con slug='zia-teresa'
INSERT INTO dishes (id, restaurant_id, category, name, description, price, available, allergens)
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Coperto', 'Coperto', NULL, 2.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Antipasti', 'Cozze gratinate', NULL, 6.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Antipasti', 'Cozze alla marinara', NULL, 6.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Antipasti', 'Prosciutto alla barese', NULL, 8.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Antipasti', 'Bresaola', NULL, 8.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Antipasti', 'Salumi misti', NULL, 8.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Antipasti', 'Insalata di mare', NULL, 10.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Antipasti', 'Cocktail di gamberi', NULL, 12.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Antipasti', 'Espresso casa (min. due porzioni)', NULL, 12.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Antipasti', 'Frutti di mare crudi', NULL, 14.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Primi piatti', 'Orecchiette alla barese', NULL, 8.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Primi piatti', 'Spaghetti alle cozze', NULL, 12.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Primi piatti', 'Spaghetti al cartoccio', NULL, 12.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Primi piatti', 'Spaghetti alle vongole', NULL, 12.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Primi piatti', 'Tubettini marinati', NULL, 12.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Primi piatti', 'Cavatelli alla pescatora', NULL, 12.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Primi piatti', 'Risotto alla pescatora', NULL, 12.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Primi piatti', 'Risotto alla zingara', NULL, 12.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Primi piatti', 'Bavettine agli scampi', NULL, 14.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Primi piatti', 'Tagliolini alla cicala greca', NULL, 14.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Primi piatti', 'Calamarata al gambero rosso', NULL, 14.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Primi piatti', 'Tagliolini all''astice', NULL, 20.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Primi piatti', 'Zuppa di pesce', NULL, 25.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Primi piatti', 'Piatto del giorno', NULL, 12.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Secondi', 'Pescato locale giornaliero (al kg)', 'Prezzo al chilo, pescato del giorno', 60.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Secondi', 'Pesce 2° scelta (al kg)', 'Prezzo al chilo', 40.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Secondi', 'Scampi (al kg)', 'Prezzo al chilo', 70.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Secondi', 'Astice (al kg)', 'Prezzo al chilo', 80.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Secondi', 'Aragosta (al kg)', 'Prezzo al chilo', 90.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Secondi', 'Bistecca ai ferri', NULL, 8.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Secondi', 'Scaloppine', NULL, 8.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Secondi', 'Bistecca di bovino', NULL, 12.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Secondi', 'Gamberoni alla brace', NULL, 12.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Secondi', 'Pesce spada', NULL, 12.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Secondi', 'Polpo arrosto', NULL, 12.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Secondi', 'Frittura mista', NULL, 12.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Secondi', 'Grigliata mista di pesce', NULL, 14.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Formaggi e contorni', 'Insalata verde', NULL, 3.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Formaggi e contorni', 'Patate fritte', NULL, 3.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Formaggi e contorni', 'Crudité', NULL, 4.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Formaggi e contorni', 'Burratina', NULL, 4.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Formaggi e contorni', 'Insalata capricciosa', NULL, 4.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Formaggi e contorni', 'Bufaletta', NULL, 4.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Formaggi e contorni', 'Grana', NULL, 4.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Formaggi e contorni', 'Bocconcini', NULL, 4.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Formaggi e contorni', 'Tris misto (Grana, mozzarella e provolone)', NULL, 5.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Dessert', 'Caffè', NULL, 1.50, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Dessert', 'Liquori nazionali', NULL, 3.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Dessert', 'Sorbetto', NULL, 3.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Dessert', 'Frutta di stagione', NULL, 3.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Dessert', 'Liquori esteri', NULL, 4.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Dessert', 'Macedonia con gelato', NULL, 4.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Dessert', 'Dolce al carrello', NULL, 4.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Bevande', 'Acqua minerale', NULL, 2.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Bevande', 'Birra, Coca, Fanta piccola', NULL, 2.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Bevande', 'Birra media', NULL, 4.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Bevande', 'Coca Cola, Aranciata (1 litro)', NULL, 4.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Bevande', 'Vino locale in caraffa (1/2 litro)', NULL, 5.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Bevande', 'Vino locale in caraffa (1 litro)', NULL, 8.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Bevande', 'Vino locale in bottiglia', NULL, 10.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'Margherita', 'Pomodoro, mozzarella, formaggio', 6.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'Napoletana', 'Pomodoro, mozzarella, acciuga', 6.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'Romana', 'Pomodoro, mozzarella, acciughe, capperi', 8.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'Boom', 'Pomodoro, mozzarella, funghi, tonno', 8.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'Capricciosa', 'Pomodoro, mozzarella, carciofini, funghi, prosciutto', 8.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'Quattro stagioni', 'Pomodoro, mozzarella, carciofini, funghi, acciughe, capperi, prosciutto', 8.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'Crudaiola', 'Pomodoro fresco, mozzarella, basilico, ricotta marzotica, rucola', 8.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'Calabrese', 'Pomodoro, mozzarella, salame piccante, peperoncino', 8.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'Fumé', 'Pomodoro, mozzarella, scamorza affumicata, speck', 8.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'Norcia', 'Pomodoro, mozzarella, formaggio, salsiccia di Norcia', 8.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'Quattro formaggi', 'Pomodoro, mozzarella, gorgonzola, formaggio fresco, grana', 8.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'Dellera', 'Pomodoro, mozzarella, carne', 8.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'Salmone', 'Pomodoro, mozzarella, salmone', 9.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'Bresaola', 'Pomodoro, mozzarella, formaggio, bresaola', 9.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'Stracciatella e crudo', 'Pomodoro, mozzarella, stracciatella e prosciutto crudo', 9.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'San Daniele', 'Pomodoro, mozzarella, prosciutto San Daniele', 9.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'A modo mio', 'Max quattro ingredienti a scelta', 9.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa'
UNION ALL
SELECT CONCAT('dish_', REPLACE(UUID(), '-', '')), r.id, 'Pizzeria', 'Pescatora', 'Pomodoro, mozzarella, frutti di mare', 10.00, 1, '[]' FROM restaurants r WHERE r.slug = 'zia-teresa';