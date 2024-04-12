import {
    buildHiddenInput,
    initEditor,
    prepareActionButton,
    removeTinyEditor,
    setDeleteButton,
    setEditorAfterCloning,
    updateEditorAndLabel
} from "./utils";
import {get_string as getString} from 'core/str';
import {handleAccordions, handleNewAccordion, initAccordionsAfterFillingForm} from "./accordionHandler";

const editorFields = ['title', 'question', 'description'];
let counterModules = 0;

/**
 * @param {boolean} fromBtnClick
 */
async function addModule(fromBtnClick) {
    counterModules = counterModules + 1;
    let moduleParent = document.querySelector('#row_course_module_container_0');
    let module = moduleParent.cloneNode(true);
    module.setAttribute('id', 'row_course_module_container_' + counterModules);

    // Header
    module.querySelector('#course_module_header_0').setAttribute('id', 'course_module_header_' + counterModules);

    module.querySelector('#section_isvisible_0').setAttribute('name', 'section_isvisible_' + counterModules);
    module.querySelector('#section_isvisible_0').setAttribute('id', 'section_isvisible_' + counterModules);
    module.querySelector('[for="section_isvisible_0"]').setAttribute('for', 'section_isvisible_' + counterModules);

    module.querySelector('[href="#collapse_module_header_0"]').innerText =
        await getString('section', 'format_udehauthoring') + ' ' + (counterModules + 1);
    module.querySelector('[href="#collapse_module_header_0"]')
        .setAttribute('aria-controls', 'collapse_module_header_' + counterModules);
    module.querySelector('[href="#collapse_module_header_0"]')
        .setAttribute('href', '#collapse_module_header_' + counterModules);

    module.querySelector('#collapse_module_header_0')
        .setAttribute('id', 'collapse_module_header_' + counterModules);

    module.querySelector('#course_module_0').setAttribute('id', 'course_module_' + counterModules);
    if (module.querySelector('[name="section_0_id_value"]')) {
        module.querySelector('[name="section_0_id_value"]').setAttribute('value', '');
        module.querySelector('[name="section_0_id_value"]').setAttribute(
            'name', 'section_' + counterModules + '_id_value');
    }

    // Content
    editorFields.forEach(editorField => {
        setEditorAfterCloning(module,
            editorField,
            'section',
            counterModules,
            null,
            fromBtnClick
        );
    });

    // Delete button
    setDeleteButton(module, 'remove_module', counterModules);

    let container = document.querySelector('#displayable-form-sections-container');
    container.insertBefore(module, document.querySelector('#section-add-container'));

    await updateAddModuleButton();
    disableModuleDeleteButton();

    if (fromBtnClick) {
        handleNewAccordion('#course_module_header_', counterModules);
        await handleEditor(counterModules);
    }
}

const handleEditor = async(counter) => {
    editorFields.forEach(editorField => {
        window.$.ajax(
            {
                type: "POST",
                url: "../handlers/ajax_editor_handler.php",
                data: {
                    id: document.querySelector("#udeh-form").querySelector('[name = "course_id"]').value,
                    elementId: 'id_section_' + editorField + '_' + counter,
                    text: ''
                },
                success: function(response) {
                    let draftIdElm = document.querySelector('#row_course_module_container_' + counter)
                        .querySelector('[name = "section_' + editorField + '_' + counter + '[itemid]"]');
                    initEditor(
                        response,
                        draftIdElm
                    );
                },
                error: async function(e) {
                    window.console.log(e);
                }
            });
    });
};

/**
 * @param {string} index
 */
async function removeModule(index) {
    let module = document.querySelector('#row_course_module_container_' + index);
    for (const editorField of editorFields) {
        await removeTinyEditor('id_section_' + editorField + '_' + index);
    }
    module.remove();
    await updateExistingModules(index);
    await updateAddModuleButton();
    disableModuleDeleteButton();
    updateRemoveModuleButton();
    counterModules = counterModules - 1;
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
async function updateAddModuleButton() {
    let x = document.querySelectorAll('[id^="row_course_module_container_"]');
    let addModuleButtonContainer = document.getElementById('section-add-container');
    let addModuleButtonText = addModuleButtonContainer.querySelector('.add-text');
    addModuleButtonText.innerHTML = await getString('section', 'format_udehauthoring') + ' ' + (x.length + 1);
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
async function updateExistingModules(index) {
    let modules = document.querySelectorAll('[id^="row_course_module_container_"]');
    for (const module of modules) {
        if (parseInt(module.id.substring(module.id.lastIndexOf('_') + 1)) > parseInt(index)) {
            let currentIndex = parseInt(module.id.substring(module.id.lastIndexOf('_') + 1));
            module.setAttribute('id', 'row_course_module_container_' + (currentIndex - 1));

            let header = module.querySelector('.accordion-header');
            header.setAttribute('id', 'course_module_header_' + (currentIndex - 1));

            header.querySelector('[data-toggle="collapse"]')
                .setAttribute('href', '#collapse_module_' + (currentIndex - 1));

            header.querySelector('[data-toggle="collapse"]')
                .setAttribute('aria-controls', 'collapse_module_' + (currentIndex - 1));

            header.querySelector('[data-toggle="collapse"]').innerHTML =
                await getString('section', 'format_udehauthoring') + ' ' + (currentIndex);

            header.querySelector('#section_isvisible_' + currentIndex)
                .setAttribute('name', 'section_isvisible_' + (currentIndex - 1));

            header.querySelector('#section_isvisible_' + currentIndex)
                .setAttribute('id', 'section_isvisible_' + (currentIndex - 1));

            let collapsible = module.querySelector('.collapse');
            collapsible.setAttribute('id', 'collapse_module_' + (currentIndex - 1));
            collapsible.firstElementChild.setAttribute('id', 'course_module_content_' + (currentIndex - 1));
            module.querySelector('[name="section_' + currentIndex + '_id_value"]')
                .setAttribute('name', 'section_' + (currentIndex - 1) + '_id_value');
            await updateEditorAndLabel(module, currentIndex, 'title', 'section');
            await updateEditorAndLabel(module, currentIndex, 'question', 'section');
            await updateEditorAndLabel(module, currentIndex, 'description', 'section');
            await handleEditor((currentIndex - 1));
        }
    }
}

/**
 *
 */
export function initModules() {
    const formContainer = document.getElementById('form_container');
    let addButton = document.querySelector('#id_add_module');
    formContainer.addEventListener('click', async event => {
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
                    await removeModule(id);
                    document.getElementById('udeh-form').dataset.changed = 'true';
                    event.stopImmediatePropagation();
                }
            } else {
                return;
            }
        }
    });

    addButton.addEventListener('click', async event => {
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
                await addModule(true);
                document.getElementById('udeh-form').dataset.changed = 'true';
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
}

/**
 * @param {array} modules
 */
export async function fillFormModules(modules) {
    for (const module of modules) {
        const i = modules.indexOf(module);
        if (i === 0) {
            // Editors value for index 0 are set in the course_plan to avoid racing conditions
            buildHiddenInput(module.id, i, 'fitem_id_section_title_0', 'section_');
            document.querySelector('#section_isvisible_0').checked = module.isvisiblepreview === '1';
            document.querySelector('[name = "section_0_id_value"]').setAttribute('value', module.id);
        } else {
            if (document.getElementById('id_section_title_' + i) === null) {
                await addModule(false);
            }
            document.querySelector('#section_isvisible_' + i).checked = module.isvisiblepreview === '1';
            editorFields.forEach(editorField => {
                document.querySelector('#id_section_' + editorField + '_' + i).value = module[editorField];
            });
            document.querySelector('[name = "section_' + i + '_id_value"]').setAttribute('value', module.id);
            await handleEditor(i);
        }
    }
    prepareActionButton([
        {type: 0, id: 'module_0'},
        {type: 1, id: 'module'}]);
    disableModuleDeleteButton();
    initAccordionsAfterFillingForm();
    handleAccordions();
    // prepareAccordions('row_course_module_container_');
}

