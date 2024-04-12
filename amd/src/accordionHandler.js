const getCollapsibles = (anchor) => {
    let collapsibles = document.querySelector('#' + anchor).querySelectorAll('[data-toggle="collapse"]');

    let collapsiblesArray = Array.from(collapsibles);

    return collapsiblesArray.filter(collapsible => {
            return collapsible.getAttribute('aria-controls').includes('collapse');
        }
    );
};


const getAnchor = () => {
    let anchors = window.location.href.includes("#") ?
        [window.location.href.substring(window.location.href.lastIndexOf("#") + 1)] : [""];

    if (window.$('#global-evaluations-container').length > 0) {
        anchors = ['global-evaluations-container'];
    }

    if (window.$('#section-subquestions-container').length > 0) {
        anchors = ['section-subquestions-container'];
    }

    if (window.$('#subquestion-explorations-container').length > 0) {
        anchors = ['subquestion-explorations-container', 'subquestion-resources-container'];
    }

    return anchors;
};


const clickAndRetry = (collapsibleHeader, state = true) => {
    let interval = setInterval(function() {
        // Try to open
        if (state) {
            if (collapsibleHeader.classList && collapsibleHeader.classList.contains('collapsed')) {
                collapsibleHeader.click();
            } else {
                clearInterval(interval);
            }
        }
        // Try to close
        else {
            if (collapsibleHeader.classList && !collapsibleHeader.classList.contains('collapsed')) {
                collapsibleHeader.click();
            } else {
                clearInterval(interval);
            }
        }
    }, 100);
};


const handleAccordion = (collapsible, anchor, forceOpen = false, withNavigation = true) => {

    // Force open accordion if needed
    if (forceOpen) {
        clickAndRetry(collapsible);
    }

    collapsible.addEventListener('click', function() {
        const myCollapseEl = document.querySelector('#' + collapsible.getAttribute('aria-controls'));
        if (anchor.includes('objectives')) {
            // Remove all localStorage for same anchor and learning anchor
            localStorage.removeItem('focusOn_' + anchor + '-learning');
            if (!myCollapseEl.id.includes('learning')) {
                // Remove localStorage only if click on another teaching
                localStorage.removeItem('focusOn_' + anchor);
            }
        } else {
            // Remove all localStorage for same anchor
            localStorage.removeItem('focusOn_' + anchor);
        }
        if (collapsible.getAttribute('aria-expanded') === 'false') {
            // Set new localStorage on accordion opening
            if (myCollapseEl.id.includes('learning')) {
                let parent = myCollapseEl.closest('[id^="course_learning_objectives_container_"]');
                let index = parent.id.substring(parent.id.lastIndexOf('_') + 1, parent.id.length);
                localStorage.setItem('focusOn_' + anchor, 'collapse_teaching_' + index);
                localStorage.setItem('focusOn_' + anchor + '-learning', myCollapseEl.id);
            } else {
                localStorage.setItem('focusOn_' + anchor, myCollapseEl.id);

                // Set Learning base LocalStorage if needed
                if (myCollapseEl.id.includes('teaching')
                    && localStorage.getItem('focusOn_' + anchor + '-learning') === null) {
                    let selector = 'collapse_learning_'
                        + myCollapseEl.id.substring(myCollapseEl.id.lastIndexOf('_') + 1) + '_0';
                    let headerClickable = document.querySelector('[aria-controls="' + selector + '"]');
                    if (headerClickable.classList.contains('collapsed')) {
                        clickAndRetry(document.querySelector('[aria-controls="' + selector + '"]'));
                    }
                    localStorage.setItem('focusOn_' + anchor + '-learning', selector);
                }
            }

            if (withNavigation) {
                // Navigate to open accordion
                let $card = window.$(this).closest('.card');
                window.$('html,body').animate({
                    scrollTop: $card.offset().top - 100
                }, 250);
            }
        }
    });
};


export const initAccordionsAfterFillingForm = () => {
    window.$(document).ready(function() {
            let anchors = getAnchor();
            anchors.forEach(anchor => {
                let collapsibleId = localStorage.getItem('focusOn_' + anchor);
                let elm = document.querySelector('[aria-controls="' + collapsibleId + '"]');
                if (collapsibleId && elm) {
                    // Open accordion if present in localstorage
                    clickAndRetry(elm);
                    let subCollapsibleId = localStorage.getItem('focusOn_' + anchor + '-learning');
                    let subElm = document.querySelector('[aria-controls="' + subCollapsibleId + '"]');
                    if (subCollapsibleId && subElm) {
                        // Open SubAccordion if present in localstorage
                        clickAndRetry(subElm);
                    }
                } else {
                    // If no value is present in localstorage for anchor, open the first one
                    let firstCollapsibleHeader = getCollapsibles(anchor)[0];
                    clickAndRetry(firstCollapsibleHeader);
                    if (anchor === 'displayable-form-objectives-container') {
                        clickAndRetry(getCollapsibles(anchor)[1]);
                    }
                }
            });
    });
};


export const handleAccordions = (withNavigation = true) => {
    window.$(document).ready(function() {
        let anchors = getAnchor();
        // Handle accordions for current anchors
        anchors.forEach(anchor => {
            let collapsibles = getCollapsibles(anchor);
            for (const collapsible of collapsibles) {
                // Handle each accordion on page load, force open if only one present
                if (collapsibles.length === 1
                    || (collapsibles.length === 2
                        && collapsibles[0].getAttribute('aria-controls') === 'collapse_teaching_0')) {
                    handleAccordion(collapsible, anchor, true, withNavigation);
                } else {
                    handleAccordion(collapsible, anchor, false, withNavigation);
                }
            }
        });
    });
};


export const handleNewAccordion = (prefix, counter) => {
    let anchor = getAnchor();

    // Here get all the headers to close them before addition
    let headers = document.querySelectorAll('[id^="' + prefix.replace('#', '') + '"]');
    headers.forEach(header => {
       let collapsibleHeader = header.querySelector('a');
       clickAndRetry(collapsibleHeader, false);
    });

    // Add new click event handler for new accordion
    let collapsibleHeader = document.querySelector(prefix + counter + ' a');
    handleAccordion(collapsibleHeader, anchor, true);
};