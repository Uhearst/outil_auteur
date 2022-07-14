/**
 */
export function initNavBar() {
    window.addEventListener('load', function() {
        window.$('body').css('zoom', localStorage.getItem('zoomLevel'));
    });
    zoom();
    preview();
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

/**
 *
 */
function preview() {
    let previewButton = document.querySelector('#preview-button');
    if (previewButton) {
        previewButton.addEventListener('click', function() {
            if("url" in previewButton.dataset) {
                location.href = previewButton.dataset.url;
            }
            if("urls" in previewButton.dataset) {
                let anchor = location.hash.substr(1);
                let urls = JSON.parse(previewButton.dataset.urls);
                if (anchor in urls) {
                    location.href = urls[anchor];
                }
            }
        });
    }
}