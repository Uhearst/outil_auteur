/**
 *
 */
export function init() {
    updateVisibleForm();
    window.addEventListener('hashchange', function() {
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
}

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
