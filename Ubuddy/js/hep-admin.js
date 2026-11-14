const button = document.getElementById('archive-btn');
const info = document.getElementById('info');

button.addEventListener('click', function() {
    info.style.display = 'none'; // Completely removes it from the layout
});