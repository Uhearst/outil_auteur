/* eslint-disable max-len */
import {get_string as getString} from 'core/str';
import * as Config from 'core/config';

// let tryCounter = 0;
let tinyMCEPromise;

export const initEditor = (response, draftIdElm) => {
    let parsedResponse = JSON.parse(response);
    draftIdElm.setAttribute('value', JSON.parse(parsedResponse.data.config).draftitemid);
    let footers = document.querySelector('footer').querySelectorAll('.footer-section');
    footers.forEach(footer => {
        let scripts = footer.querySelectorAll('script');
        if (scripts.length !== 0) {
            let inlineDefaultConfiguration = document.createElement('script');
            inlineDefaultConfiguration.text =
                "M.util.js_pending('editor_tiny/editor:defaultConfiguration'); " +
                "require(['editor_tiny/editor'], (Tiny) => { " +
                "Tiny.configureDefaultEditor(" + parsedResponse.data.editorDefaultConfig + "); " +
                "M.util.js_complete('editor_tiny/editor:defaultConfiguration'); " +
                "});";
            footer.appendChild(inlineDefaultConfiguration);

            let inlineScript = document.createElement('script');
            inlineScript.text =
                "M.util.js_pending('editor_tiny/editor'); " +
                "require(['editor_tiny/editor'], (Tiny) => { " +
                "Tiny.setupForElementId({ " +
                "elementId: \"" + parsedResponse.data.elementId + "\", " +
                "options: " + parsedResponse.data.config + "" +
                "}); " +
                "M.util.js_complete('editor_tiny/editor');" +
                "});";
            footer.appendChild(inlineScript);
        }
    });
};

/**
 * @param {string} id
 */
export async function removeTinyEditor(id) {
    const tinyMCE = await getTinyMCE();
    tinyMCE.get()
        .filter(
            (editor) => editor.id === id
        )
        .forEach(
            (editor) => {
                editor.remove();
            }
        );
}

const getTinyMCE = () => {

    const baseUrl = `${Config.wwwroot}/lib/editor/tiny/loader.php/${M.cfg.jsrev}`;

    if (tinyMCEPromise) {
        return tinyMCEPromise;
    }

    tinyMCEPromise = new Promise((resolve, reject) => {
        const head = document.querySelector('head');
        let script = head.querySelector('script[data-tinymce="tinymce"]');
        if (script) {
            resolve(window.tinyMCE);
        }

        script = document.createElement('script');
        script.dataset.tinymce = 'tinymce';
        script.src = `${baseUrl}/tinymce.js`;
        script.async = true;

        script.addEventListener('load', () => {
            resolve(window.tinyMCE);
        }, false);

        script.addEventListener('error', (err) => {
            reject(err);
        }, false);

        head.append(script);
    });

    return tinyMCEPromise;
};

/**
 * @param {String} str
 */
export function removeTags(str) {
    if ((str === null) || (str === '')) {
        return false;
    } else {
        str = str.toString();
    }
    str = str.replace(/<div class="h5p-placeholder"[^>]*>([\s\S]*?)<\/div>/, '');
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
 *
 */
export function formatEditorAndFileManager() {
    let formContainer = document.getElementById('udeh-form');
    let inputs = formContainer.querySelectorAll('[class^="form-group row  fitem   "]');
    inputs.forEach(input => {
        if (input.children[1] && input.children[1].dataset
            && (input.children[1].dataset.fieldtype === 'editor' || input.children[1].dataset.fieldtype === 'filemanager')) {
            removeUselessColClasses(input.children[0]);
            removeUselessColClasses(input.children[1]);
            input.classList.add('mx-2');
            let label = input.querySelector('label');
            if (label) {
                label.classList.replace('m-0', 'm-1');
            }
        }
    });
}

const removeUselessColClasses = (node) => {
    Array.from(node.classList).forEach(className => {
        if (className.startsWith('col-') && !className.startsWith('col-form')) {
            node.classList.remove(className);
        }
    });

    node.classList.add('col-12');
};


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
        const formContainer = document.getElementById('form_container');
        formContainer.appendChild(input);
    } else {
        anchorInput.setAttribute('value', anchor);
    }
}

/**
 *
 */
/*export function getAndSetScrollPosition() {
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

/!**
 * @param {string} addedType
 *!/
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

/!**
 * @param {object} currentElement
 *!/
function waitForAccordionIdWithInterval(currentElement) {
    let elementId = null;
    let interval = setInterval(function() {
        elementId = currentElement.firstElementChild.children[1].getAttribute('id');
        if (elementId.includes('_')) {
            handleAccordions(currentElement);
            clearInterval(interval);
        }
    }, 100);
}*/

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
        element.firstElementChild.setAttribute('class', 'active router');
    } else {
        element.firstElementChild.setAttribute('class', 'router');
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
                getString('evaluationrecommendedformat', 'format_udehauthoring')
                    .then(
                        (valueString) => {
                            restrictions.firstElementChild.innerHTML =
                                restrictions.firstElementChild.innerHTML + ', ' + valueString + valueForFormat;
                        }
                    )
                    .catch(() => {
                        restrictions.firstElementChild.innerHTML =
                            restrictions.firstElementChild.innerHTML + ', Format recommandé: ' + valueForFormat;
                    });

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

/**
 * @param {int|string} id
 * @param {int|string} counter
 * @param {string} elementName
 * @param {string} prefix
 */
export function buildHiddenInput(id, counter, elementName, prefix) {
    let element = document.getElementById(elementName);
    let parent = element.parentNode;
    let idValue = parent.querySelectorAll('[name="' + prefix + (counter) + '_id_value"]');
    if (idValue.length === 0) {
        let input = document.createElement("input");
        input.setAttribute('name', prefix + (counter) + '_id_value');
        input.setAttribute('value', id);
        input.hidden = true;
        parent.insertBefore(input, element);
    }
}

export const setEditorAfterCloning = (editorContainer, field, prefix, counter, parentUpdatedField = null, fromBtnClick = false) => {

    // Container
    editorContainer.querySelector('#fitem_id_' + prefix + '_' + field + '_0')
        .setAttribute(
            'id',
            parentUpdatedField
                ? 'fitem_id_' + prefix + '_' + parentUpdatedField + '_' + counter
                : 'fitem_id_' + prefix + '_' + field + '_' + counter
        );

    // Label
    if (editorContainer.querySelector('[for="id_' + prefix + '_' + field + '_0"]')) {
        editorContainer.querySelector('[for="id_' + prefix + '_' + field + '_0"]')
            .setAttribute('for', 'id_' + prefix + '_' + field + '_' + counter);
    }

    let textEvalParentNode = editorContainer.querySelector('#id_' + prefix + '_' + field + '_0').parentNode;

    // Textarea, need to remove hidden elements from cloning
    if (fromBtnClick || (textEvalParentNode && textEvalParentNode.querySelector('[role="application"]') !== null)) {

        if (textEvalParentNode.querySelector('[role="application"]') !== null) {
            textEvalParentNode.querySelector('[role="application"]').remove();
        }

        if (textEvalParentNode.querySelector('.tox-tinymce-aux') !== null) {
            textEvalParentNode.querySelector('.tox-tinymce-aux').remove();
        }
    }

    editorContainer.querySelector('#id_' + prefix + '_' + field + '_0').setAttribute('style', '');
    editorContainer.querySelector('#id_' + prefix + '_' + field + '_0').value = '';

    editorContainer.querySelector('#id_' + prefix + '_' + field + '_0')
        .setAttribute(
            'name',
            parentUpdatedField
                ? prefix + '_' + parentUpdatedField + '_' + counter + '[text]'
                : prefix + '_' + field + '_' + counter + '[text]'
        );

    editorContainer.querySelector('#id_' + prefix + '_' + field + '_0')
        .setAttribute(
            'id',
            parentUpdatedField
                ? 'id_' + prefix + '_' + parentUpdatedField + '_' + counter
                : 'id_' + prefix + '_' + field + '_' + counter
        );

    // Format
    editorContainer.querySelector('#menu' + prefix + '_' + field + '_0format')
        .setAttribute(
            'name',
            parentUpdatedField
                ? prefix + '_' + parentUpdatedField + '_' + counter + '[format]'
                : prefix + '_' + field + '_' + counter + '[format]'
        );

    editorContainer.querySelector('#menu' + prefix + '_' + field + '_0format')
        .setAttribute(
            'id',
            parentUpdatedField
                ? 'menu' + prefix + '_' + field + '_' + counter + 'format'
                : 'menu' + prefix + '_' + parentUpdatedField + '_' + counter + 'format'
        );

    // Itemid
    editorContainer.querySelector('[name="' + prefix + '_' + field + '_0[itemid]"').setAttribute('value', '');
    editorContainer.querySelector('[name="' + prefix + '_' + field + '_0[itemid]"')
        .setAttribute(
            'name',
            parentUpdatedField
                ? prefix + '_' + parentUpdatedField + '_' + counter + '[itemid]'
                : prefix + '_' + field + '_' + counter + '[itemid]'
        );

    // Error
    editorContainer.querySelector('#id_error_' + prefix + '_' + field + '_0')
        .setAttribute(
            'id',
            parentUpdatedField
                ? 'id_error_' + prefix + '_' + parentUpdatedField + '_' + counter
                : 'id_error_' + prefix + '_' + field + '_' + counter
        );
};

export const setDeleteButton = (container, elm, counter, subCounter = '') => {
    let baseSubCounter = subCounter === '' ? '' : '_0';

    container.querySelector('#fitem_id_' + elm + '_0' + baseSubCounter)
        .setAttribute('id', 'fitem_id_' + elm + '_' + counter + subCounter);
    container.querySelector('[name="' + elm + '_0' + baseSubCounter + '"]')
        .setAttribute('id', 'id_' + elm + '_' + counter + subCounter);
    container.querySelector('[name="' + elm + '_0' + baseSubCounter + '"]')
        .setAttribute('name', elm + '_' + counter + subCounter);
    container.querySelector('#id_error_' + elm + '_0' + baseSubCounter)
        .setAttribute('id', 'id_error_' + elm + '_' + counter + subCounter);
};

/**
 * @param {object} elm
 * @param {int} currentIndex
 * @param {string} element
 * @param {string} prefix
 */
export const updateEditorAndLabel = async(elm, currentIndex, element, prefix) => {
    let container = elm.querySelector('[id^="fitem_id_' + prefix + '_' + element + '_"]');
    container.setAttribute('id', 'fitem_id_' + prefix + '_' + element + '_' + (currentIndex - 1));

    let containerLabel = container.querySelector('[for^="id_' + prefix + '_' + element + '_"]');
    if (containerLabel) {
        containerLabel.setAttribute('for', 'id_' + prefix + '_' + element + '_' + (currentIndex - 1));
    }

    await removeTinyEditor('id_' + prefix + '_' + element + '_' + currentIndex);

    let editorTextArea = container.querySelector('[id^="id_' + prefix + '_' + element + '_"]');
    editorTextArea.setAttribute('id', 'id_' + prefix + '_' + element + '_' + (currentIndex - 1));
    editorTextArea.setAttribute('name', prefix + '_' + element + '_' + (currentIndex - 1) + '[text]');

    let editorFormat = container.querySelector('[name="' + prefix + '_' + element + '_' + currentIndex + '[format]"]');
    editorFormat.setAttribute('id', 'menu' + prefix + '_' + element + '_' + (currentIndex - 1) + 'format');
    editorFormat.setAttribute('name', prefix + '_' + element + '_' + (currentIndex - 1) + '[format]');

    container
        .querySelector('[name="' + prefix + '_' + element + '_' + currentIndex + '[itemid]"]')
        .setAttribute('name', prefix + '_' + element + '_' + (currentIndex - 1) + '[itemid]');

    container
        .querySelector('#id_error_' + prefix + '_' + element + '_' + currentIndex)
        .setAttribute('id', 'id_error_' + prefix + '_' + element + '_' + (currentIndex - 1));
};