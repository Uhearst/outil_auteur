import {
    buildHiddenInput,
    prepareActionButton,
    setEditorAfterCloning,
    initEditor,
    removeTinyEditor,
    updateEditorAndLabel,
    setDeleteButton,
} from "./utils";
import {get_string as getString} from 'core/str';
import {handleAccordions, handleNewAccordion, initAccordionsAfterFillingForm} from "./accordionHandler";

let counterTeachingObjectives = 0;
let cloneTooltipTeachingObjectives = null;
let cloneTooltipLearningObjectives = null;
let cloneTooltipLearningObjectivesCompetency = null;

/**
 * @param {int} containerIndex
 * @param {boolean} fromBtnClick
 */
async function addLearningObjectives(containerIndex, fromBtnClick) {
    let learningObjectivesContainer = document.querySelector('#course_learning_objectives_container_' + containerIndex);
    let learningIndex = learningObjectivesContainer ? learningObjectivesContainer.children.length : 0;
    let learningObjective = document.querySelector('#course_learning_objectives_container_0').firstElementChild.cloneNode(true);

    learningObjective.querySelector('#course_learning_objectives_subcontainer_0_0')
        .setAttribute('id', 'course_learning_objectives_subcontainer_' + containerIndex + '_' + learningIndex);

    // Header
    learningObjective.querySelector('#course_learning_objectives_header_0_0')
        .setAttribute('id', 'course_learning_objectives_header_' + containerIndex + '_' + learningIndex);
    learningObjective.querySelector('[href="#collapse_learning_0_0"]').innerText =
        await getString('learningobjective', 'format_udehauthoring') + ' '
        + (parseInt(containerIndex) + 1) + '.' + (learningIndex + 1);
    learningObjective.querySelector('[href="#collapse_learning_0_0"]')
        .setAttribute('aria-controls', 'collapse_learning_' + containerIndex + '_' + learningIndex);
    learningObjective.querySelector('[href="#collapse_learning_0_0"]')
        .setAttribute('href', '#collapse_learning_' + containerIndex + '_' + learningIndex);

    learningObjective.querySelector('#collapse_learning_0_0')
        .setAttribute('data-parent', '#course_learning_objectives_container_' + containerIndex);
    learningObjective.querySelector('#collapse_learning_0_0')
        .setAttribute('id', 'collapse_learning_' + containerIndex + '_' + learningIndex);

    learningObjective.querySelector('#course_learning_objectives_0_0')
        .setAttribute('id', 'course_learning_objectives_' + containerIndex + '_' + learningIndex);

    if (learningObjective.querySelector('[name="course_learning_objectives_0_0_id_value"]')) {
        learningObjective.querySelector('[name="course_learning_objectives_0_0_id_value"]').setAttribute('value', '');
        learningObjective.querySelector('[name="course_learning_objectives_0_0_id_value"]')
            .setAttribute('name', 'course_learning_objectives_' + containerIndex + '_' + learningIndex + '_id_value');
    }

    // Content
    setEditorAfterCloning(
        learningObjective,
        'learning_objectives_def_0',
        'course',
        learningIndex,
        'learning_objectives_def_' + containerIndex,
        fromBtnClick
    );

    // Delete button
    setDeleteButton(learningObjective, 'remove_learning_objectives', counterTeachingObjectives, '_' + learningIndex);


    // Competency
    let competencyContainer = learningObjective.querySelector('#fitem_id_course_learning_objectives_competency_type_0_0');
    competencyContainer.setAttribute(
        'id',
        'fitem_id_course_learning_objectives_competency_type_' + containerIndex + '_' + learningIndex
    );
    competencyContainer.querySelector('#id_course_learning_objectives_competency_type_0_0')
        .setAttribute('name', 'course_learning_objectives_competency_type_' + containerIndex + '_' + learningIndex);
    competencyContainer.querySelector('#id_course_learning_objectives_competency_type_0_0')
        .setAttribute('id', 'id_course_learning_objectives_competency_type_' + containerIndex + '_' + learningIndex);
    competencyContainer.querySelector('#id_error_course_learning_objectives_competency_type_0_0')
        .setAttribute('id', 'id_error_course_learning_objectives_competency_type_' + containerIndex + '_' + learningIndex);

    learningObjectivesContainer
        .insertBefore(learningObjective, learningObjectivesContainer.querySelector('.add_learning_container'));

    await updateAddLearningButton(containerIndex);
    disableLearningDeleteButton();

    if (fromBtnClick) {
        handleNewAccordion('#course_learning_objectives_header_' + containerIndex + '_', learningIndex);
        await handleEditor(containerIndex, learningIndex);
    }
}

/**
 * @param {boolean} fromBtnClick
 */
async function addTeachingObjectives(fromBtnClick) {
    counterTeachingObjectives = counterTeachingObjectives + 1;

    let teachingObjectiveParent = document.querySelector('#course_teaching_objectives_container_0').parentElement;
    let teachingObjective = teachingObjectiveParent.cloneNode(true);

    teachingObjective.querySelector('#course_teaching_objectives_container_0')
        .setAttribute('id', 'course_teaching_objectives_container_' + counterTeachingObjectives);

    // Header
    teachingObjective.querySelector('#course_teaching_objectives_header_0')
        .setAttribute('id', 'course_teaching_objectives_header_' + counterTeachingObjectives);
    teachingObjective.querySelector('[href="#collapse_teaching_0"]').innerText =
        await getString('teachingobjective', 'format_udehauthoring') + ' ' + (counterTeachingObjectives + 1);
    teachingObjective.querySelector('[href="#collapse_teaching_0"]')
        .setAttribute('aria-controls', 'collapse_teaching_' + counterTeachingObjectives);
    teachingObjective.querySelector('[href="#collapse_teaching_0"]')
        .setAttribute('href', '#collapse_teaching_' + counterTeachingObjectives);

    teachingObjective.querySelector('#collapse_teaching_0')
        .setAttribute('id', 'collapse_teaching_' + counterTeachingObjectives);

    teachingObjective.querySelector('#course_teaching_objectives_0')
        .setAttribute('id', 'course_teaching_objectives_' + counterTeachingObjectives);

    if (teachingObjective.querySelector('[name="course_teaching_objectives_0_id_value"]')) {
        teachingObjective.querySelector('[name="course_teaching_objectives_0_id_value"]').setAttribute('value', '');
        teachingObjective.querySelector('[name="course_teaching_objectives_0_id_value"]')
            .setAttribute('name', 'course_teaching_objectives_' + counterTeachingObjectives + '_id_value');
    }


    // Content
    setEditorAfterCloning(teachingObjective,
        'teaching_objectives',
        'course',
        counterTeachingObjectives,
        null,
        fromBtnClick
    );

    // Learning obj
    let learningContainer = teachingObjective.querySelector('#course_learning_objectives_container_0');
    if (learningContainer) {
        while (learningContainer.firstChild) {
            learningContainer.removeChild(learningContainer.firstChild);
        }
        learningContainer.setAttribute('id', 'course_learning_objectives_container_' + counterTeachingObjectives);
    }

    // Delete button
    setDeleteButton(teachingObjective, 'remove_teaching_objectives', counterTeachingObjectives);

    let container = document.getElementById('displayable-form-objectives-container');
    container.insertBefore(teachingObjective, document.getElementById('teaching-add-container'));

    await addLearningObjectives(counterTeachingObjectives, fromBtnClick);

    await updateAddTeachingButton();
    disableTeachingDeleteButton();

    if (fromBtnClick) {
        handleNewAccordion('#course_teaching_objectives_header_', counterTeachingObjectives);
        await handleEditor(counterTeachingObjectives);
    }
}

const handleEditor = async(counter, secondCounter = null) => {
    window.$.ajax(
        {
            type: "POST",
            url: "../handlers/ajax_editor_handler.php",
            data: {
                id: document.querySelector("#udeh-form").querySelector('[name = "course_id"]').value,
                elementId: secondCounter !== null
                    ? 'id_course_learning_objectives_def_' + counter + '_' + secondCounter
                    : 'id_course_teaching_objectives_' + counter,
                text: ''
            },
            success: function(response) {
                let draftIdElm = '';
                if (secondCounter !== null) {
                    draftIdElm = document.querySelector('#course_learning_objectives_subcontainer_' + counter + '_' + secondCounter)
                        .querySelector('[name = "course_learning_objectives_def_' + counter + '_' + secondCounter + '[itemid]"]');
                } else {
                    draftIdElm = document.querySelector('#course_teaching_objectives_container_' + counter)
                        .querySelector('[name = "course_teaching_objectives_' + counter + '[itemid]"]');
                }
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

/**
 * @param {string} index
 */
async function removeTeachingObjective(index) {
    let teachingObjective = document.getElementById('course_teaching_objectives_container_' + index);
    await removeTinyEditor('id_course_teaching_objectives_' + index);
    let learningObjectives = teachingObjective.querySelectorAll('div.row-container-child');

    for (let i = 0; i < learningObjectives.length; i++) {
        await removeTinyEditor("id_course_learning_objectives_def_" + index + '_' + i);
    }

    teachingObjective.parentElement.remove();
    await updateExistingTeachingObjectives(index);
    await updateAddTeachingButton();
    updateRemoveTeachingButton();
    disableTeachingDeleteButton();
    counterTeachingObjectives = counterTeachingObjectives - 1;
}

/**
 * @param {string} teachingIndex
 * @param {string} learningIndex
 */
async function removeLearningObjective(teachingIndex, learningIndex) {
    let learningObjective =
        document.getElementById('course_learning_objectives_subcontainer_' + teachingIndex + '_' + learningIndex);
    await removeTinyEditor("id_course_learning_objectives_def_" + teachingIndex + '_' + learningIndex);
    learningObjective.parentElement.remove();
    await updateExistingLearningObjectiveForIndex(teachingIndex, learningIndex);
    await updateAddLearningButton(parseInt(teachingIndex));
    disableLearningDeleteButton();
}

/**
 * @param {string} index
 */
async function updateExistingTeachingObjectives(index) {
    let teachingObjectives = document.querySelectorAll('[id^="course_teaching_objectives_container_"]');
    for (const teachingObjective of teachingObjectives) {
        if (parseInt(teachingObjective.id.substring(teachingObjective.id.lastIndexOf('_') + 1)) > parseInt(index)) {
            let currentIndex = parseInt(teachingObjective.id.substring(teachingObjective.id.lastIndexOf('_') + 1));
            await removeTinyEditor('id_course_teaching_objectives_' + currentIndex);
            teachingObjective.setAttribute('id', 'course_teaching_objectives_container_' + (currentIndex - 1));
            let headerContainer = teachingObjective.querySelector('[id^="course_teaching_objectives_header_"]');
            headerContainer.setAttribute('id', "course_teaching_objectives_header_" + (currentIndex - 1));
            headerContainer.firstElementChild.setAttribute('href', '#collapse_teaching_' + (currentIndex - 1));
            headerContainer.firstElementChild.setAttribute('aria-controls', 'collapse_teaching_' + (currentIndex - 1));
            headerContainer.firstElementChild.innerHTML =
                await getString('teachingobjective', 'format_udehauthoring') + ' ' + (currentIndex);

            let contentContainer = teachingObjective.querySelector('[id^="collapse_teaching_"]');
            contentContainer.setAttribute('id', 'collapse_teaching_' + (currentIndex - 1));
            contentContainer.firstElementChild.setAttribute('id', 'course_teaching_objectives_' + (currentIndex - 1));

            if (contentContainer.querySelector('[name$="_id_value"]')) {
                contentContainer.querySelector('[name$="_id_value"]')
                    .setAttribute('name', 'course_teaching_objectives_' + (currentIndex - 1) + '_id_value');
            }

            await updateEditorAndLabel(contentContainer, currentIndex, 'teaching_objectives', 'course');
            await handleEditor((currentIndex - 1));
            await updateExistingLearningObjectiveFromTeachingUpdate(currentIndex);
        }
    }
}

/**
 * @param {int} teachingIndex
 */
async function updateExistingLearningObjectiveFromTeachingUpdate(teachingIndex) {
    let learningObjectiveListContainer = document.getElementById('course_learning_objectives_container_' + teachingIndex);
    learningObjectiveListContainer.setAttribute('id', 'course_learning_objectives_container_' + (teachingIndex - 1));
    let list = learningObjectiveListContainer.querySelectorAll('[id^="course_learning_objectives_subcontainer_"]');
    for (const learningObjectiveContainer of list) {
        let currentLearningIndex = parseInt(learningObjectiveContainer.id
            .substring(learningObjectiveContainer.id.lastIndexOf('_') + 1));
        await updateLearningObjective(learningObjectiveContainer, teachingIndex - 1, currentLearningIndex);
        let buttonContainer =
            document.getElementById('fitem_id_remove_learning_objectives_' + teachingIndex + '_' + currentLearningIndex);
        updateRemoveLearningButton(buttonContainer, teachingIndex - 1, currentLearningIndex);
    }
    await updateAddLearningButton(teachingIndex - 1);
}


/**
 * @param {string} teachingIndex
 * @param {string} learningIndex
 */
async function updateExistingLearningObjectiveForIndex(teachingIndex, learningIndex) {
    let learningObjectiveListContainer = document.getElementById('course_learning_objectives_container_' + teachingIndex);
    let list = learningObjectiveListContainer.querySelectorAll('[id^="course_learning_objectives_subcontainer_"]');
    for (const learningObjectiveContainer of list) {
        if (parseInt(learningObjectiveContainer.id
            .substring(learningObjectiveContainer.id.lastIndexOf('_') + 1)) > parseInt(learningIndex)) {
            let currentIndex = parseInt(
                learningObjectiveContainer.id.substring(learningObjectiveContainer.id.lastIndexOf('_') + 1));
            await updateLearningObjective(learningObjectiveContainer, teachingIndex, currentIndex - 1);
            let buttonContainer =
                document.getElementById('fitem_id_remove_learning_objectives_' + teachingIndex + '_' + currentIndex);
            updateRemoveLearningButton(buttonContainer, teachingIndex, currentIndex - 1);
        }

    }
}

/**
 * @param {object} element
 * @param {string} teachingIndex
 * @param {string} learningIndex
 */
async function updateLearningObjective(element, teachingIndex, learningIndex) {
    await removeTinyEditor("id_course_learning_objectives_def_" + teachingIndex + '_' + learningIndex);
    element.setAttribute('id', 'course_learning_objectives_subcontainer_' + teachingIndex + '_' + learningIndex);
    let headerContainer = element.querySelector('[id^="course_learning_objectives_header_"]');
    headerContainer.setAttribute('id', "course_learning_objectives_header_" + teachingIndex + '_' + learningIndex);
    headerContainer.firstElementChild.setAttribute('href', '#collapse_learning_' + teachingIndex + '_' + learningIndex);
    headerContainer.firstElementChild.setAttribute('aria-controls', 'collapse_learning_' + teachingIndex + '_' + learningIndex);
    headerContainer.firstElementChild.innerHTML =
        await getString('learningobjective', 'format_udehauthoring')
        + ' '
        + (parseInt(teachingIndex) + 1)
        + '.' + (parseInt(learningIndex) + 1);

    let contentContainer = element.querySelector('[id^="collapse_learning_"]');
    contentContainer.setAttribute('id', 'collapse_learning_' + teachingIndex + '_' + learningIndex);
    contentContainer.setAttribute('data-parent', '#course_learning_objectives_container_' + teachingIndex);

    contentContainer.querySelector('[id^="fitem_id_course_learning_objectives_"]')
        .setAttribute('id', 'fitem_id_course_learning_objectives_' + teachingIndex + '_' + learningIndex);

    contentContainer.querySelector('[id^="course_learning_objectives_"]')
        .setAttribute('id', 'course_learning_objectives_' + teachingIndex + '_' + learningIndex);

    if (contentContainer.querySelector('[name$="_id_value"]')) {
        contentContainer.querySelector('[name$="_id_value"]')
            .setAttribute('name', 'course_learning_objectives_' + teachingIndex + '_' + learningIndex + '_id_value');
    }

    contentContainer.querySelector('[name^="course_learning_objectives_def_"]')
        .setAttribute('name', 'course_learning_objectives_def_' + teachingIndex + '_' + learningIndex + '[text]');
    contentContainer.querySelector('[id^="id_course_learning_objectives_def_"]')
        .setAttribute('id', 'id_course_learning_objectives_def_' + teachingIndex + '_' + learningIndex);

    contentContainer.querySelector('[name$="[format]"]')
        .setAttribute('name', 'course_learning_objectives_def_' + teachingIndex + '_' + learningIndex + '[format]');
    contentContainer.querySelector('[id$="format"]')
        .setAttribute('id', 'id_course_learning_objectives_def_' + teachingIndex + '_' + learningIndex + 'format');

    contentContainer.querySelector('[name$="[itemid]"]')
        .setAttribute('name', 'course_learning_objectives_def_' + teachingIndex + '_' + learningIndex + '[itemid]');

    contentContainer.querySelector('[id^="id_error_course_learning_objectives_def_"]')
        .setAttribute('id', 'id_error_course_learning_objectives_def_' + teachingIndex + '_' + learningIndex);

    await handleEditor(teachingIndex, learningIndex);

    contentContainer
        .querySelector('[id^="fitem_id_course_learning_objectives_competency_type_"]')
        .setAttribute('id', 'fitem_id_course_learning_objectives_competency_type_' + teachingIndex + '_' + learningIndex);

    contentContainer.querySelector('[id^="id_course_learning_objectives_competency_type_"]')
        .setAttribute('id', 'id_course_learning_objectives_competency_type_' + teachingIndex + '_' + learningIndex);
    contentContainer.querySelector('[for^="id_course_learning_objectives_competency_type_"]')
        .setAttribute('for', 'id_course_learning_objectives_competency_type_' + teachingIndex + '_' + learningIndex);

    contentContainer.querySelector('[name^="course_learning_objectives_competency_type_"]')
        .setAttribute('name', 'course_learning_objectives_competency_type_' + teachingIndex + '_' + learningIndex);
    contentContainer.querySelector('[id^="id_error_course_learning_objectives_competency_type_"]')
        .setAttribute('id', 'id_error_course_learning_objectives_competency_type_' + teachingIndex + '_' + learningIndex);
}

/**
 * @param {object} element
 * @param {string} teachingIndex
 * @param {string} learningIndex
 */
function updateRemoveLearningButton(element, teachingIndex, learningIndex) {
    element.setAttribute('id', 'fitem_id_remove_learning_objectives_' + teachingIndex + '_' + learningIndex);
    let buttonElm = element.querySelector('[type="button"]');
    buttonElm.setAttribute('name', 'remove_learning_objectives_' + teachingIndex + '_' + learningIndex);
    buttonElm.setAttribute('id', 'id_remove_learning_objectives_' + teachingIndex + '_' + learningIndex);
}

/**
 *
 */
function updateRemoveTeachingButton() {
    let buttons = document.querySelectorAll('[id^="fitem_id_remove_teaching_objectives"]');
    buttons.forEach(function (button, index) {
        button.setAttribute('id', 'fitem_id_remove_teaching_objectives_' + index);
        let buttonElm = button.querySelector('[type="button"]');
        buttonElm.setAttribute('name', 'remove_teaching_objectives_' + index);
        buttonElm.setAttribute('id', 'id_remove_teaching_objectives_' + index);
    });
}

/**
 */
async function updateAddTeachingButton() {
    let formContainer = document.getElementById('displayable-form-objectives-container');
    let x = formContainer.querySelectorAll('.row-container');
    let addTeachingButtonContainer = document.getElementById('teaching-add-container');
    let addTeachingButtonText = addTeachingButtonContainer.querySelector('.add-text');
    addTeachingButtonText.innerHTML = await getString('teachingobjective', 'format_udehauthoring') + ' ' + (x.length + 1);
}

/**
 * @param {int} index
 */
async function updateAddLearningButton(index) {
    let teachingContainer = document.getElementById('course_teaching_objectives_container_' + index);
    let learningContainer = document.getElementById('course_learning_objectives_container_' + index);
    let x = learningContainer.querySelectorAll('.row-container-child');
    let addLearningButtonText = teachingContainer.querySelector('.add-text');
    addLearningButtonText.innerHTML =
        await getString('learningobjective', 'format_udehauthoring') + ' ' + (parseInt(index) + 1) + '.' + (x.length + 1);
    let buttonContainer = teachingContainer.querySelector('[id^="fitem_id_add_learning_objectives_"]');
    buttonContainer.setAttribute('id', 'fitem_id_add_learning_objectives_' + index);
    let buttonElement = buttonContainer.querySelector('[id^="id_add_learning_objectives"]');
    buttonElement.setAttribute('id', 'id_add_learning_objectives_' + index);
    buttonElement.setAttribute('name', 'add_learning_objectives_' + index);
}

/**
 */
function disableTeachingDeleteButton() {
    let container = document.getElementById('displayable-form-objectives-container');
    let x = container.querySelectorAll('.row-container');
    if (x.length === 1) {
        let buttonContainer = document.querySelector('.remove_teaching_action_button');
        let button = buttonContainer.querySelector('button');
        button.hidden = true;
    } else {
        let buttonContainers = document.querySelectorAll('.remove_teaching_action_button');
        buttonContainers.forEach(buttonContainer => {
            let button = buttonContainer.querySelector('button');
            button.hidden = false;
        });
    }
}

/**
 */
function disableLearningDeleteButton() {

    let teachingContainers = document.querySelectorAll('.row-container');
    teachingContainers.forEach(teachingContainer => {
        let learningContainers = teachingContainer.querySelectorAll('.row-container-child');
        if (learningContainers.length === 1) {
            let buttonContainer = learningContainers[0].querySelector('.remove_learning_action_button');
            let button = buttonContainer.querySelector('button');
            button.hidden = true;
        } else {
            learningContainers.forEach(learningContainer => {
                let buttonContainer = learningContainer.querySelector('.remove_learning_action_button');
                let button = buttonContainer.querySelector('button');
                button.hidden = false;
            });
        }
    });
}

/**
 * @param {int} index
 */
function getTeachingTooltip(index) {
    if (index === 0) {
        let teachingObjective = document.getElementById('fitem_id_course_teaching_objectives_0');
        let tooltip = teachingObjective.firstElementChild.firstElementChild.firstElementChild;
        if (tooltip) {
            cloneTooltipTeachingObjectives = tooltip.cloneNode(true);
            let teachingObjectiveHeader = document.getElementById('course_teaching_objectives_header_0');
            teachingObjectiveHeader.appendChild(tooltip);
        }
    } else {
        let teachingObjectiveHeader = document.getElementById('course_teaching_objectives_header_' + index);
        if (cloneTooltipTeachingObjectives) {
            let currentClone = cloneTooltipTeachingObjectives.cloneNode(true);
            teachingObjectiveHeader.appendChild(currentClone);
        }
    }
    getLearningTooltip(index, 0);
}

/**
 * @param {int} parentIndex
 * @param {int} index
 */
function getLearningTooltip(parentIndex, index) {
    if (parentIndex === 0 && index === 0) {
        let learningObjective = document.getElementById('fitem_id_course_learning_objectives_def_0_0');
        let teachingObjectiveCompetency = document.getElementById('fitem_id_course_learning_objectives_competency_type_0_0');
        let learningTooltip = learningObjective.firstElementChild.firstElementChild.firstElementChild;
        let tocHandle = teachingObjectiveCompetency.firstElementChild;
        let learningTooltipCompetency = null;
        if (typeof tocHandle.children[1] === 'undefined') {
            learningTooltipCompetency = tocHandle.firstElementChild.children[1].firstElementChild;
        } else {
            learningTooltipCompetency = tocHandle.children[1].firstElementChild;
        }
        if (learningTooltip) {
            cloneTooltipLearningObjectives = learningTooltip.cloneNode(true);
            let learningObjectiveHeader = document.getElementById('course_learning_objectives_header_0_0');
            learningObjectiveHeader.appendChild(learningTooltip);
        }
        if (learningTooltipCompetency) {
            cloneTooltipLearningObjectivesCompetency = learningTooltipCompetency.cloneNode(true);
        }

    } else {
        if (cloneTooltipLearningObjectives) {
            let learningObjectiveHeader = document.getElementById('course_learning_objectives_header_' + parentIndex + '_' + index);
            let currentClone = cloneTooltipLearningObjectives.cloneNode(true);
            learningObjectiveHeader.appendChild(currentClone);
        }

        if (cloneTooltipLearningObjectivesCompetency) {
            let learningObjectiveCompetencyContainer = document
                .getElementById('fitem_id_course_learning_objectives_competency_type_' + parentIndex + '_' + index);
            let competencyTooltipContainer = learningObjectiveCompetencyContainer.firstElementChild.children[1];
            let currentComptencyClone = cloneTooltipLearningObjectivesCompetency.cloneNode(true);
            competencyTooltipContainer.appendChild(currentComptencyClone);
        }
    }
}

export const initObjectives = () => {
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
            if (element && element.id.includes('learning') && element.hidden === false) {
                let n = element.id.lastIndexOf('_');
                let index = element.id.substring(n + 1);
                if (element.id.includes('remove')) {
                    let teachingId = element.id.charAt(element.id.length - 3);
                    await removeLearningObjective(teachingId, index);
                    document.getElementById('udeh-form').dataset.changed = 'true';
                    event.stopImmediatePropagation();
                } else {
                    event.stopImmediatePropagation();
                    await addLearningObjectives(index, true);
                    document.getElementById('udeh-form').dataset.changed = 'true';
                }
            } else if (element && element.id.includes('teaching') && element.hidden === false) {
                if (element.id.includes('remove')) {
                    let id = element.id.charAt(element.id.length - 1);
                    await removeTeachingObjective(id);
                    document.getElementById('udeh-form').dataset.changed = 'true';
                    event.stopImmediatePropagation();
                }
            } else {
                return;
            }
        }
    });
    let addButton = document.querySelector('#id_add_teaching_objective');
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
            if (element && element.id.includes('teaching') && element.hidden === false) {
                await addTeachingObjectives(true);
                document.getElementById('udeh-form').dataset.changed = 'true';
            } else {
                return;
            }
        }
    });
    prepareActionButton([
        { type: 0, id: 'teaching_objectives_0' },
        { type: 0, id: 'learning_objectives_0_0' },
        { type: 1, id: 'teaching_objective' },
        { type: 1, id: 'learning_objectives_0' }]);
    disableTeachingDeleteButton();
    disableLearningDeleteButton();
/*    prepareAccordions('course_teaching_objectives_container_', 1);
    prepareAccordions('course_learning_objectives_subcontainer_', 2);*/
};

export const fillFormObjectives = async(teachingObjectives) => {
    getTeachingTooltip(0);

    for (const teachingObjective of teachingObjectives) {
        const i = teachingObjectives.indexOf(teachingObjective);
        if (i === 0) {
            buildHiddenInput(teachingObjective.id, i, 'fitem_id_course_teaching_objectives_0', 'course_teaching_objectives_');
            document.querySelector('[name = "course_teaching_objectives_0_id_value"]')
                .setAttribute('value', teachingObjective.id);
            await fillTeachingLearningObjective(i, teachingObjective);
        } else {
            if (document.getElementById('id_course_teaching_objectives_' + i) === null) {
                await addTeachingObjectives(false);
            }
            document.querySelector('#id_course_teaching_objectives_' + i).value = teachingObjective.teachingobjective;
            document.querySelector('[name = "course_teaching_objectives_' + i + '_id_value"]')
                .setAttribute('value', teachingObjective.id);
            await handleEditor(i);
            await fillTeachingLearningObjective(i, teachingObjective);
        }
    }
    disableTeachingDeleteButton();
    disableLearningDeleteButton();
/*    prepareAccordions('course_teaching_objectives_container_', 1);
    prepareAccordions('course_learning_objectives_subcontainer_', 2);*/
    initAccordionsAfterFillingForm();
    handleAccordions();
};

const fillTeachingLearningObjective = async(teachingIndex, teachingObjective) => {
    if (teachingObjective.learningobjectives.length === 0) {
        await handleEditor(teachingIndex, 0);
    } else {
        for (const [j, learningobjective] of teachingObjective.learningobjectives.entries()) {
            if (j === 0 && teachingIndex === 0) {
                buildHiddenInput(
                    learningobjective.id,
                    teachingIndex + '_' + j,
                    'fitem_id_course_learning_objectives_def_0_0', 'course_learning_objectives_'
                );
                document.querySelector('#id_course_learning_objectives_competency_type_0_0').value =
                    learningobjective.learningobjectivecompetency;
                document.querySelector('[name = "course_learning_objectives_0_0_id_value"]')
                    .setAttribute('value', learningobjective.id);
            } else if (teachingIndex > 0 && j === 0) {
                setEditorsAndIdValue(teachingIndex, j, learningobjective);
                await handleEditor(teachingIndex, 0);
            } else {
                if (document.getElementById('id_course_learning_objectives_def_' + teachingIndex + '_' + j) === null) {
                    await addLearningObjectives(teachingIndex, false);
                }
                setEditorsAndIdValue(teachingIndex, j, learningobjective);
                await handleEditor(teachingIndex, j);
            }

            document.getElementById('id_course_learning_objectives_competency_type_' + teachingIndex + '_' + j).selectedIndex =
                learningobjective.learningobjectivecompetency;
        }
    }

};

const setEditorsAndIdValue = (i, j, learningobjective) => {
    document.querySelector('#id_course_learning_objectives_def_' + i + '_' + j).value =
        learningobjective.learningobjective;
    document.querySelector('#id_course_learning_objectives_competency_type_' + i + '_' + j).value =
        learningobjective.learningobjectivecompetency;
    document.querySelector('[name = "course_learning_objectives_' + i + '_' + j + '_id_value"]')
        .setAttribute('value', learningobjective.id);
};
