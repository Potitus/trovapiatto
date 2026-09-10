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
    'verdure alla griglia': 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop',
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
    'birra': 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=400&h=300&fit=crop',
    'birra artigianale': 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=400&h=300&fit=crop',
    'vino rosso': 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=400&h=300&fit=crop',
    'vino bianco': 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=400&h=300&fit=crop',
    'acqua': 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?w=400&h=300&fit=crop',
    'coca cola': 'https://images.unsplash.com/photo-1554866585-cd94860890b7?w=400&h=300&fit=crop',
    'espresso': 'https://images.unsplash.com/photo-1510707577719-ae7c14805e3a?w=400&h=300&fit=crop',
    'caffè': 'https://images.unsplash.com/photo-1510707577719-ae7c14805e3a?w=400&h=300&fit=crop',
    'cappuccino': 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=400&h=300&fit=crop',
    
    // PESCE
    'ostriche': 'https://images.unsplash.com/photo-1565557623814-695d41c38d8f?w=400&h=300&fit=crop',
    'aragosta': 'https://images.unsplash.com/photo-1565557623814-695d41c38d8f?w=400&h=300&fit=crop',
    'granchio': 'https://images.unsplash.com/photo-1565557623814-695d41c38d8f?w=400&h=300&fit=crop',
    'zuppa di pesce': 'https://images.unsplash.com/photo-1565557623814-695d41c38d8f?w=400&h=300&fit=crop',
    'risotto ai frutti di mare': 'https://images.unsplash.com/photo-1563379926898-05f4575a45d8?w=400&h=300&fit=crop',
    'spaghetti ai frutti di mare': 'https://images.unsplash.com/photo-1563379926898-05f4575a45d8?w=400&h=300&fit=crop',
    
    // CARNE
    'tagliata': 'https://images.unsplash.com/photo-1558030006-450675393462?w=400&h=300&fit=crop',
    'tagliata di manzo': 'https://images.unsplash.com/photo-1558030006-450675393462?w=400&h=300&fit=crop',
    'filetto': 'https://images.unsplash.com/photo-1558030006-450675393462?w=400&h=300&fit=crop',
    'filetto di manzo': 'https://images.unsplash.com/photo-1558030006-450675393462?w=400&h=300&fit=crop',
    'ossobuco': 'https://images.unsplash.com/photo-1544025162-d76694265947?w=400&h=300&fit=crop',
    'arrosto': 'https://images.unsplash.com/photo-1544025162-d76694265947?w=400&h=300&fit=crop',
    'salsiccia': 'https://images.unsplash.com/photo-1544025162-d76694265947?w=400&h=300&fit=crop',
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
    'panzerotto': 'https://images.unsplash.com/photo-1536964549204-cce9eab227bd?w=400&h=300&fit=crop',
    'taralli': 'https://images.unsplash.com/photo-1572695157366-5e585ab2b69f?w=400&h=300&fit=crop',
    'burrata': 'https://images.unsplash.com/photo-1608897013039-887f21d8c804?w=400&h=300&fit=crop',
    'stracciatella': 'https://images.unsplash.com/photo-1608897013039-887f21d8c804?w=400&h=300&fit=crop',
    'mozzarella': 'https://images.unsplash.com/photo-1608897013039-887f21d8c804?w=400&h=300&fit=crop',
    'mozzarella di bufala': 'https://images.unsplash.com/photo-1608897013039-887f21d8c804?w=400&h=300&fit=crop',
    'lampascioni': 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop',
    'pure di patate': 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=400&h=300&fit=crop'
};

/**
 * Trova l'immagine placeholder per un piatto
 * @param {string} dishName - Nome del piatto
 * @param {string} category - Categoria del piatto (opzionale)
 * @returns {string|null} URL dell'immagine o null
 */
function getDishPlaceholder(dishName, category) {
    if (!dishName) return null;
    
    const nameLower = dishName.toLowerCase().trim();
    
    // Cerca corrispondenza esatta
    if (DISH_PLACEHOLDERS[nameLower]) {
        return DISH_PLACEHOLDERS[nameLower];
    }
    
    // Cerca corrispondenza parziale
    for (const [key, url] of Object.entries(DISH_PLACEHOLDERS)) {
        if (nameLower.includes(key) || key.includes(nameLower)) {
            return url;
        }
    }
    
    // Fallback per categoria
    const categoryLower = (category || '').toLowerCase();
    const categoryFallbacks = {
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
    
    for (const [key, url] of Object.entries(categoryFallbacks)) {
        if (categoryLower.includes(key) || key.includes(categoryLower)) {
            return url;
        }
    }
    
    return null;
}

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
