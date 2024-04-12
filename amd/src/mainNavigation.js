/**
 *
 */
export function init() {
    updateVisibleForm();
    window.addEventListener('hashchange', function () {
        updateVisibleForm();
    });
}

/**
 *
 */
function updateVisibleForm() {
    let n = window.location.pathname.lastIndexOf('/');
    let page = window.location.pathname.substring(n + 1);
    let anchor = window.location.href.includes("#") ?
        window.location.href.substring(window.location.href.lastIndexOf('#') + 1) : null;
    if (page.includes('course')) {
        if (anchor === null) {
            hideAllDisplayableElements();
            showMask('displayable-form-informations-container');
        } else {
            hideAllDisplayableElements();
            showMask(anchor);
        }
    }

    setDirtyForm();
}

const setDirtyForm = () => {
    let form = document.querySelector('#udeh-form');
    let inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('change', () => {
            if (form.dataset.changed !== 'true') {
                form.dataset.changed = 'true';
            }
        });
    });

    let interval = setInterval(function() {
        let iframes = form.querySelectorAll('iframe');
        iframes.forEach(iframe => {
            let iframeBody = iframe.contentWindow.document.body;
            if (iframeBody) {
                iframeBody.addEventListener('keyup', () => {
                    if (form.dataset.changed !== 'true') {
                        form.dataset.changed = 'true';
                    }
                });
            }
        });

        clearInterval(interval);
    }, 3000);
};

/**
 *
 */
function hideAllDisplayableElements() {
    let nodeList = document.querySelectorAll("[id^='displayable']");
    nodeList.forEach(node => {
        node.style.display = 'none';
    });
}

/**
 * @param {string} id
 */
function showMask(id) {
    let node = document.getElementById(id);
    if (node) {
        if (node.style.display === 'block') {
            node.style.display = 'none';
        } else {
            node.style.display = 'block';
        }
    }
}