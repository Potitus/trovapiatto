# Menu Template - Trovapiatto.it

All restaurant menus MUST use the same layout as Enzo & Ciro (`menu/enzociro/index.html`).

## How to create a new menu:

1. Copy `menu/enzociro/index.html`
2. Change `RESTAURANT_SLUG` on line 862 to the new restaurant slug
3. Update the header background image URL (line 789) if available
4. Update the modal offers image URL (line 1863) if available
5. Save as `menu/[restaurant-slug]/index.html`

## Configuration Variables:
- `RESTAURANT_SLUG` - The restaurant's database slug (required)
- Header background image - Optional restaurant header image
- Modal offers image - Optional promotional image

## Assets used:
- CSS: leggimenu.it CDN (bootstrap, style, custom, cart)
- JS: jQuery, Bootstrap, leggimenu scripts
- Fonts: Google Fonts (Roboto, Source Sans Pro)
- Icons: FontAwesome

## Categories (IMPORTANT - Always create categories before adding dishes):

Categories MUST be created via API before adding dishes. The menu template uses `category_id` to group dishes.

### Standard Categories (display_order):
1. Antipasti
2. Primi
3. Secondi
4. Contorni
5. Dolci
6. Bevande

### How to create categories:
```
POST /app/admin/api.php?action=add-category
Body: {"restaurant_id": "rest_xxx", "name": "Antipasti", "display_order": 1}
```

### How to assign dishes to categories:
When adding/updating a dish, include `category_id`:
```
POST /app/admin/api.php?action=update-dish
Body: {"id": "dish_xxx", "category_id": "cat_xxx", "category": "Antipasti"}
```

### Category IDs for reference:
- I Due Ghiottoni: Antipasti=cat_50be7d0d1604, Primi=cat_fa8f37bc33e5, Secondi=cat_89f587ec3060, Contorni=cat_6066aa53d06b, Dolci=cat_4e07a0190538, Bevande=cat_a428d37f1097
- Osteria delle Travi: Antipasti=cat_84dff3d99267, Primi=cat_8b441c028e26, Secondi=cat_1c668f12eeb7, Contorni=cat_b1adc8fcfc23, Dolci=cat_ac6355980c7e, Bevande=cat_c58af71a8f42
