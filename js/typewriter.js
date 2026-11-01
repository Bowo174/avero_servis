document.addEventListener('DOMContentLoaded', function () {
    var heading = document.getElementById('welcome-title');
    if (!heading) return;

    var message = heading.getAttribute('data-text') || heading.textContent.trim();
    heading.setAttribute('aria-label', message);
    heading.textContent = '';

    var position = 0;
    function typeNextCharacter() {
        heading.textContent += message.charAt(position);
        position += 1;

        if (position < message.length) {
            window.setTimeout(typeNextCharacter, 65);
        } else {
            heading.classList.add('typed');
        }
    }

    window.setTimeout(typeNextCharacter, 250);
});
