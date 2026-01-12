/**
 * Canvas Video Text Mask Script
 * Handles the "video inside text" effect using an off-screen canvas.
 * Shared between index.php and about.php
 */
document.addEventListener('DOMContentLoaded', function () {
    const video = document.getElementById('sourceVideo');
    const textMasks = document.querySelectorAll('.video-text');

    // Only proceed if elements exist
    if (!video || textMasks.length === 0) return;

    // Create a shared off-screen canvas for better performance
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d', { alpha: false }); // Optimize for speed (no alpha channel needed on source)

    let isPlaying = false;
    let animationFrameId;

    // Function to update the canvas with the current video frame
    function updateCanvas() {
        if (!isPlaying) return;

        if (video.videoWidth > 0 && video.videoHeight > 0) {
            // Resize canvas if needed
            if (canvas.width !== video.videoWidth || canvas.height !== video.videoHeight) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
            }

            // Draw video frame
            ctx.drawImage(video, 0, 0);

            // Get data URL (consider optimizing this if frame rate drops, but for simple text it's usually fine)
            // For continuous animation, we paint the data URL
            const dataUrl = canvas.toDataURL('image/jpeg', 0.5); // Lower quality for speed

            textMasks.forEach(text => {
                text.style.backgroundImage = `url(${dataUrl})`;
                text.style.backgroundSize = 'cover';
                text.style.backgroundPosition = 'center';
            });
        }

        animationFrameId = requestAnimationFrame(updateCanvas);
    }

    // Event listeners
    video.addEventListener('play', () => {
        isPlaying = true;
        updateCanvas();
    });

    video.addEventListener('pause', () => {
        isPlaying = false;
        if (animationFrameId) cancelAnimationFrame(animationFrameId);
    });

    // Attempt autoplay
    video.play().catch(e => {
        console.warn('Video mask autoplay prevented:', e);
        // Fallback or user interaction logic could go here
    });
});
