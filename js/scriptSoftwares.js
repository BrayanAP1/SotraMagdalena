function selectSoftware(option) {
    const card = document.querySelector(`.software-card.${option}`);
    card.style.transform = "scale(0.95)";
    
    // Agregar clase de animación de clic
    card.classList.add('software-card-clicked');
    
    setTimeout(() => { 
      card.style.transform = "scale(1)"; 
      card.classList.remove('software-card-clicked');
    }, 150);
    
    setTimeout(() => {
      if(option === 'proveedores') {
        window.location.href = "pages/dashboardProveedores.php";
      } else if(option === 'envios') {
        window.location.href = "pages/dashboardPrecios.php";
      }
    }, 300);
  }

  document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.software-card');
    cards.forEach((card, index) => {
      card.style.animation = `fadeInUp 0.5s ease-out ${index * 0.2}s both`;
    });
    
    // Manejo de errores en la imagen del logo
    const logo = document.querySelector('.logo');
    logo.addEventListener('error', function() {
      this.src = 'data:image/svg+xml;charset=utf-8,%3Csvg xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22 width%3D%22180%22 height%3D%22180%22 viewBox%3D%220 0 180 180%22%3E%3Crect width%3D%22180%22 height%3D%22180%22 fill%3D%22%23f0f0f0%22%2F%3E%3Ctext x%3D%2290%22 y%3D%2290%22 font-family%3D%22Arial%22 font-size%3D%2218%22 text-anchor%3D%22middle%22 dominant-baseline%3D%22middle%22%3ESotraMagdalena%3C%2Ftext%3E%3C%2Fsvg%3E';
    });
  });