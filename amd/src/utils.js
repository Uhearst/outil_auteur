import {dictionnary} from "./language/format_udehauthoring_fr";

let tryCounter = 0;

/**
 * @param {string} id
 * @param {string} toolBarOptions
 */
export function initTinyMce(id, toolBarOptions) {
    window.tinymce.init({
        selector: 'textarea#' + id,
        menubar: false,
        plugins: 'lists image table link charmap emoticons',
        toolbar: toolBarOptions,
        branding: false,
        min_height: 160,
        height: 160
    });
}

/**
 * @param {string} id
 */
export function removeTinyMce(id) {
    window.tinymce.remove('#' + id);
}

/**
 * @param {String} str
 */
export function removeTags(str) {
    if ((str === null) || (str === '')) {
        return false;
    } else {
        str = str.toString();
    }

    return str.replace(/(<([^>]+)>)/ig, '');
}

/**
 * @param {Array} buttons
 */
export function prepareActionButton(buttons) {
    let addingButtonsContainer = document.
    querySelectorAll("[class='col-md-9 form-inline align-items-start felement']");
    if (addingButtonsContainer) {
        addingButtonsContainer.forEach(function(addingButtonContainer) {
            if (addingButtonContainer.dataset.fieldtype === 'button') {
                addingButtonContainer.removeAttribute('class');
                let childNodes = addingButtonContainer.childNodes;
                childNodes.forEach(child => {
                    if (child.type && child.type === 'button') {
                        child.classList.remove('btn-secondary');
                    }
                });
            }

        });
    }
    let addingButtons = document.querySelectorAll("[class*='add_action_button']");
    if (addingButtons) {
        addingButtons.forEach(function(addingButton) {
            let childNodes = addingButton.childNodes;
            childNodes.forEach(child => {
                child.removeAttribute('class');
            });
        });
    }

    buttons.forEach(button => {
        if (button.type === 0) {
            let initialRemoveButton = document.getElementById('fitem_id_remove_' + button.id);
            if (initialRemoveButton) {
                initialRemoveButton.firstElementChild.style.setProperty("display", "none", "important");
            }
        } else {
            let initialRemoveButton = document.getElementById('fitem_id_add_' + button.id);
            if (initialRemoveButton) {
                initialRemoveButton.firstElementChild.style.setProperty("display", "none", "important");
            }
        }
    });
}

/**
 * @param {string} currentIdContainer
 * @param {int} isObj
 */
export function prepareAccordions(currentIdContainer, isObj = 0) {
    let elements = null;
    if (isObj === 1) {
        elements = document.querySelectorAll('[class="row row-container mb-3"]');
    } else if (isObj === 2) {
        elements = document.querySelectorAll('[class="row row-container-child mb-3"]');
    } else {
        elements = document.querySelectorAll('[id^=' + currentIdContainer + ']');
    }
    elements.forEach(element => {
        window.$(document).ready(function() {
            window.$('#' + element.firstElementChild.children[1].id).on('shown.bs.collapse', function(event) {
                event.stopPropagation();
                let $card = window.$(this).closest('.card');
                window.$('html,body').animate({
                    scrollTop: $card.offset().top - 100
                }, 250);
            });
        });
    });
}

/**
 * @param {Object} element
 */
export function handleNewAccordionElement(element) {
    tryCounter = tryCounter + 1;
    window.$(document).ready(function() {
        let collapsibleId = element.firstElementChild.children[1].getAttribute('id');
        try {
            window.$('#' + collapsibleId).collapse('toggle');
            window.$('#' + collapsibleId).on('shown.bs.collapse', function() {
                let $card = window.$(this).closest('.card');
                window.$('html,body').animate({
                    scrollTop: $card.offset().top - 100
                }, 250);
            });
        } catch (e) {
            if (tryCounter > 20) {
                window.console.error('New accordeons can\'t be handled, contact a dev.');
                window.console.error('Error detail: ' + e);
            } else {
                window.console.log('got error handling new accordeon, will retry');
                let interval = setInterval(function() {
                    handleNewAccordionElement(element);
                    clearInterval(interval);
                }, 100);
            }
        }
    });
}

/**
 *
 */
export function formatEditorAndFileManager() {
    let formContainer = document.getElementById('udeh-form');
    let inputs = formContainer.querySelectorAll('[class^="form-group row  fitem   "]');
    inputs.forEach(input => {
        if (input.children[1] && input.children[1].dataset
            && (input.children[1].dataset.fieldtype === 'editor' || input.children[1].dataset.fieldtype === 'filemanager')) {
            input.classList.add('inline-element');
            input.children[1].classList.add('inline-inner-element');
        }
    });
}


/**
 * @param {String} anchor
 */
export function appendAnchorToForm(anchor) {
    let anchorInput = document.getElementById('anchor');
    if (anchorInput === null) {
        var input = document.createElement("input");
        input.setAttribute("type", "hidden");
        input.setAttribute("name", "anchor");
        input.setAttribute("id", "anchor");
        input.setAttribute("value", anchor);
        document.getElementById('form_container').appendChild(input);
    } else {
        anchorInput.setAttribute('value', anchor);
    }
}

/**
 *
 */
export function getAndSetScrollPosition() {
    let addedType = localStorage.getItem("added-type");
    if (addedType !== null && addedType !== 'null' && addedType !== undefined) {
        if (document.readyState !== 'loading') {
            toggleAccordionFromPhpAddition(addedType);
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                toggleAccordionFromPhpAddition(addedType);
            });
        }
    }

    window.addEventListener("beforeunload", () => {
        if (document.activeElement && document.activeElement.classList && document.activeElement.classList.contains('add-button')) {
            localStorage.setItem("added-type", document.activeElement.name.substring(document.activeElement.name.indexOf('_' + 1)));
        } else {
            localStorage.setItem("added-type", null);
        }
    });
}

/**
 * @param {string} addedType
 */
function toggleAccordionFromPhpAddition(addedType) {
    let container = null;
    let rows = null;
    let forId = null;
    if (addedType.includes('subquestion')) {
        container = document.getElementById('section-subquestions-container');
    } else {
        forId = addedType.includes('resource') ? 'resources' : 'explorations';
        container = document.getElementById('subquestion-' + forId + '-container');
    }
    rows = container.querySelectorAll('[class*="row-container"]');
    waitForAccordionIdWithInterval(rows[rows.length - 1]);
}

/**
 * @param {object} currentElement
 */
function waitForAccordionIdWithInterval(currentElement) {
    let elementId = null;
    let interval = setInterval(function() {
        elementId = currentElement.firstElementChild.children[1].getAttribute('id');
        if (elementId.includes('_')) {
            handleNewAccordionElement(currentElement);
            clearInterval(interval);
        }
    }, 100);
}

/**
 * @param {String} id
 */
export function appendStyleToAnchorMenuSection(id) {
    let container = document.getElementById('collapse-course-container');
    for (let i = 0; i < container.children.length; i++) {
        if (!(container.children[i].getAttribute('class').includes('disabled-menu-element'))) {
            container.children[i].setAttribute('class', 'mb-3');
            updateIcon(container.children[i], 'actif', 'passif');
        }
    }
    let element = document.getElementById(id);
    element.classList.add('active-menu-element');
    updateIcon(element, 'passif', 'actif', true);
}

/**
 * @param {Object} element
 * @param {String} previousValue
 * @param {String} newValue
 * @param {Boolean} isActive
 */
function updateIcon(element, previousValue, newValue, isActive = false) {
    let src = element.firstElementChild.firstElementChild.getAttribute('src');
    if (src && src.includes(previousValue)) {
        let newSrc = src.replace(previousValue, newValue);
        element.firstElementChild.firstElementChild.setAttribute('src', newSrc);
        element.setAttribute('onmouseout', 'this.firstElementChild.firstElementChild.setAttribute("src", "' + newSrc + '")');
    }
    if (isActive) {
        element.firstElementChild.setAttribute('class', 'active');
    } else {
        element.firstElementChild.removeAttribute('class');
    }
}

/**
 *
 */
export function appendSizeToFileManager() {
    let fileManagers = document.querySelectorAll('[id*="vignette"]');
    if (fileManagers) {
        fileManagers.forEach(fileManager => {
            if (fileManager.classList.contains('form-group')) {
                let valueForFormat = '';
                if (fileManager.getAttribute('id').includes('resource')) {
                    valueForFormat = '125 x 80';
                } else {
                    valueForFormat = '364 x 215';
                }
                let restrictions = fileManager.querySelector('.fp-restrictions');
                restrictions.firstElementChild.innerHTML =
                    restrictions.firstElementChild.innerHTML + ', ' + dictionnary.recommendedFormat + valueForFormat;
            }
        });
    }
}

/**
 * @param {string} embedContainerId
 * @param {string} introductionContainerId
 * @param {Integer} i
 */
export function handleEmbed(embedContainerId, introductionContainerId, i = null) {

    let customCheckbox = null;
    let embedValContainer = null;
    if (i !== null) {
        customCheckbox = document.getElementById(('embed_selector_' + i));
        embedValContainer = document.querySelector('[name="isembed[' + i + ']"]');
    } else {
        customCheckbox = document.getElementById('embed_selector');
        embedValContainer = document.querySelector('[name="isembed"]');
    }
    if (embedValContainer.value === "1") {
        customCheckbox.checked = true;
    }
    setEmbedVisible(customCheckbox, embedContainerId, introductionContainerId);

    customCheckbox.addEventListener('click', function() {
        setEmbedVisible(customCheckbox, embedContainerId, introductionContainerId);
    });
}

/**
 * @param {object} customCheckbox
 * @param {string} embedContainerId
 * @param {string} introductionContainerId
 */
function setEmbedVisible(customCheckbox, embedContainerId, introductionContainerId) {
    let embedContainer = document.getElementById(embedContainerId);
    let introductionContainer = document.getElementById(introductionContainerId);
    if (customCheckbox.checked) {
        embedContainer.style = '';
        introductionContainer.style = 'display:none !important';
    } else {
        embedContainer.style = 'display:none !important';
        introductionContainer.style = '';
    }
}