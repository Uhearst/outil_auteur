import {handleNewAccordionElement, initTinyMce, prepareAccordions, prepareActionButton, removeTinyMce} from "./utils";
import {dictionnary} from "./language/format_udehauthoring_fr";

let counterModules = 0;

/**
 * @param {boolean} fromBtnClick
 */
function addModule(fromBtnClick) {
    let x = buildModule();
    let container = document.getElementById('displayable-form-sections-container');
    container.insertBefore(x, document.getElementById('section-add-container'));
    updateAddModuleButton();
    disableModuleDeleteButton();
    initTinyMce('id_section_title_' + counterModules, 'superscript subscript | undo redo');
    initTinyMce('id_section_question_' + counterModules,
        ['formatselect bold italic | numlist bullist indent outdent | link unlink | emoticons image ',
        // eslint-disable-next-line max-len
        'underline strikethrough superscript subscript | alignleft aligncenter alignright | charmap table removeformat | undo redo ']);
    initTinyMce('id_section_description_' + counterModules,
        ['formatselect bold italic | numlist bullist indent outdent | link unlink | emoticons image ',
            // eslint-disable-next-line max-len
            'underline strikethrough superscript subscript | alignleft aligncenter alignright | charmap table removeformat | undo redo ']);
    getTooltips(counterModules);
    if (fromBtnClick) {
        handleNewAccordionElement(x);
    }
}

/**
 * @param {string} index
 */
function removeModule(index) {
    let module = document.getElementById('row_course_module_container_' + index);
    removeTinyMce("id_section_title_" + index);
    removeTinyMce("id_section_question_" + index);
    removeTinyMce("id_section_description_" + index);
    module.remove();
    updateExistingModules(index);
    updateAddModuleButton();
    disableModuleDeleteButton();
    updateRemoveModuleButton();
    counterModules = counterModules - 1;
}

/**
 *
 */
function buildModule() {
    counterModules = counterModules + 1;
    let rowModuleContainer = document.createElement("div");
    let colModuleContainer = document.createElement("div");
    let colButtonContainer = document.createElement("div");

    rowModuleContainer.setAttribute('class', 'row row-container mb-3');
    rowModuleContainer.setAttribute('id', 'row_course_module_container_' + counterModules);
    colModuleContainer.setAttribute('class', 'col-11 accordion-container card');
    colButtonContainer.setAttribute('class', 'col-1 remove_module_action_button');

    let header = buildModuleHeader();
    let content = buildModuleContent();
    colModuleContainer.appendChild(header);
    colModuleContainer.appendChild(content);

    let button = buildModuleButton();
    colButtonContainer.appendChild(button);

    rowModuleContainer.appendChild(colModuleContainer);
    rowModuleContainer.appendChild(colButtonContainer);

    return rowModuleContainer;
}

/**
 *
 */
function buildModuleHeader() {
    let headerContainer = document.createElement("div");
    let linkForCollapseDiv = document.createElement("a");

    headerContainer.setAttribute('class', 'card-header accordion-header');
    headerContainer.setAttribute('id', 'course_module_header_' + (counterModules));

    linkForCollapseDiv.setAttribute('data-toggle', 'collapse');
    linkForCollapseDiv.setAttribute('href', '#collapse_module_' + (counterModules));
    linkForCollapseDiv.setAttribute('role', 'button');
    linkForCollapseDiv.setAttribute('aria-expanded', 'false');
    linkForCollapseDiv.setAttribute('aria-controls', 'collapse_module_' + (counterModules));
    linkForCollapseDiv.setAttribute('class', 'collapsed');
    linkForCollapseDiv.innerHTML = dictionnary.module + (counterModules + 1);

    headerContainer.appendChild(linkForCollapseDiv);

    return headerContainer;
}

/**
 *
 */
function buildModuleContent() {
    let contentContainer = document.createElement("div");
    let collapseDiv = document.createElement("div");

    collapseDiv.setAttribute('class', 'collapse');
    collapseDiv.setAttribute('id', 'collapse_module_' + (counterModules));
    collapseDiv.setAttribute('data-parent', '#displayable-form-sections-container');

    contentContainer.setAttribute('class', 'card-body accordion-content');
    contentContainer.setAttribute('id', 'course_module_content_' + (counterModules));

    let title = buildEditorContainer('section_title', 'Titre du module');
    let question = buildEditorContainer('section_question', dictionnary.moduleQuestion);
    let description = buildEditorContainer('section_description', dictionnary.moduleDescription);

    contentContainer.appendChild(title);
    contentContainer.appendChild(question);
    contentContainer.appendChild(description);
    collapseDiv.appendChild(contentContainer);

    return collapseDiv;
}

/**
 * @param {string} id
 * @param {string} labelText
 */
function buildEditorContainer(id, labelText) {
    let fieldContainer = document.createElement("div");
    let labelContainer = document.createElement("div");
    let label = document.createElement("label");
    let editorContainer = document.createElement("div");


    fieldContainer.setAttribute('class', 'form-group row  fitem   tiny-editor');
    fieldContainer.setAttribute('id', 'fitem_id_' + id + '_' + counterModules);

    labelContainer.setAttribute('class', 'col-md-3 col-form-label d-flex pb-0 pr-md-0');

    label.setAttribute('class', 'd-inline word-break');
    label.setAttribute('for', 'id_' + id + '_' + counterModules);
    label.innerHTML = labelText;

    editorContainer.setAttribute('class', 'col-md-9 align-items-start felement');
    editorContainer.setAttribute('data-fieldtype', 'editor');

    let editor = buildTinyMCE(id, counterModules);

    editorContainer.appendChild(editor);
    labelContainer.appendChild(label);

    fieldContainer.appendChild(labelContainer);
    fieldContainer.appendChild(editorContainer);

    return fieldContainer;
}

/**
 * @param {string} target
 * @param {string} moduleIndex
 */
function buildTinyMCE(target, moduleIndex) {
    let inputContainerDiv = document.createElement("textarea");
    inputContainerDiv.setAttribute('class', 'custom-editor');
    inputContainerDiv.setAttribute('id', 'id_' + target + '_' + moduleIndex);
    inputContainerDiv.setAttribute('name', target + '_' + moduleIndex);
    return inputContainerDiv;
}

/**
 *
 */
function buildModuleButton() {
    let rowModuleContainer = document.createElement("div");
    let buttonContainer = document.createElement("div");
    let buttonElement = document.createElement("button");
    let icon = document.createElement("i");

    rowModuleContainer.setAttribute('id', 'fitem_id_remove_module_' + counterModules);

    buttonContainer.setAttribute('data-fieldtype', 'button');

    buttonElement.setAttribute('class', 'btn ml-0');
    buttonElement.setAttribute('name', 'remove_module_' + counterModules);
    buttonElement.setAttribute('id', 'id_remove_module_' + counterModules);
    buttonElement.setAttribute('type', 'button');

    icon.setAttribute('class', 'remove-button-js fa fa-minus-circle fa-2x');

    buttonElement.appendChild(icon);
    buttonContainer.appendChild(buttonElement);
    rowModuleContainer.appendChild(buttonContainer);

    return rowModuleContainer;
}

/**
 *
 */
function disableModuleDeleteButton() {
    let modules = document.querySelectorAll('[id^="row_course_module_container_"]');
    if (modules.length === 1) {
        let button = document.getElementById('id_remove_module_0');
        button.hidden = true;
    } else {
        let buttons = document.querySelectorAll('[id^="id_remove_module"]');
        buttons.forEach(button => {
            button.hidden = false;
        });
    }
}

/**
 *
 */
function updateAddModuleButton() {
    let x = document.querySelectorAll('[id^="row_course_module_container_"]');
    let addModuleButtonContainer = document.getElementById('section-add-container');
    let addModuleButtonText = addModuleButtonContainer.querySelector('.add-text');
    addModuleButtonText.innerHTML = dictionnary.module + (x.length + 1);
}

/**
 *
 */
function updateRemoveModuleButton() {
    let buttons = document.querySelectorAll('[id^="fitem_id_remove_module_"]');
    buttons.forEach(function(button, index) {
        button.setAttribute('id', 'fitem_id_remove_module_' + index);
        let buttonElm = button.querySelector('[type="button"]');
        buttonElm.setAttribute('name', 'remove_module_' + index);
        buttonElm.setAttribute('id', 'id_remove_module_' + index);
    });
}

/**
 * @param {string} index
 */
function updateExistingModules(index) {
    let modules = document.querySelectorAll('[id^="row_course_module_container_"]');
    modules.forEach(module => {
        if (parseInt(module.id.substring(module.id.lastIndexOf('_') + 1)) > parseInt(index)) {
            let currentIndex = parseInt(module.id.substring(module.id.lastIndexOf('_') + 1));
            module.setAttribute('id', 'row_course_module_container_' + (currentIndex - 1));
            let header = module.querySelector('.accordion-header');
            header.setAttribute('id', 'course_module_header_' + (currentIndex - 1));
            header.firstElementChild.setAttribute('href', '#collapse_module_' + (currentIndex - 1));
            header.firstElementChild.setAttribute('aria-controls', 'collapse_module_' + (currentIndex - 1));
            header.firstElementChild.innerHTML = dictionnary.module + (currentIndex);

            let collapsible = module.querySelector('.collapse');
            collapsible.setAttribute('id', 'collapse_module_' + (currentIndex - 1));
            collapsible.firstElementChild.setAttribute('id', 'course_module_content_' + (currentIndex - 1));
            updateEditorAndLabel(module, currentIndex, 'title', 'superscript subscript | undo redo');
            updateEditorAndLabel(module, currentIndex, 'question', 'superscript subscript | undo redo');
            updateEditorAndLabel(module, currentIndex, 'description',
                'bold italic | numlist bullist | underline strikethrough superscript subscript | undo redo');

        }
    });
}

/**
 * @param {object} module
 * @param {string} currentIndex
 * @param {string} element
 * @param {string} toolbarOptions
 */
function updateEditorAndLabel(module, currentIndex, element, toolbarOptions) {
    let titleContainer = module.querySelector('[id^="fitem_id_section_' + element + '_"]');
    titleContainer.setAttribute('id', 'fitem_id_section_' + element + '_' + (currentIndex - 1));
    let labelTitle = titleContainer.querySelector('[for^="id_section_' + element + '_"]');
    labelTitle.setAttribute('for', 'id_section_' + element + '_' + (currentIndex - 1));
    removeTinyMce('id_section_' + element + '_' + (currentIndex));
    let editorTitle = titleContainer.querySelector('[id^="id_section_' + element + '_"]');
    editorTitle.setAttribute('id', 'id_section_' + element + '_' + (currentIndex - 1));
    editorTitle.setAttribute('name', 'section_' + element + '_' + (currentIndex - 1));
    initTinyMce('id_section_' + element + '_' + (currentIndex - 1), toolbarOptions);
}

/**
 * @param {int} index
 */
function getTooltips(index) {
    const titleContainer = document.getElementById('fitem_id_section_title_0');
    const questionContainer = document.getElementById('fitem_id_section_question_0');
    const descriptionContainer = document.getElementById('fitem_id_section_description_0');

    const titleToolTip = titleContainer.firstElementChild.children[1];
    const questionToolTip = questionContainer.firstElementChild.children[1];
    const descriptionToolTip = descriptionContainer.firstElementChild.children[1];

    let titleToFillContainer = document.getElementById('fitem_id_section_title_' + index);
    titleToFillContainer.firstElementChild.appendChild(titleToolTip.cloneNode(true));

    let questionToFillContainer = document.getElementById('fitem_id_section_question_' + index);
    questionToFillContainer.firstElementChild.appendChild(questionToolTip.cloneNode(true));

    let descriptionToFillContainer = document.getElementById('fitem_id_section_description_' + index);
    descriptionToFillContainer.firstElementChild.appendChild(descriptionToolTip.cloneNode(true));
}

/**
 * @param {int} id
 * @param {int} counter
 */
function buildIdHiddenInput(id, counter) {
    let input = document.createElement("input");
    input.setAttribute('name', 'section_' + (counter) + '_id_value');
    input.setAttribute('value', id);
    input.hidden = true;

    let element = document.getElementById('fitem_id_section_title_' + (counter));
    element.parentNode.insertBefore(input, element);
}

/**
 * @param {string} id
 * @param {int} type
 * @param {object} toAppend
 */
function waitWithInterval(id, type, toAppend) {
    let element = null;
    let timereditable = setInterval(function() {
        element = document.getElementById(id);
        if(element !== null) {
            if(type === 0) {
                element.innerHTML = toAppend;
            } else {
                element.value = toAppend;
            }
            clearInterval(timereditable);
        }
    }, 100);
}

/**
 *
 */
export function initModules() {
    const formContainer = document.getElementById('form_container');
    let addButton = document.querySelector('#id_add_module');
        formContainer.addEventListener('click', event => {
            const isButton = event.target.nodeName === 'BUTTON'
                || (event.target.nodeName === 'I' && event.target.parentNode && event.target.parentNode.nodeName === 'BUTTON');
            if (!isButton) {
                return;
            } else {
                let element = null;
                if (event.target.nodeName === 'I') {
                    element = event.target.parentNode;
                } else {
                    element = event.target;
                }
                if (element && element.id.includes('module') && element.hidden === false) {
                    if (element.id.includes('remove')) {
                        let id = element.id.substring(element.id.lastIndexOf('_') + 1);
                        removeModule(id);
                        event.stopImmediatePropagation();
                    }
                } else {
                    return;
                }
            }
        });

    addButton.addEventListener('click', event => {
        const isButton = event.target.nodeName === 'BUTTON'
            || (event.target.nodeName === 'I' && event.target.parentNode && event.target.parentNode.nodeName === 'BUTTON');
        if (!isButton) {
            return;
        } else {
            event.stopImmediatePropagation();
            let element = null;
            if (event.target.nodeName === 'I') {
                element = event.target.parentNode;
            } else {
                element = event.target;
            }
            if (element && element.id.includes('module') && element.hidden === false) {
                addModule(true);
            } else {
                return;
            }
        }
    });
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
    disableModuleDeleteButton();
    prepareAccordions('row_course_module_container_');
}

/**
 * @param {array} modules
 */
export function fillFormModules(modules) {
    modules.forEach(function(module, i) {
        if (i === 0) {
            waitWithInterval('id_section_title_0editable', 0, module.title);
            waitWithInterval('id_section_title_0', 1, module.title);

            waitWithInterval('id_section_question_0editable', 0, module.question);
            waitWithInterval('id_section_question_0', 1, module.question);

            waitWithInterval('id_section_description_0editable', 0, module.description);
            waitWithInterval('id_section_description_0', 1, module.description);

        } else {
            if (document.getElementById('id_section_title_' + i) === null) {
                addModule(false);
            }
            let title = document.getElementById('id_section_title_' + i);
            title.innerHTML = module.title;
            let question = document.getElementById('id_section_question_' + i);
            question.innerHTML = module.question;
            let description = document.getElementById('id_section_description_' + i);
            description.innerHTML = module.description;
        }
        buildIdHiddenInput(module.id, i);
    });
    prepareActionButton([
        {type: 0, id: 'module_0'},
        {type: 1, id: 'module'}]);
    disableModuleDeleteButton();
    prepareAccordions('row_course_module_container_');
}