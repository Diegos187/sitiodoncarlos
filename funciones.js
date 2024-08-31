
function smoothScroll(targetId) {
const element = document.getElementById(targetId);
if (element) {
    element.scrollIntoView({ behavior: 'smooth' });
}
}



document.addEventListener('DOMContentLoaded', () => {
// Get all accordion items
const accordionItems = document.querySelectorAll('.accordion-item');

// Loop through each accordion item
accordionItems.forEach(item => {
const title = item.querySelector('.accordion-title');

// Add click event listener to title
title.addEventListener('click', () => {
    // Close all other accordions
    accordionItems.forEach(otherItem => {
    if (otherItem !== item) {
        const content = otherItem.querySelector('.accordion-content');
        content.style.maxHeight = null; // Reset max-height
        otherItem.classList.remove('active'); // Remove active class
    }
    });

    // Toggle the current accordion
    const content = item.querySelector('.accordion-content');
    if (item.classList.contains('active')) {
    content.style.maxHeight = null; // Reset max-height
    } else {
    content.style.maxHeight = content.scrollHeight + 'px'; // Set max-height to content height
    }
    item.classList.toggle('active'); // Toggle active class
});
});
});
