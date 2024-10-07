
// -------------------------------------- DESPLAZAMIENTO SUAVE

function smoothScroll(targetId) {
    const element = document.getElementById(targetId);
    if (element) {
      // Calcular la posición exacta del elemento
      const elementPosition = element.getBoundingClientRect().top + window.scrollY;
  
      // Desplazarse suavemente a la posición
      window.scrollTo({
        top: elementPosition,
        behavior: 'smooth'
      });
    } else {
      console.error(`Elemento con ID "${targetId}" no encontrado.`);
    }
  }


  // ----------------- FUNCION DEL ACORDEON -----------------------------

document.addEventListener('DOMContentLoaded', () => {

const accordionItems = document.querySelectorAll('.accordion-item');


accordionItems.forEach(item => {
const title = item.querySelector('.accordion-title');


title.addEventListener('click', () => {
    // CIERRA TODOS LOS ACORDEONES 
    accordionItems.forEach(otherItem => {
    if (otherItem !== item) {
        const content = otherItem.querySelector('.accordion-content');
        content.style.maxHeight = null; 
        otherItem.classList.remove('active'); 
    }
    });

    // Toggle the current accordion
    const content = item.querySelector('.accordion-content');
    if (item.classList.contains('active')) {
    content.style.maxHeight = null; 
    } else {
    content.style.maxHeight = content.scrollHeight + 'px'; 
    }
    item.classList.toggle('active');
});
});
});


