import {validateAdditionalInfo} from "./validator/addtionnalInformationValidator";
import {get_string as getString} from 'core/str';
import {
    buildHiddenInput,
    initEditor,
    removeTinyEditor,
    setDeleteButton,
    setEditorAfterCloning
} from "./utils";
import {handleAccordions, handleNewAccordion, initAccordionsAfterFillingForm} from "./accordionHandler";

let counterInfo = 0;

/**
 *
 */
export function initAdditional() {
    const formContainer = document.getElementById('form_container');
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
            if (element && element.id.includes('add_info') && element.hidden === false) {
                if (element.id.includes('remove')) {
                    let id = element.id.substring(element.id.lastIndexOf('_') + 1);
                    await removeAdditionalInfo(id);
                    disableAddInfoDeleteButton();
                    await updateExistingHeaders();
                    document.getElementById('udeh-form').dataset.changed = 'true';
                    event.stopImmediatePropagation();
                }
            } else {
                return;
            }
        }
    });

    let addButton = document.querySelector('#id_add_info');
    addButton.addEventListener('click', async event => {
        event.stopImmediatePropagation();
        let element = event.target;

        if (element && element.hidden === false) {
            await addAdditionalInfo(true);
            document.getElementById('udeh-form').dataset.changed = 'true';
            disableAddInfoDeleteButton();
        } else {
            return;
        }

    });
    validateAdditionalInfo();
}

/**
 * @param {array} informations
 */
export const fillFormAdditional = async(informations) => {
    if (informations && informations.length > 0) {
        for (const information of informations) {
            const i = informations.indexOf(information);
            if (i === 0) {
                buildHiddenInput(information.id, i, 'fitem_id_add_info_title_' + (i), 'add_info_');
                document.querySelector('[name = "add_info_0_id_value"]').setAttribute('value', information.id);
            } else {
                if (document.getElementById('id_add_info_title_' + i) === null) {
                    await addAdditionalInfo(false);
                }
                document.querySelector('#id_add_info_title_' + i).value = information.title;
                document.querySelector('#id_add_info_content_' + i).value = information.content;
                document.querySelector('[name = "add_info_' + i + '_id_value"]').setAttribute('value', information.id);
                await handleEditor(i);
            }
        }
    }
    disableAddInfoDeleteButton();
    initAccordionsAfterFillingForm();
    handleAccordions();
};

/**
 * @param {integer} index
 */
async function removeAdditionalInfo(index) {
    await removeTinyEditor('id_add_info_content_' + index);
    document.querySelector('#add_info_' + index).remove();
    document.getElementById('udeh-form').dataset.changed = 'true';
}

const updateExistingHeaders = async () => {
    let headers = document.querySelectorAll('[aria-controls^="collapse_addinfo_header_"]');
    headers = Array.from(headers);
    for (const header of headers) {
        const i = headers.indexOf(header);
        header.innerText = await getString('field', 'format_udehauthoring') + ' ' + (i + 1);
    }
};

const disableAddInfoDeleteButton = () => {
    let infos = document.querySelectorAll('[id^="add_info_"]');
    if (infos.length <= 1) {
        let button = document.querySelector('[id^="id_remove_add_info_"]');
        button.hidden = true;
    } else {
        let buttons = document.querySelectorAll('[id^="id_remove_add_info_"]');
        buttons.forEach(button => {
            button.hidden = false;
        });
    }
};


/**
 * @param {boolean} isNew
 */
async function addAdditionalInfo(isNew) {
    counterInfo = counterInfo + 1;
    let infoParent = document.querySelector('#add_info_0');
    let info = infoParent.cloneNode(true);
    info.setAttribute('id', 'add_info_' + counterInfo);

    // Header
    info.querySelector('#course_addinfo_header_0')
        .setAttribute('id', 'course_addinfo_header_' + counterInfo);

    info.querySelector('[href="#collapse_addinfo_header_0"]').innerText =
        await getString('field', 'format_udehauthoring')
        + ' '
        + (document.querySelectorAll('[aria-controls^="collapse_addinfo_header_"]').length + 1);
    info.querySelector('[href="#collapse_addinfo_header_0"]')
        .setAttribute('aria-controls', 'collapse_addinfo_header_' + counterInfo);
    info.querySelector('[href="#collapse_addinfo_header_0"]')
        .setAttribute('href', '#collapse_addinfo_header_' + counterInfo);

    info.querySelector('#collapse_addinfo_header_0')
        .setAttribute('id', 'collapse_addinfo_header_' + counterInfo);

    info.querySelector('#course_addinfo_0')
        .setAttribute('id', 'course_addinfo_' + counterInfo);

    if (info.querySelector('[name="add_info_0_id_value"]')) {
        info.querySelector('[name="add_info_0_id_value"]')
            .setAttribute('name', 'add_info_' + counterInfo + '_id_value');
        info.querySelector('[name="add_info_' + counterInfo + '_id_value"]').setAttribute('value', '');
        info.querySelector('[name="add_info_' + counterInfo + '_id_value"]').value = '';
    }

    // Title
    let title = info.querySelector('#fitem_id_add_info_title_0');

    title.setAttribute('id', 'fitem_id_add_info_title_' + counterInfo);

    title.querySelector('[for="id_add_info_title_0"]')
        .setAttribute('for', 'id_add_info_title_' + counterInfo);
    title.querySelector('#id_add_info_title_0')
        .setAttribute('name', 'add_info_title_' + counterInfo);
    title.querySelector('#id_add_info_title_0').value = '';
    title.querySelector('#id_add_info_title_0')
        .setAttribute('id', 'id_add_info_title_' + counterInfo);
    title.querySelector('#id_error_add_info_title_0')
        .setAttribute('id', 'id_error_add_info_title_' + counterInfo);


    setEditorAfterCloning(info, 'content', 'add_info', counterInfo, null, isNew);

    // Delete button
    setDeleteButton(info, 'remove_add_info', counterInfo);

    let container = document.querySelector('#displayable-form-additional-information-container');
    container.insertBefore(info, document.querySelector('#addinfo-add-container'));

    if (isNew) {
        handleNewAccordion('#course_addinfo_header_', counterInfo);
        await handleEditor(counterInfo);
    }
}

const handleEditor = async(addInfoIndex) => {
    window.$.ajax(
        {
            type: "POST",
            url: "../handlers/ajax_editor_handler.php",
            data: {
                id: document.querySelector("#udeh-form").querySelector('[name = "course_id"]').value,
                elementId: 'id_add_info_content_' + addInfoIndex,
                text: ''
            },
            success: function(response) {
                let draftIdElm = document.querySelector('#add_info_' + addInfoIndex)
                    .querySelector('[name = "add_info_content_' + addInfoIndex + '[itemid]"]');
                initEditor(
                    response,
                    draftIdElm
                );
            },
            error: async function(e) {
                window.console.log(e);
            }
        });
};