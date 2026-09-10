/*
inspiration
https://cz.pinterest.com/pin/830703093790696716/
*/

// Modal functions
function openMenuModal(title, menuUrl) {
  const modal = document.getElementById('menuModal');
  const modalTitle = document.getElementById('modalTitle');
  const menuIframe = document.getElementById('menuIframe');
  
  modalTitle.textContent = title;
  menuIframe.src = menuUrl;
  modal.classList.add('show');
  document.body.style.overflow = 'hidden';
}

function closeMenuModal() {
  const modal = document.getElementById('menuModal');
  modal.classList.remove('show');
  document.getElementById('menuIframe').src = '';
  document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
  const modal = document.getElementById('menuModal');
  if (event.target === modal) {
    closeMenuModal();
  }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closeMenuModal();
  }
});

var swiper = new Swiper(".swiper", {
  effect: "coverflow",
  grabCursor: true,
  spaceBetween: 30,
  centeredSlides: false,
  coverflowEffect: {
    rotate: 0,
    stretch: 0,
    depth: 0,
    modifier: 1,
    slideShadows: false
  },
  loop: true,
  pagination: {
    el: ".swiper-pagination",
    clickable: true
  },
  keyboard: {
    enabled: true
  },
  mousewheel: {
    thresholdDelta: 70
  },
  breakpoints: {
    460: {
      slidesPerView: 3
    },
    768: {
      slidesPerView: 3
    },
    1024: {
      slidesPerView: 3
    },
    1600: {
      slidesPerView: 3.6
    }
  }
});

// Initialize the map
document.addEventListener('DOMContentLoaded', function() {
  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      if (href !== '#' && document.querySelector(href)) {
        e.preventDefault();
        document.querySelector(href).scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });

  // Create map centered on Italy
  var map = L.map('map').setView([41.9028, 12.4964], 6);

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
  }).addTo(map);

  // Carica i ristoranti dall'API
  fetch('/app/admin/get-restaurants.php')
    .then(response => {
      if (!response.ok) {
        console.error('API Error:', response.status);
        throw new Error('API Response: ' + response.status);
      }
      return response.json();
    })
    .then(data => {
      var restaurants = data.data || [];
      console.log('Ristoranti caricati:', restaurants.length);
      
      if (!restaurants || restaurants.length === 0) {
        console.warn('Nessun ristorante trovato');
        return;
      }
      
      // Aggiungi i marker per ogni ristorante
      restaurants.forEach(function(restaurant) {
        if (!restaurant.latitude || !restaurant.longitude) return;
        
        const bubbleHtml = `
          <div style="width:60px;height:60px;background:white;border:3px solid #e73a3a;border-radius:50%;overflow:hidden;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,0.25);transition:all 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 6px 20px rgba(231, 58, 58, 0.4)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.25)';">
            <img src="https://www.trovapiatto.it/logo/logo_ufficiale.png" alt="${restaurant.name}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;padding:4px;background:#f0f0f0;">
          </div>
        `;
        
        var marker = L.marker([parseFloat(restaurant.latitude), parseFloat(restaurant.longitude)], {
          icon: L.divIcon({
            html: bubbleHtml,
            iconSize: [60, 60],
            iconAnchor: [30, 60],
            popupAnchor: [0, -60]
          })
        }).addTo(map);
        
        // Create popup content
        var popupContent = `
          <div style="text-align: center; padding: 15px; min-width: 280px;">
            <h3 style="color: #461356; margin: 0 0 10px 0; font-size: 1.15rem; font-weight: 700;">${restaurant.name}</h3>
            <div style="display: flex; gap: 10px; justify-content: center; margin-bottom: 10px; flex-wrap: wrap;">
              <span style="background: #e73a3a; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500;"><i class="fa-solid fa-location-dot"></i> ${restaurant.city || 'N/A'}</span>
              <span style="background: #2d1b3d; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem;"><i class="fa-solid fa-utensils"></i> ${restaurant.cuisine || 'Mista'}</span>
            </div>
            <p style="margin: 10px 0; color: #333; font-size: 0.9rem; line-height: 1.4;"><i class="fa-solid fa-star" style="color: #e73a3a;"></i> ${restaurant.rating || 'N/A'} | <i class="fa-solid fa-plate-wheat" style="color: #e73a3a;"></i> ${restaurant.dishes_count || 0} piatti</p>
            <div style="margin-top: 8px;">
              <a href="https://www.trovapiatto.it/menu/${restaurant.slug}/" style="display: inline-block; background: #e73a3a; color: white; padding: 8px 16px; border-radius: 20px; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: all 0.3s ease;" onmouseover="this.style.background='#c92a2a'; this.style.transform='scale(1.05)';" onmouseout="this.style.background='#e73a3a'; this.style.transform='scale(1)';"><i class="fa-solid fa-book"></i> Menu</a>
            </div>
          </div>
        `;
        
        marker.bindPopup(popupContent, { maxWidth: 320 });
        
        // Add hover effect
        marker.on('mouseover', function() {
          this.openPopup();
        });
      });
    })
    .catch(error => console.error('Errore caricamento ristoranti:', error));

  // Add map zoom control
  map.zoomControl.setPosition('bottomright');
});
