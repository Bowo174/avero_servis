(function () {
    'use strict';

    let viewer = null;
    let activeButton = null;

    function closeViewer() {
        if (!viewer) return;
        viewer.remove();
        viewer = null;
        if (activeButton) activeButton.focus();
        activeButton = null;
    }

    function openViewer(button) {
        const source = button.getAttribute('data-preview-src');
        if (!source) return;

        if (!viewer) {
            viewer = document.createElement('div');
            viewer.className = 'image-viewer';
            viewer.setAttribute('role', 'dialog');
            viewer.setAttribute('aria-modal', 'true');

            const image = document.createElement('img');
            const close = document.createElement('button');
            close.type = 'button';
            close.className = 'image-viewer-close';
            close.textContent = 'Tutup';
            close.addEventListener('click', closeViewer);
            viewer.addEventListener('click', function (event) {
                if (event.target === viewer) closeViewer();
            });
            viewer.append(image, close);
            document.body.appendChild(viewer);
        }

        activeButton = button;
        const image = viewer.querySelector('img');
        image.src = source;
        image.alt = button.getAttribute('data-preview-alt') || 'Pratinjau foto barang';
        viewer.querySelector('button').focus();
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-preview-src]');
        if (button) openViewer(button);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeViewer();
    });
})();
