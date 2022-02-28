/**
 *
 */
export function init() {
    updateVisibleForm();
    window.addEventListener('hashchange', function() {
        updateVisibleForm();
    });
    // TODO: Handle toogle menu
    // handleToggleMenuButton();
}

/**
 *
 */
/*function handleToggleMenuButton() {
    window.console.log('here');
    let button = document.getElementById('toggle-menu-btn');

    window.console.log(window.$('#sidebar-container').height());
    setTimeout(() => {
        window.console.log(window.$('#sidebar-container').height());
    }, 2000);

    // doSmth();
    window.$(window).on("scroll resize", function(){
        window.console.log(window.$('#sidebar-container').height());
    });
    button.addEventListener('click', function(event) {
        window.console.log(event);
    });
}*/

/**
 *
 */
/*function doSmth() {
    let sideContainer = window.$("#sidebar-container");

}*/

/**
 *
 */
function updateVisibleForm() {
    let n = window.location.pathname.lastIndexOf('/');
    let page = window.location.pathname.substring(n + 1);
    let anchor = window.location.href.includes("#") ?
        window.location.href.substring(window.location.href.lastIndexOf('#') + 1) : null;
    // TODO other pages
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
