document.addEventListener('DOMContentLoaded', function () {
    const configIcon = document.getElementById('config-icon');
    const modal = document.getElementById('config-modal');
    const closeModal = document.getElementById('close-modal');
    const mainContent = document.getElementById('main-content');

    configIcon.addEventListener('click', function () {
        modal.style.display = 'block';
        mainContent.classList.add('blur');  // Difuminar el contenido
    });

    closeModal.addEventListener('click', function () {
        modal.style.display = 'none';
        mainContent.classList.remove('blur');  // Quitar el efecto difuminado
    });

    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
            mainContent.classList.remove('blur');  // Quitar el efecto difuminado
        }
    });
});
