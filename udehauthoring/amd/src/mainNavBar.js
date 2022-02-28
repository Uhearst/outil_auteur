/**
 */
export function initNavBar() {
    window.addEventListener('load', function() {
        window.$('body').css('zoom', localStorage.getItem('zoomLevel'));
    });
    zoom();
}

/**
 *
 */
function zoom() {
    let zoomButtons = document.querySelectorAll('.zoom-level');
    zoomButtons.forEach(zoomButton => {
        zoomButton.addEventListener('click', function(event) {
            localStorage.setItem('zoomLevel', (event.target.value / 100));
            window.$('body').css('zoom', (event.target.value / 100));
        });
    });
}