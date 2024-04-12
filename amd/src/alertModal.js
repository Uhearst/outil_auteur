import {get_string as getString} from 'core/str';

let currentIndex = null;
let currentName = null;

/**
 *
 */
export async function handleModal() {

    await buildModal();
    toggleModal();
    deleteToolFromModal();
}

/**
 *
 */
async function buildModal() {
    let modalContainer = document.createElement('div');
    let modalDialog = document.createElement('div');
    let modalContent = document.createElement('div');
    let modalHeader = document.createElement('div');
    let modalTitle = document.createElement('h5');
    let modalBody = document.createElement('div');
    let modalFooter = document.createElement('div');
    let buttonClose = document.createElement('button');
    let buttonCloseHeader = document.createElement('button');
    let buttonSave = document.createElement('button');

    modalContainer.setAttribute('class', 'modal fade alert-modal');
    modalContainer.setAttribute('id', 'alertModal');
    modalContainer.setAttribute('tabindex', '-1');
    modalContainer.setAttribute('role', 'dialog');
    modalContainer.setAttribute('aria-labelledby', 'alertModalLabel');
    modalContainer.setAttribute('aria-hidden', 'true');

    modalDialog.setAttribute('class', 'modal-dialog');
    modalDialog.setAttribute('role', 'document');

    modalContent.setAttribute('class', 'modal-content alert-modal-content');

    modalHeader.setAttribute('class', 'modal-header');

    modalTitle.setAttribute('class', 'modal-title');
    modalTitle.setAttribute('id', 'alertModalLabel');
    modalTitle.innerHTML = await getString('alertmodaltitle', 'format_udehauthoring');

    buttonCloseHeader.setAttribute('type', 'button');
    buttonCloseHeader.setAttribute('class', 'close');
    buttonCloseHeader.setAttribute('data-dismiss', 'modal');
    buttonCloseHeader.setAttribute('aria-label', 'Close');
    buttonCloseHeader.innerHTML = '<span aria-hidden="true">&times;</span>';

    modalBody.setAttribute('class', 'modal-body');

    modalFooter.setAttribute('class', 'modal-footer');

    buttonClose.setAttribute('type', 'button');
    buttonClose.setAttribute('class', 'btn btn-secondary');
    buttonClose.setAttribute('data-dismiss', 'modal');
    buttonClose.innerHTML = await getString('cancel');

    buttonSave.setAttribute('type', 'button');
    buttonSave.setAttribute('class', 'btn btn-primary');
    buttonSave.setAttribute('id', 'btn_delete_tool');
    buttonSave.innerHTML = await getString('delete');

    modalFooter.appendChild(buttonClose);
    modalFooter.appendChild(buttonSave);

    modalHeader.appendChild(modalTitle);
    modalHeader.appendChild(buttonCloseHeader);

    modalContent.appendChild(modalHeader);
    modalContent.appendChild(modalBody);
    modalContent.appendChild(modalFooter);

    modalDialog.appendChild(modalContent);

    modalContainer.appendChild(modalDialog);
    let form = document.getElementById('udeh-form');
    if (form) {
        form.appendChild(modalContainer);
    }


}

/**
 *
 */
function toggleModal() {
    let buttons = document.querySelectorAll('[name*="[delete_tool]"]');
    buttons.forEach(button => {
        button.addEventListener('click', async e => {
            e.stopImmediatePropagation();
            if (location.href.includes('subquestion') || location.href.includes('globalevaluation')) {
                currentIndex = button.name.slice(button.name.indexOf('[') + 1,
                    button.name.indexOf(']'));
            }
            if (window.location.pathname.includes('subquestion')) {
                currentName = document.querySelector('[id = "exploration_tool_name_' + currentIndex + '"]').innerHTML;
            } else {
                if (currentIndex === null) {
                    currentName = document.querySelector('[id = "evaluation_tool_name"]').innerHTML;
                } else {
                    currentName = document.querySelector('[id = "evaluation_tool_name_' + currentIndex + '"]').innerHTML;
                }
            }
            let modalBody = document.querySelector('#alertModal').querySelector('.modal-body');
            modalBody.innerHTML = await getString('alertmodalbody', 'format_udehauthoring', currentName);
            window.$('#alertModal').modal('toggle');
        });
    });
}

/**
 *
 */
function deleteToolFromModal() {
    let deleteButton = document.getElementById('btn_delete_tool');

    deleteButton.addEventListener('click', () => {
        let cmid = null;
        let id = null;
        let type = null;
        if(window.location.pathname.includes('subquestion')) {
            type = 1;
            cmid = document.querySelector('[name = "exploration_tool_cmid[' + currentIndex + ']"]');
            id = document.querySelector('[name = "exploration_id[' + currentIndex + ']"]');
        } else {
            type = 2;
            if(currentIndex === null) {
                cmid = document.querySelector('[name = "evaluation_tool_cmid"]');
                id = document.querySelector('[name = "id"]');
            } else {
                cmid = document.querySelector('[name = "evaluation_tool_cmid[' + currentIndex + ']"]');
                id = document.querySelector('[name = "evaluation_id[' + currentIndex + ']"]');
            }
        }
        window.$.ajax(
            {
                type: "POST",
                url: "../handlers/ajax_tool_handler.php",
                data: {
                    cmid: cmid.value,
                    id: id.value,
                    type: type,
                },
                success: function() {
                    window.$('#alertModal').modal('toggle');
                    if (currentIndex === null) {
                        document.getElementById('fgroup_id_url_group').style = "display:none;";
                        document.querySelector('[name*="generate_tool"]').disabled = false;
                        let select = document.getElementById('id_evaluation_tool');
                        select.disabled = false;
                    } else {
                        document.getElementById('fgroup_id_url_group_' + currentIndex).style = "display:none;";
                        document.getElementById('id_tool_group_' + currentIndex + '_generate_tool').disabled = false;
                        if (window.location.pathname.includes('subquestion')) {
                            document.getElementById('id_tool_group_' + currentIndex + '_exploration_tool').disabled = false;
                        } else {
                            document.getElementById('id_tool_group_' + currentIndex + '_evaluation_tool').disabled = false;
                        }

                    }
                    currentIndex = null;
                    window.location.reload();
                },
                error: function() {
                    window.console.log('failure');
                }
            });
    });
}