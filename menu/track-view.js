/**
 * Page View Tracker - Traccia visite sulle pagine menu
 * Aggiungere questo script a ogni pagina menu
 */
(function() {
    const TRACKING_URL = '/app/admin/api-crud.php';
    
    function getRestaurantId() {
        const match = window.location.pathname.match(/\/menu\/([^\/]+)/);
        return match ? match[1] : null;
    }

    function getDishId() {
        const params = new URLSearchParams(window.location.search);
        return params.get('dish') || null;
    }

    function trackView() {
        const restaurantId = getRestaurantId();
        if (!restaurantId) return;

        const data = {
            restaurant_id: restaurantId,
            dish_id: getDishId(),
            page_type: getDishId() ? 'dish' : 'menu'
        };

        if (navigator.sendBeacon) {
            const blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
            navigator.sendBeacon(TRACKING_URL + '?action=track-view', blob);
        } else {
            fetch(TRACKING_URL + '?action=track-view', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
                keepalive: true
            }).catch(() => {});
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', trackView);
    } else {
        trackView();
    }
})();
