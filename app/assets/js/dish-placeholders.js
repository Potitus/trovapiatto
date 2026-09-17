/**
 * Dish Image Placeholders
 * Mapping di piatti classici a immagini placeholder
 */
const DISH_PLACEHOLDERS = {
    // PIZZE
    'margherita': 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=400&h=300&fit=crop',
    'pizza margherita': 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=400&h=300&fit=crop',
    'marinara': 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=400&h=300&fit=crop',
    'diavola': 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=400&h=300&fit=crop',
    'quattro stagioni': 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=400&h=300&fit=crop',
    'capricciosa': 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=400&h=300&fit=crop',
    'prosciutto e funghi': 'https://images.unsplash.com/photo-1594007654729-407eedc4be65?w=400&h=300&fit=crop',
    'quattro formaggi': 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=400&h=300&fit=crop',
    'pepperoni': 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=400&h=300&fit=crop',
    'calzone': 'https://images.unsplash.com/photo-1536964549204-cce9eab227bd?w=400&h=300&fit=crop',
    
    // PRIMI
    'spaghetti alle vongole': 'https://images.unsplash.com/photo-1563379926898-05f4575a45d8?w=400&h=300&fit=crop',
    'spaghetti alla carbonara': 'https://images.unsplash.com/photo-1612874742237-6526221588e3?w=400&h=300&fit=crop',
    'carbonara': 'https://images.unsplash.com/photo-1612874742237-6526221588e3?w=400&h=300&fit=crop',
    'spaghetti alla bolognese': 'https://images.unsplash.com/photo-1598866594230-a7c12756260f?w=400&h=300&fit=crop',
    'bolognese': 'https://images.unsplash.com/photo-1598866594230-a7c12756260f?w=400&h=300&fit=crop',
    'risotto ai funghi': 'https://images.unsplash.com/photo-1476124369491-e7addf5db371?w=400&h=300&fit=crop',
    'risotto funghi': 'https://images.unsplash.com/photo-1476124369491-e7addf5db371?w=400&h=300&fit=crop',
    'risotto': 'https://images.unsplash.com/photo-1476124369491-e7addf5db371?w=400&h=300&fit=crop',
    'lasagna': 'https://images.unsplash.com/photo-1574894709920-11b28e7367e3?w=400&h=300&fit=crop',
    'lasagne': 'https://images.unsplash.com/photo-1574894709920-11b28e7367e3?w=400&h=300&fit=crop',
    'pasta al pomodoro': 'https://images.unsplash.com/photo-1598866594230-a7c12756260f?w=400&h=300&fit=crop',
    'gnocchi': 'https://images.unsplash.com/photo-1529692236671-f1f6cf9683ba?w=400&h=300&fit=crop',
    'ravioli': 'https://images.unsplash.com/photo-1587314168485-3236d6710814?w=400&h=300&fit=crop',
    'fettuccine alfredo': 'https://images.unsplash.com/photo-1645112411341-6c4fd023714a?w=400&h=300&fit=crop',
    'spaghetti aglio e olio': 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?w=400&h=300&fit=crop',
    'cacio e pepe': 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?w=400&h=300&fit=crop',
    'pasta e fagioli': 'https://images.unsplash.com/photo-1598866594230-a7c12756260f?w=400&h=300&fit=crop',
    'orecchiette': '/images/dishes/orecchiette-con-le-cime-di-rapa.webp',
    'orecchiette alle cime di rapa': '/images/dishes/orecchiette-con-le-cime-di-rapa.webp',
    'orecchiette con cime di rapa': '/images/dishes/orecchiette-con-le-cime-di-rapa.webp',
    'orecchiette con le cime di rapa': '/images/dishes/orecchiette-con-le-cime-di-rapa.webp',
    'orecchiette con le rape': '/images/dishes/orecchiette-con-le-cime-di-rapa.webp',
    'cavatelli': 'https://images.unsplash.com/photo-1598866594230-a7c12756260f?w=400&h=300&fit=crop',
    
    // SECONDI
    'bistecca': 'https://images.unsplash.com/photo-1558030006-450675393462?w=400&h=300&fit=crop',
    'bistecca alla fiorentina': 'https://images.unsplash.com/photo-1558030006-450675393462?w=400&h=300&fit=crop',
    'branzino': 'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?w=400&h=300&fit=crop',
    'branzino al forno': 'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?w=400&h=300&fit=crop',
    'salmone': 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=400&h=300&fit=crop',
    'salmone alla griglia': 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=400&h=300&fit=crop',
    'tonno': 'https://images.unsplash.com/photo-1599084993091-1cb5c0721cc6?w=400&h=300&fit=crop',
    'tonno alla griglia': 'https://images.unsplash.com/photo-1599084993091-1cb5c0721cc6?w=400&h=300&fit=crop',
    'pollo arrosto': 'https://images.unsplash.com/photo-1598103442097-8b74394b95c6?w=400&h=300&fit=crop',
    'pollo alla griglia': 'https://images.unsplash.com/photo-1598103442097-8b74394b95c6?w=400&h=300&fit=crop',
    'agnello': 'https://images.unsplash.com/photo-1544025162-d76694265947?w=400&h=300&fit=crop',
    'costolette di agnello': 'https://images.unsplash.com/photo-1544025162-d76694265947?w=400&h=300&fit=crop',
    'frittura di pesce': 'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?w=400&h=300&fit=crop',
    'calamari': 'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?w=400&h=300&fit=crop',
    'calamari fritti': 'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?w=400&h=300&fit=crop',
    'gamberi': 'https://images.unsplash.com/photo-1565557623814-695d41c38d8f?w=400&h=300&fit=crop',
    'gamberoni': 'https://images.unsplash.com/photo-1565557623814-695d41c38d8f?w=400&h=300&fit=crop',
    
    // ANTIPASTI
    'bruschetta': 'https://images.unsplash.com/photo-1572695157366-5e585ab2b69f?w=400&h=300&fit=crop',
    'antipasto misto': 'https://images.unsplash.com/photo-1572695157366-5e585ab2b69f?w=400&h=300&fit=crop',
    'caprese': 'https://images.unsplash.com/photo-1608897013039-887f21d8c804?w=400&h=300&fit=crop',
    'insalata caprese': 'https://images.unsplash.com/photo-1608897013039-887f21d8c804?w=400&h=300&fit=crop',
    'carpaccio': 'https://images.unsplash.com/photo-1572695157366-5e585ab2b69f?w=400&h=300&fit=crop',
    'carpaccio di manzo': 'https://images.unsplash.com/photo-1572695157366-5e585ab2b69f?w=400&h=300&fit=crop',
    'cozze': 'https://images.unsplash.com/photo-1565557623814-695d41c38d8f?w=400&h=300&fit=crop',
    'cozze e vongole': 'https://images.unsplash.com/photo-1565557623814-695d41c38d8f?w=400&h=300&fit=crop',
    'vongole': 'https://images.unsplash.com/photo-1565557623814-695d41c38d8f?w=400&h=300&fit=crop',
    'prosciutto e melone': 'https://images.unsplash.com/photo-1572695157366-5e585ab2b69f?w=400&h=300&fit=crop',
    
    // CONTORNI
    'insalata': 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop',
    'insalata mista': 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop',
    'patatine fritte': 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=400&h=300&fit=crop',
    'patate al forno': 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=400&h=300&fit=crop',
    'verdure alla griglia': '/images/dishes/verdure-grigliate.jpg',
    'verdure grigliate': '/images/dishes/verdure-grigliate.jpg',
    'funghi gratinati': '/images/dishes/funghi-gratinati.jpg',
    'funghi': '/images/dishes/funghi-gratinati.jpg',
    'olive con pomodoro': '/images/dishes/olive-con-pomodoro.jpg',
    'olive con pomodorini': '/images/dishes/olive-con-pomodoro.jpg',
    'olive nere': '/images/dishes/olive-con-pomodoro.jpg',
    'skattata': '/images/dishes/Sckattata.jpg',
    'sckattata': '/images/dishes/Sckattata.jpg',
    'bresaola': '/images/dishes/bresaola-punta-anca.webp',
    'olive': '/images/dishes/olive-in-acqua.jpg',
    'spinaci': 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop',
    
    // DOLCI
    'tiramisu': 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=400&h=300&fit=crop',
    'tiramisù': 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=400&h=300&fit=crop',
    'panna cotta': 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=400&h=300&fit=crop',
    'gelato': 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=400&h=300&fit=crop',
    'cannoli': 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=400&h=300&fit=crop',
    'cannoli siciliani': 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=400&h=300&fit=crop',
    'cheesecake': 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=400&h=300&fit=crop',
    'profiterole': 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=400&h=300&fit=crop',
    'cioccolato fondente': 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=400&h=300&fit=crop',
    
    // BEVANDE
    'birra': '/images/dishes/birra-peroni.png',
    'birra artigianale': '/images/dishes/birra-peroni.png',
    'peroni': '/images/dishes/birra-peroni.png',
    'nastro azzurro': '/images/dishes/birra_nastro_azzurro.webp',
    'tennet': '/images/dishes/birra-peroni.png',
    'tennent': '/images/dishes/birra-peroni.png',
    'crest': '/images/dishes/birra-peroni.png',
    'the': '/images/dishes/the-lattina.jpg',
    'tè': '/images/dishes/the-lattina.jpg',
    'te limone': '/images/dishes/the-lattina.jpg',
    'te pesca': '/images/dishes/the-lattina.jpg',
    'the limone': '/images/dishes/the-lattina.jpg',
    'the pesca': '/images/dishes/the-lattina.jpg',
    'the alla pesca': '/images/dishes/the-lattina.jpg',
    'limone': '/images/dishes/the-lattina.jpg',
    'pesca': '/images/dishes/the-lattina.jpg',
    'sprite': '/images/dishes/sprite.jpg',
    'bibita in lattina': '/images/dishes/bibite-in-lattina.jpg',
    'bibite in lattina': '/images/dishes/bibite-in-lattina.jpg',
    'lattina': '/images/dishes/bibite-in-lattina.jpg',
    'coca cola': '/images/dishes/bibite-in-lattina.jpg',
    'coca zero': '/images/dishes/bibite-in-lattina.jpg',
    'vino rosso': 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=400&h=300&fit=crop',
    'vino bianco': 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=400&h=300&fit=crop',
    'acqua naturale': '/images/dishes/acqua-naturale.jpg',
    'acqua frizzante': '/images/dishes/acqua-frizzante.jpg',
    'acqua': 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?w=400&h=300&fit=crop',
    'espresso': 'https://images.unsplash.com/photo-1510707577719-ae7c14805e3a?w=400&h=300&fit=crop',
    'caffè': 'https://images.unsplash.com/photo-1510707577719-ae7c14805e3a?w=400&h=300&fit=crop',
    'cappuccino': 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=400&h=300&fit=crop',
    
    // PESCE
    'ostriche': 'https://images.unsplash.com/photo-1565557623814-695d41c38d8f?w=400&h=300&fit=crop',
    'aragosta': 'https://images.unsplash.com/photo-1565557623814-695d41c38d8f?w=400&h=300&fit=crop',
    'granchio': 'https://images.unsplash.com/photo-1565557623814-695d41c38d8f?w=400&h=300&fit=crop',
    'polpo alla griglia': '/images/dishes/polpo_alla_griglia.jpg',
    'polpo': '/images/dishes/polpo_alla_griglia.jpg',
    'zuppa di pesce': 'https://images.unsplash.com/photo-1565557623814-695d41c38d8f?w=400&h=300&fit=crop',
    'risotto ai frutti di mare': 'https://images.unsplash.com/photo-1563379926898-05f4575a45d8?w=400&h=300&fit=crop',
    'spaghetti ai frutti di mare': 'https://images.unsplash.com/photo-1563379926898-05f4575a45d8?w=400&h=300&fit=crop',
    'spaghetti ai ricci': '/images/dishes/spaghetti-ai-ricci.jpg',

// CARNE
    'tagliata': 'https://images.unsplash.com/photo-1558030006-450675393462?w=400&h=300&fit=crop',
    'tagliata di manzo': 'https://images.unsplash.com/photo-1558030006-450675393462?w=400&h=300&fit=crop',
    'filetto': 'https://images.unsplash.com/photo-1558030006-450675393462?w=400&h=300&fit=crop',
    'filetto di manzo': 'https://images.unsplash.com/photo-1558030006-450675393462?w=400&h=300&fit=crop',
    'bistecca alla fiorentina': '/images/dishes/bistecche.jpeg',
    'ossobuco': 'https://images.unsplash.com/photo-1544025162-d76694265947?w=400&h=300&fit=crop',
    'arrosto': 'https://images.unsplash.com/photo-1544025162-d76694265947?w=400&h=300&fit=crop',
    'salsiccia': '/images/dishes/salsiccia.webp',
    'bombette': '/images/dishes/bombette.jpeg',
    'bombetta': '/images/dishes/bombette.jpeg',
    'braciola': '/images/dishes/braciola.jpeg',
    'grigliata mista': '/images/dishes/grigliatamista.jpeg',
    'grigliata': '/images/dishes/grigliatamista.jpeg',
    'spiedini': '/images/dishes/spiedini-verdure-carne.jpg',
    'spiedini misti': '/images/dishes/spiedini-verdure-carne.jpg',
    'zampina': '/images/dishes/zampina-enzociro.jpeg',
    'arrosticini': '/images/dishes/spiedini-verdure-carne.jpg',
    'hamburger': 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=300&fit=crop',
    
    // CUCINA ETNICA
    'sushi': 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=400&h=300&fit=crop',
    'sashimi': 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=400&h=300&fit=crop',
    'maki': 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=400&h=300&fit=crop',
    'pad thai': 'https://images.unsplash.com/photo-1559314809-0d155014e29e?w=400&h=300&fit=crop',
    'curry': 'https://images.unsplash.com/photo-1565557623814-695d41c38d8f?w=400&h=300&fit=crop',
    'tacos': 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=400&h=300&fit=crop',
    'kebab': 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=400&h=300&fit=crop',
    'falafel': 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=400&h=300&fit=crop',
    
    // CUCINA PUGLIESE SPECIFICA
    'focaccia': '/images/dishes/focaccia-barese.jpg',
    'focaccia barese': '/images/dishes/focaccia-barese.jpg',
    'panzerotto': '/images/dishes/panino-salsiccia.jpg',
    'panzerottini': '/images/dishes/panino-salsiccia.jpg',
    'taralli': 'https://images.unsplash.com/photo-1572695157366-5e585ab2b69f?w=400&h=300&fit=crop',
    'burrata': 'https://images.unsplash.com/photo-1608897013039-887f21d8c804?w=400&h=300&fit=crop',
    'stracciatella': '/images/dishes/stracciatella.webp',
    'stacciatella': '/images/dishes/stracciatella.webp',
    'mozzarella': 'https://images.unsplash.com/photo-1608897013039-887f21d8c804?w=400&h=300&fit=crop',
    'mozzarella di bufala': 'https://images.unsplash.com/photo-1608897013039-887f21d8c804?w=400&h=300&fit=crop',
    'lampascioni': 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop',
    'pure di patate': 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=400&h=300&fit=crop',

// PANINI / STREET FOOD (soleluna e simili)
    'panino con salsiccia': '/images/dishes/panino-salsiccia.jpg',
    'panino con wurstel': '/images/dishes/wurstel.jpg',
    'panino con hamburger': 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=300&fit=crop',
    'panino con cotoletta': '/images/dishes/panino-cotoletta.jpg',
    'panino con bombette': '/images/dishes/bombette.jpeg',
    'panino con capocollo': '/images/dishes/panino-boscaiolo.jpg',
    'panino con porchetta': '/images/dishes/panino-boscaiolo.jpg',
    'panino con pollo': '/images/dishes/panino-filettopollo.jpeg',
    'panino con arrosticini': '/images/dishes/spiedini-verdure-carne.jpg',
    'panino con zampina': '/images/dishes/zampina-enzociro.jpeg',
    'panino con pancetta': '/images/dishes/panino-boscaiolo.jpg',
    'panino con norcia': '/images/dishes/panino-prosciutto-cotto.jpg',
    'panino con prosciutto': '/images/dishes/panino-prosciutto-cotto.jpg',
    'prosciutto crudo': '/images/dishes/prosciutto-parma.jpg',
    'prosciutto di parma': '/images/dishes/prosciutto-parma.jpg',
    'panino': '/images/dishes/panino-boscaiolo.jpg',
    'panino vegetariano': '/images/dishes/veggy.jpg',
    'panino vegano': '/images/dishes/veggy.jpg',
    'filetto pollo': '/images/dishes/panino-filettopollo.jpeg',
    'nuggets': '/images/dishes/panino-filettopollo.jpeg',
    'nuggets di pollo': '/images/dishes/panino-filettopollo.jpeg',
    // Alias single-token per fuzzy/exact su nomi brevi (es. "Würstel" senza "panino con")
    'bistecca': '/images/dishes/bistecche.jpeg',
    'bistecche': '/images/dishes/bistecche.jpeg',
    'wurstel': '/images/dishes/wurstel.jpg',
    'cotoletta': '/images/dishes/panino-cotoletta.jpg',
    'boscaiolo': '/images/dishes/panino-boscaiolo.jpg',
    'salsiccia con friarielli': '/images/dishes/panino-salsiccia.jpg',
    // Foto reali dai menu dei ristoranti (La Muraya, Bari)
    'capocollo di martina franca': '/images/dishes/muraya-capocollo.jpg',
    'capocollo': '/images/dishes/prosciutto-parma.jpg',
    'insalata di mare': '/images/dishes/muraya-insalata-di-mare.jpg',
    'insalata di moscardini': '/images/dishes/muraya-moscardini.jpg',
    'misto fritto in tempura': '/images/dishes/muraya-misto-tempura.jpg',
    'misto fritto': '/images/dishes/muraya-misto-tempura.jpg',
    // Illustrazioni ad hoc per categoria (mai una foto assurda)
    'primi di mare': '/images/dishes/illustrations/primi-mare.svg',
    'primi di terra': '/images/dishes/illustrations/primi-mare.svg',
    'secondi di pesce': '/images/dishes/illustrations/secondi-pesce.svg',
    'secondi di terra': '/images/dishes/illustrations/secondi-pesce.svg',
    'antipasti di mare': '/images/dishes/illustrations/mare.svg',
    'antipasto di mare': '/images/dishes/illustrations/mare.svg',
    'antipasto di frutti di mare': '/images/dishes/illustrations/mare.svg',
    'frutta e dolci': '/images/dishes/illustrations/dolci.svg',
    // Fritture -> foto reale di frittura (mai bevande/dolci per assonanza)
    'frittura': '/images/dishes/muraya-misto-tempura.jpg',
    'frittura mista di pesce': '/images/dishes/muraya-misto-tempura.jpg',
    'frittura di calamari': '/images/dishes/muraya-misto-tempura.jpg',
    'frittura di paranzella': '/images/dishes/muraya-misto-tempura.jpg',
    // Pesce specifico (niente foto di dolci/bevande per assonanza)
    'pescato del giorno': 'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?w=400&h=300&fit=crop',
    'pesce spada': 'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?w=400&h=300&fit=crop',
    'tagliata di tonno': 'https://images.unsplash.com/photo-1599084993091-1cb5c0721cc6?w=400&h=300&fit=crop',
    'carpaccio di pesce': 'https://images.unsplash.com/photo-1599084993091-1cb5c0721cc6?w=400&h=300&fit=crop',
    // Paste ripiene/farcite (niente salumi in foto per piatti di pasta)
    'spaghetti al nero di seppia': 'https://images.unsplash.com/photo-1563379926898-05f4575a45d8?w=400&h=300&fit=crop',
    'tubettone': 'https://images.unsplash.com/photo-1598866594230-a7c12756260f?w=400&h=300&fit=crop',
    'agnolotti': 'https://images.unsplash.com/photo-1587314168485-3236d6710814?w=400&h=300&fit=crop',
    'mezzemaniche fiori di zucchina e capocollo': 'https://images.unsplash.com/photo-1598866594230-a7c12756260f?w=400&h=300&fit=crop',
    'spaghetto cacio pepe e capocollo': 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?w=400&h=300&fit=crop',
    // Dolci specifici (illustrazione onesta al posto di foto di altri dolci)
    'crostata': '/images/dishes/illustrations/dolci.svg',
    'sporcamuss': '/images/dishes/illustrations/dolci.svg',
    'souffle': '/images/dishes/illustrations/dolci.svg',
    'souffle al cioccolato': '/images/dishes/illustrations/dolci.svg',
    'cassata': '/images/dishes/illustrations/dolci.svg',
    'cassata di tommasino': '/images/dishes/illustrations/dolci.svg',
    'tartufo': '/images/dishes/illustrations/dolci.svg',
    'tartufo di pizzo': '/images/dishes/illustrations/dolci.svg',
    'frutta di stagione': '/images/dishes/illustrations/dolci.svg',
    'cannolo': '/images/dishes/illustrations/dolci.svg',
    'cannolo alle mandorle': '/images/dishes/illustrations/dolci.svg',
    'gamberoni grigliati': '/images/dishes/gamberoni-grigliati.webp'
};

/**
 * Normalizzazione condivisa: minuscole, senza accenti, spazi collassati.
 * "Tiramisù" -> "tiramisu", "Würstel" -> "wurstel", "Orecchiette  con" -> "orecchiette con"
 */
function normalizeDishName(s) {
    return String(s || '')
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[’‘`´]/g, "'")
        .replace(/[^a-z0-9\s']/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

function escapeRegExp(s) {
    return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

/**
 * Levenshtein con early-exit su maxDist (per fuzzy leggero senza dipendenze).
 */
function dishLevenshtein(a, b, maxDist) {
    if (a === b) return 0;
    var la = a.length, lb = b.length;
    if (Math.abs(la - lb) > maxDist) return maxDist + 1;
    if (la === 0) return lb;
    if (lb === 0) return la;
    var prev = new Array(lb + 1), cur = new Array(lb + 1), i, j, cost, min;
    for (j = 0; j <= lb; j++) prev[j] = j;
    for (i = 1; i <= la; i++) {
        cur[0] = i;
        min = cur[0];
        for (j = 1; j <= lb; j++) {
            cost = a.charAt(i - 1) === b.charAt(j - 1) ? 0 : 1;
            cur[j] = Math.min(prev[j] + 1, cur[j - 1] + 1, prev[j - 1] + cost);
            if (cur[j] < min) min = cur[j];
        }
        if (min > maxDist) return maxDist + 1;
        var tmp = prev; prev = cur; cur = tmp;
    }
    return prev[lb];
}

// Mappa chiavi normalizzate -> url, ordinata per specificità (chiavi lunghe prima)
var _DISH_NORM_ENTRIES = null;
function getDishNormEntries() {
    if (_DISH_NORM_ENTRIES) return _DISH_NORM_ENTRIES;
    _DISH_NORM_ENTRIES = Object.entries(DISH_PLACEHOLDERS).map(function(pair) {
        return { key: normalizeDishName(pair[0]), url: pair[1] };
    }).filter(function(e) { return e.key; });
    _DISH_NORM_ENTRIES.sort(function(a, b) { return b.key.length - a.key.length; });
    return _DISH_NORM_ENTRIES;
}

/**
 * Trova l'immagine placeholder per un piatto.
 * Ordine: 1) match esatto normalizzato 2) word-boundary (niente sottostringhe:
 * "pizzaiola" != pizza, "mist" != "misto") 3) fuzzy su singoli token 4) fallback categoria
 */
function getDishPlaceholder(dishName, category) {
    if (!dishName) return null;

    var n = normalizeDishName(dishName);
    if (!n) return null;
    var entries = getDishNormEntries();
    var i, e;

    // 1) Corrispondenza esatta normalizzata
    for (i = 0; i < entries.length; i++) {
        if (n === entries[i].key) return entries[i].url;
    }

    // 2) Match a parole intere (chiavi lunghe prima = più specifiche prima)
    for (i = 0; i < entries.length; i++) {
        e = entries[i];
        // ignora chiavi troppo corte per l'includes generico
        if (e.key.length < 4) continue;
        try {
            if (new RegExp('\\b' + escapeRegExp(e.key) + '\\b').test(n)) return e.url;
        } catch (err) {
            if (n.indexOf(e.key) !== -1) return e.url;
        }
    }

    // 3) Fuzzy su singoli token: gestisce refusi e varianti
    // es. "sckattata/skattata", "tiramisu/tiramissu", "wurstel/wursthel", "stacciatella/stracciatella"
    // Guard: stesse prime 2 lettere (evita assurdità tipo "martina"~"lattina")
    var tokens = n.split(' ').filter(function(t) { return t.length >= 4; });
    var best = null, bestDist = 3, bestLen = 0;
    for (var t = 0; t < tokens.length; t++) {
        var tok = tokens[t];
        var maxD = tok.length <= 5 ? 1 : 2;
        for (i = 0; i < entries.length; i++) {
            e = entries[i];
            if (e.key.indexOf(' ') !== -1) continue; // solo chiavi single-token qui
            if (e.key.length < 4) continue;
            if (e.key.slice(0, 2) !== tok.slice(0, 2)) continue;
            if (Math.abs(tok.length - e.key.length) > maxD) continue;
            var d = dishLevenshtein(tok, e.key, maxD);
            if (d <= maxD && (d < bestDist || (d === bestDist && e.key.length > bestLen))) {
                bestDist = d; best = e.url; bestLen = e.key.length;
            }
        }
    }
    if (best) return best;
    
    // Fallback per categoria (match normalizzato a parole intere)
    var categoryNorm = normalizeDishName(category || '');
    var categoryFallbacks = {
        'antipasti': 'https://images.unsplash.com/photo-1572695157366-5e585ab2b69f?w=400&h=300&fit=crop',
        'primi': 'https://images.unsplash.com/photo-1598866594230-a7c12756260f?w=400&h=300&fit=crop',
        'secondi': 'https://images.unsplash.com/photo-1558030006-450675393462?w=400&h=300&fit=crop',
        'pizze': 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=400&h=300&fit=crop',
        'pizza': 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=400&h=300&fit=crop',
        'dolci': 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=400&h=300&fit=crop',
        'contorni': 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop',
        'insalate': 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop',
        'bevande': 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=400&h=300&fit=crop',
        'pesce': 'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?w=400&h=300&fit=crop',
        'carne': 'https://images.unsplash.com/photo-1558030006-450675393462?w=400&h=300&fit=crop'
    };

    if (categoryNorm) {
        var catKeys = Object.keys(categoryFallbacks).sort(function(a, b) { return b.length - a.length; });
        for (var c = 0; c < catKeys.length; c++) {
            try {
                if (new RegExp('\\b' + escapeRegExp(catKeys[c]) + '\\b').test(categoryNorm)) {
                    return categoryFallbacks[catKeys[c]];
                }
            } catch (err) {
                if (categoryNorm.indexOf(catKeys[c]) !== -1) return categoryFallbacks[catKeys[c]];
            }
        }
    }

    return null;
}

// Alias unificato: le pagine storiche usavano getDishImage(name) con logiche duplicate.
// Ora puntano tutte alla stessa implementazione normalizzata + fuzzy.
try {
    if (typeof globalThis !== 'undefined' && typeof globalThis.getDishImage === 'undefined') {
        globalThis.getDishImage = getDishPlaceholder;
    }
} catch (e) { /* ambiente senza globalThis */ }

/**
 * Applica immagini placeholder ai piatti che non hanno immagine
 * @param {Array} dishes - Array di piatti
 * @returns {Array} Piatti con immagini aggiunte
 */
function applyDishPlaceholders(dishes) {
    return dishes.map(dish => {
        if (!dish.image_url) {
            const placeholder = getDishPlaceholder(dish.name, dish.category);
            if (placeholder) {
                dish.image_url = placeholder;
            }
        }
        return dish;
    });
}
