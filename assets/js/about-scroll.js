document.addEventListener('DOMContentLoaded', function () {
    const tiltContainer = document.querySelector('.item-tilt');
    const tiltElement = document.querySelector('.tilt-element');

    if (!tiltContainer || !tiltElement) {
        console.log('Tilt elements not found');
        return;
    }

    console.log('Tilt animation initialized');

    tiltContainer.addEventListener('mousemove', (e) => {
        const rect = tiltContainer.getBoundingClientRect();
        // Calculate mouse position relative to center of element
        const x = e.clientX - rect.left - rect.width / 2;
        const y = e.clientY - rect.top - rect.height / 2;

        // Calculate rotation (adjust sensitivity with the divisor)
        const rotateY = x / 20; // Max rotation +/- 10deg approx
        const rotateX = -(y / 20); // Invert Y axis for correct tilt

        tiltElement.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.02)`;
    });

    tiltContainer.addEventListener('mouseleave', () => {
        // Reset on mouse leave
        tiltElement.style.transform = `rotateX(0) rotateY(0) scale(1)`;
    });
});
