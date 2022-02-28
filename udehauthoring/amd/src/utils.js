/**
 * @param {string} id
 * @param {string} toolBarOptions
 */
export function initTinyMce(id, toolBarOptions) {
    window.console.log('initied:' + id);
    window.tinymce.init({
        selector: 'textarea#' + id,
        menubar: false,
        plugins: 'lists',
        toolbar: toolBarOptions,
        branding: false
    });
}

/**
 * @param {string} id
 */
export function removeTinyMce(id) {
    window.console.log('removed:' + id);
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
 *
 */
export function formatEditorAndFileManager() {
    // window.console.log('entered');
    let formContainer = document.getElementById('udeh-form');
    let inputs = formContainer.querySelectorAll('[class^="form-group row  fitem   "]');
    // window.console.log('inputs');
    // window.console.log(inputs);
    inputs.forEach(input => {
        // window.console.log(input);
        // window.console.log(input.children[1].dataset);
        if (input.children[1].dataset.fieldtype === 'editor' || input.children[1].dataset.fieldtype === 'filemanager') {
            input.classList.add('inline-element');
            input.children[1].classList.add('inline-inner-element');
        }
    });
}


/**
 *
 */
export function appendAnchorToForm() {
    let anchor = window.location.href.includes("#") ?
        window.location.href.substring(window.location.href.lastIndexOf('#') + 1) : 'displayable-form-informations-container';
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