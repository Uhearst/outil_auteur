import {handleNewAccordionElement, initTinyMce, prepareAccordions, prepareActionButton, removeTinyMce} from "./utils";
import {dictionnary} from "./language/format_udehauthoring_fr";

let counterTeachingObjectives = 0;
let cloneTooltipTeachingObjectives = null;
let cloneTooltipLearningObjectives = null;
let cloneTooltipLearningObjectivesCompetency = null;

/**
 * @param {int} index
 * @param {boolean} fromBtnClick
 */
function addLearningObjectives(index, fromBtnClick) {
    let x = buildLearningObjectives(index);
    let container = document.getElementById('course_learning_objectives_container_' + index);
    container.appendChild(x);
    updateAddLearningButton(index);
    disableLearningDeleteButton();
    let newIndex = container.children.length;
    initTinyMce('id_course_learning_objectives_def_' + index + '_' + (newIndex - 1),
        'bold italic | underline strikethrough superscript subscript | undo redo');
    getLearningTooltip(index, (newIndex - 1));
    if (fromBtnClick) {
        handleNewAccordionElement(x);
    }
}

/**
 * @param {boolean} fromBtnClick
 */
function addTeachingObjectives(fromBtnClick) {
    let x = buildTeachingObjectives();
    let container = document.getElementById('displayable-form-objectives-container');
    container.insertBefore(x, document.getElementById('teaching-add-container'));
    updateAddTeachingButton();
    disableTeachingDeleteButton();
    initTinyMce('id_course_teaching_objectives_' + counterTeachingObjectives,
        'bold italic | underline strikethrough superscript subscript | undo redo');
    initTinyMce('id_course_learning_objectives_def_' + counterTeachingObjectives + '_' + 0,
        'bold italic | underline strikethrough superscript subscript | undo redo');
    getTeachingTooltip(counterTeachingObjectives);
    if (fromBtnClick) {
        handleNewAccordionElement(x);
    }
}

/**
 * @param {string} index
 */
function removeTeachingObjective(index) {
    let teachingObjective = document.getElementById('course_teaching_objectives_container_' + index);
    removeTinyMce("id_course_teaching_objectives_" + index);
    let learningObjectives = teachingObjective.querySelectorAll('div.row-container-child');
    let counter = 0;
    learningObjectives.forEach(() => {
        removeTinyMce("id_course_learning_objectives_def_" + index + '_' + counter);
        counter = counter + 1;
    });
    teachingObjective.parentElement.remove();
    updateExistingTeachingObjectives(index);
    updateAddTeachingButton();
    updateRemoveTeachingButton();
    disableTeachingDeleteButton();
    counterTeachingObjectives = counterTeachingObjectives - 1;
}

/**
 * @param {string} teachingIndex
 * @param {string} learningIndex
 */
function removeLearningObjective(teachingIndex, learningIndex) {
    let learningObjective =
        document.getElementById('course_learning_objectives_subcontainer_' + teachingIndex + '_' + learningIndex);
    removeTinyMce("id_course_learning_objectives_def_" + teachingIndex + '_' + learningIndex);
    learningObjective.parentElement.remove();
    updateExistingLearningObjectiveForIndex(teachingIndex, learningIndex);
    updateAddLearningButton(parseInt(teachingIndex));
    disableLearningDeleteButton();
}

/**
 * @param {string} index
 */
function updateExistingTeachingObjectives(index) {
    let teachingObjectives = document.querySelectorAll('[id^="course_teaching_objectives_container_"]');
    teachingObjectives.forEach(teachingObjective => {
        if (parseInt(teachingObjective.id.substring(teachingObjective.id.lastIndexOf('_') + 1)) > parseInt(index)) {
            let currentIndex = parseInt(teachingObjective.id.substring(teachingObjective.id.lastIndexOf('_') + 1));
            removeTinyMce('id_course_teaching_objectives_' + currentIndex);
            teachingObjective.setAttribute('id', 'course_teaching_objectives_container_' + (currentIndex - 1));
            let headerContainer = teachingObjective.querySelector('[id^="course_teaching_objectives_header_"]');
            headerContainer.setAttribute('id', "course_teaching_objectives_header_" + (currentIndex - 1));
            headerContainer.firstElementChild.setAttribute('href', '#collapse_teaching_' + (currentIndex - 1));
            headerContainer.firstElementChild.setAttribute('aria-controls', 'collapse_teaching_' + (currentIndex - 1));
            headerContainer.firstElementChild.innerHTML = dictionnary.teachingObjective + (currentIndex);

            let contentContainer = teachingObjective.querySelector('[id^="collapse_teaching_"]');
            contentContainer.setAttribute('id', 'collapse_teaching_' + (currentIndex - 1));
            contentContainer.firstElementChild.setAttribute('id', 'course_teaching_objectives_' + (currentIndex - 1));

            contentContainer.firstElementChild.firstElementChild
                .setAttribute('id', 'fitem_id_course_teaching_objectives_' + (currentIndex - 1));

            let currentTeachingTextArea = contentContainer.firstElementChild.firstElementChild.querySelector('textarea');

            currentTeachingTextArea.setAttribute('id', 'id_course_teaching_objectives_' + (currentIndex - 1));
            currentTeachingTextArea.setAttribute('name', 'course_teaching_objectives_' + (currentIndex - 1));
            initTinyMce('id_course_teaching_objectives_' + (currentIndex - 1),
                'bold italic | underline strikethrough superscript subscript | undo redo');
            updateExistingLearningObjectiveFromTeachingUpdate(currentIndex);
        }
    });
}

/**
 * @param {string} teachingIndex
 */
function updateExistingLearningObjectiveFromTeachingUpdate(teachingIndex) {
    let learningObjectiveListContainer = document.getElementById('course_learning_objectives_container_' + teachingIndex);
    learningObjectiveListContainer.setAttribute('id', 'course_learning_objectives_container_' + (teachingIndex - 1));
    let list = learningObjectiveListContainer.querySelectorAll('[id^="course_learning_objectives_subcontainer_"]');
    list.forEach(learningObjectiveContainer => {
        let currentLearningIndex = parseInt(learningObjectiveContainer.id
            .substring(learningObjectiveContainer.id.lastIndexOf('_') + 1));
        removeTinyMce('id_course_learning_objectives_def_' + teachingIndex + '_' + currentLearningIndex);
        updateLearningObjective(learningObjectiveContainer, teachingIndex - 1, currentLearningIndex);
        initTinyMce('id_course_learning_objectives_def_' + (teachingIndex - 1) + '_' + currentLearningIndex,
            'bold italic | underline strikethrough superscript subscript | undo redo');
        let buttonContainer =
            document.getElementById('fitem_id_remove_learning_objectives_' + teachingIndex + '_' + currentLearningIndex);
        updateRemoveLearningButton(buttonContainer, teachingIndex - 1, currentLearningIndex);
    });
    updateAddLearningButton(teachingIndex - 1);
}


/**
 * @param {string} teachingIndex
 * @param {string} learningIndex
 */
function updateExistingLearningObjectiveForIndex(teachingIndex, learningIndex) {
    let learningObjectiveListContainer = document.getElementById('course_learning_objectives_container_' + teachingIndex);
    let list = learningObjectiveListContainer.querySelectorAll('[id^="course_learning_objectives_subcontainer_"]');
    list.forEach(learningObjectiveContainer => {
        if (parseInt(learningObjectiveContainer.id
            .substring(learningObjectiveContainer.id.lastIndexOf('_') + 1)) > parseInt(learningIndex)) {
            let currentIndex = parseInt(
                learningObjectiveContainer.id.substring(learningObjectiveContainer.id.lastIndexOf('_') + 1));
            removeTinyMce('id_course_learning_objectives_def_' + teachingIndex + '_' + currentIndex);
            updateLearningObjective(learningObjectiveContainer, teachingIndex, currentIndex - 1);
            initTinyMce('id_course_learning_objectives_def_' + teachingIndex + '_' + (currentIndex - 1),
                'bold italic | underline strikethrough superscript subscript | undo redo');
            let buttonContainer =
                document.getElementById('fitem_id_remove_learning_objectives_' + teachingIndex + '_' + currentIndex);
            updateRemoveLearningButton(buttonContainer, teachingIndex, currentIndex - 1);
        }

    });
}

/**
 * @param {object} element
 * @param {string} teachingIndex
 * @param {string} learningIndex
 */
function updateLearningObjective(element, teachingIndex, learningIndex) {
    element.setAttribute('id', 'course_learning_objectives_subcontainer_' + teachingIndex + '_' + learningIndex);
    let headerContainer = element.querySelector('[id^="course_learning_objectives_header_"]');
    headerContainer.setAttribute('id', "course_learning_objectives_header_" + teachingIndex + '_' + learningIndex);
    headerContainer.firstChild.setAttribute('href', '#collapse_learning_' + teachingIndex + '_' + learningIndex);
    headerContainer.firstChild.setAttribute('aria-controls', 'collapse_learning_' + teachingIndex + '_' + learningIndex);
    headerContainer.firstChild.innerHTML =
        dictionnary.learningObjective + (parseInt(teachingIndex) + 1) + '.' + (parseInt(learningIndex) + 1);

    let contentContainer = element.querySelector('[id^="collapse_learning_"]');
    contentContainer.setAttribute('id', 'collapse_learning_' + teachingIndex + '_' + learningIndex);
    contentContainer.setAttribute('data-parent', '#course_learning_objectives_container_' + teachingIndex);
    contentContainer.firstElementChild.setAttribute('id', 'course_learning_objective_' + teachingIndex + '_' + learningIndex);

    contentContainer.children[0].firstChild
        .setAttribute('id', 'fitem_id_course_learning_objectives_' + teachingIndex + '_' + learningIndex);

    if (contentContainer.children[0].firstElementChild.children.length === 1) {
        contentContainer.children[0].firstElementChild.children[0]
            .setAttribute('name', 'course_learning_objectives_def_' + teachingIndex + '_' + learningIndex);
        contentContainer.children[0].firstElementChild.children[0]
            .setAttribute('id', 'id_course_learning_objectives_def_' + teachingIndex + '_' + learningIndex);
    } else if (contentContainer.children[0].firstElementChild.children.length === 2) {
        contentContainer.children[0].firstElementChild.children[0]
            .setAttribute('name', 'course_learning_objectives_' + teachingIndex + '_' + learningIndex + '_id_value');
        contentContainer.children[0].firstElementChild.children[1]
            .setAttribute('name', 'course_learning_objectives_def_' + teachingIndex + '_' + learningIndex);
        contentContainer.children[0].firstElementChild.children[1]
            .setAttribute('id', 'id_course_learning_objectives_def_' + teachingIndex + '_' + learningIndex);
    }


    contentContainer.children[0].children[1]
        .setAttribute('id', 'fitem_id_course_learning_objectives_competency_type_' + teachingIndex + '_' + learningIndex);
    contentContainer.children[0].children[1].firstChild.firstChild
        .setAttribute('id', 'id_course_learning_objectives_competency_type_' + teachingIndex + '_' + learningIndex);
    contentContainer.children[0].children[1].firstChild.firstChild
        .setAttribute('for', 'id_course_learning_objectives_competency_type_' + teachingIndex + '_' + learningIndex);
    contentContainer.children[0].children[1].firstChild.firstChild
        .innerHTML = dictionnary.learningObjectiveCompetencyType;
    contentContainer.children[0].children[1].children[1].firstChild
        .setAttribute('id', 'id_course_learning_objectives_competency_type_' + teachingIndex + '_' + learningIndex);
    contentContainer.children[0].children[1].children[1].firstChild
        .setAttribute('name', 'course_learning_objectives_competency_type_' + teachingIndex + '_' + learningIndex);
    contentContainer.children[0].children[1].children[1].children[1].setAttribute('id',
        'id_error_id_course_learning_objectives_competency_type_' + teachingIndex + '_' + learningIndex);
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
    buttons.forEach(function(button, index) {
        button.setAttribute('id', 'fitem_id_remove_teaching_objectives_' + index);
        let buttonElm = button.querySelector('[type="button"]');
        buttonElm.setAttribute('name', 'remove_teaching_objectives_' + index);
        buttonElm.setAttribute('id', 'id_remove_teaching_objectives_' + index);
    });
}

/**
 */
function updateAddTeachingButton() {
    let formContainer = document.getElementById('displayable-form-objectives-container');
    let x = formContainer.querySelectorAll('.row-container');
    let addTeachingButtonContainer = document.getElementById('teaching-add-container');
    let addTeachingButtonText = addTeachingButtonContainer.querySelector('.add-text');
    addTeachingButtonText.innerHTML = dictionnary.teachingObjective + (x.length + 1);
}

/**
 * @param {int} index
 */
function updateAddLearningButton(index) {
    let teachingContainer = document.getElementById('course_teaching_objectives_container_' + index);
    let learningContainer = document.getElementById('course_learning_objectives_container_' + index);
    let x = learningContainer.querySelectorAll('.row-container-child');
    let addLearningButtonText = teachingContainer.querySelector('.add-text');
    addLearningButtonText.innerHTML = dictionnary.targetedLearningObjective + (parseInt(index) + 1) + '.' + (x.length + 1);
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
 * @param {string} containerIndex
 */
function buildLearningObjectives(containerIndex) {
    let container = document.getElementById('course_learning_objectives_container_' + containerIndex);
    let rowContainer = document.createElement("div");
    let newIndex = container ? container.children.length : 0;
    let courseLearningDiv = document.createElement("div");
    let headerDiv = document.createElement("div");
    let inputDiv = document.createElement("div");
    let selectDiv = document.createElement("div");
    let linkForCollapseDiv = document.createElement('a');
    let collapseDiv = document.createElement("div");
    let contentContainer = document.createElement("div");

    rowContainer.setAttribute('class', 'row row-container-child mb-3');

    courseLearningDiv.setAttribute('id', 'course_learning_objectives_subcontainer_' + containerIndex + '_' + newIndex);
    courseLearningDiv.setAttribute('class', 'col-11 accordion-container card');

    headerDiv.setAttribute('class', 'accordion-header card-header');
    headerDiv.setAttribute('id', 'course_learning_objectives_header_' + (containerIndex) + '_' + newIndex);

    linkForCollapseDiv.setAttribute('data-toggle', 'collapse');
    linkForCollapseDiv.setAttribute('href', '#collapse_learning_' + (containerIndex) + '_' + newIndex);
    linkForCollapseDiv.setAttribute('role', 'button');
    linkForCollapseDiv.setAttribute('aria-exapnded', 'false');
    linkForCollapseDiv.setAttribute('aria-controls', 'collapse_learning_' + (containerIndex) + '_' + newIndex);
    linkForCollapseDiv.setAttribute('class', 'collapsed');
    linkForCollapseDiv.innerHTML = dictionnary.learningObjective + (parseInt(containerIndex) + 1) + '.' + (newIndex + 1);

    collapseDiv.setAttribute('class', 'collapse');
    collapseDiv.setAttribute('id', 'collapse_learning_' + (containerIndex) + '_' + newIndex);
    collapseDiv.setAttribute('data-parent', '#course_learning_objectives_container_' + (containerIndex));

    contentContainer.setAttribute('class', 'card-body accordion-content');
    contentContainer.setAttribute('id', 'course_learning_objective_' + (containerIndex) + '_' + newIndex);

    inputDiv.setAttribute("id", "fitem_id_course_learning_objectives_" + containerIndex + '_' + newIndex);
    inputDiv.setAttribute("class", "form-group row  fitem   ");
    inputDiv.appendChild(buildTinyMCE('course_learning_objectives_def_', containerIndex, newIndex.toString()));
    selectDiv.setAttribute("id",
        "fitem_id_course_learning_objectives_competency_type_" + containerIndex + '_' + newIndex);
    selectDiv.setAttribute("class", "form-group row  fitem   ");
    selectDiv.appendChild(buildLabelDiv(
        'id_course_learning_objectives_competency_type_' + containerIndex + '_' + newIndex,
        dictionnary.learningObjectiveCompetencyType));
    selectDiv.appendChild(buildSelectDiv('course_learning_objectives_competency_type_', newIndex));

    headerDiv.appendChild(linkForCollapseDiv);

    contentContainer.appendChild(inputDiv);
    contentContainer.appendChild(selectDiv);

    collapseDiv.appendChild(contentContainer);

    courseLearningDiv.appendChild(headerDiv);
    courseLearningDiv.appendChild(collapseDiv);

    let buttonElement = buildButtonDiv('remove_learning_objectives_' + containerIndex + '_' + newIndex, 0);

    rowContainer.appendChild(courseLearningDiv);
    rowContainer.appendChild(buttonElement);

    return rowContainer;
}

/**
 *
 */
function buildTeachingObjectives() {
    let teachingObjectivesContainer = document.createElement("div");
    let teachingObjectivesInputDiv = document.createElement("div");
    let headerDiv = document.createElement("div");
    let courseTeachingDiv = document.createElement("div");
    let rowContainer = document.createElement("div");
    let linkForCollapseDiv = document.createElement('a');
    let collapseDiv = document.createElement("div");
    let learningObjectivesContainer = document.createElement("div");

    counterTeachingObjectives = counterTeachingObjectives + 1;

    rowContainer.setAttribute('class', 'row row-container mb-3');
    teachingObjectivesContainer.setAttribute('id', 'course_teaching_objectives_container_' + counterTeachingObjectives);
    teachingObjectivesContainer.setAttribute('class', 'col-11 accordion-container card');

    headerDiv.setAttribute('class', 'accordion-header card-header');
    headerDiv.setAttribute('id', 'course_teaching_objectives_header_' + (counterTeachingObjectives));

    linkForCollapseDiv.setAttribute('data-toggle', 'collapse');
    linkForCollapseDiv.setAttribute('href', '#collapse_teaching_' + (counterTeachingObjectives));
    linkForCollapseDiv.setAttribute('role', 'button');
    linkForCollapseDiv.setAttribute('aria-expanded', 'false');
    linkForCollapseDiv.setAttribute('aria-controls', 'collapse_teaching_' + (counterTeachingObjectives));
    linkForCollapseDiv.setAttribute('class', 'collapsed');
    linkForCollapseDiv.innerHTML = dictionnary.teachingObjective + (counterTeachingObjectives + 1);

    learningObjectivesContainer.setAttribute('id', 'course_learning_objectives_container_' + counterTeachingObjectives);
    learningObjectivesContainer.setAttribute('class', 'course_learning_objectives_container');

    collapseDiv.setAttribute('class', 'collapse');
    collapseDiv.setAttribute('id', 'collapse_teaching_' + (counterTeachingObjectives));
    collapseDiv.setAttribute('data-parent', '#displayable-form-objectives-container');

    courseTeachingDiv.setAttribute('id', 'course_teaching_objectives_' + counterTeachingObjectives);
    courseTeachingDiv.setAttribute('class', 'card-body accordion-content');

    teachingObjectivesInputDiv.setAttribute("id", "fitem_id_course_teaching_objectives_" + counterTeachingObjectives);
    teachingObjectivesInputDiv.setAttribute("class", "form-group row  fitem   ");
    teachingObjectivesInputDiv.appendChild(buildTinyMCE('course_teaching_objectives_', counterTeachingObjectives));

    let learningObjectives = buildLearningObjectives(counterTeachingObjectives);
    let addLearningObjectivesButton = buildAddLearningObjectiveDiv();

    headerDiv.appendChild(linkForCollapseDiv);
    learningObjectivesContainer.appendChild(learningObjectives);
    courseTeachingDiv.appendChild(teachingObjectivesInputDiv);
    courseTeachingDiv.appendChild(learningObjectivesContainer);
    courseTeachingDiv.appendChild(addLearningObjectivesButton);
    collapseDiv.appendChild(courseTeachingDiv);
    teachingObjectivesContainer.appendChild(headerDiv);
    teachingObjectivesContainer.appendChild(collapseDiv);

    let buttonElement = buildButtonDiv('remove_teaching_objectives_' + counterTeachingObjectives, 0);

    rowContainer.appendChild(teachingObjectivesContainer);
    rowContainer.appendChild(buttonElement);

    return rowContainer;
}

// eslint-disable-next-line valid-jsdoc
/**
 *
 */
function buildAddLearningObjectiveDiv() {
    let addLearningObectivesContainer = document.createElement("div");
    let addLearningObectivesRowContainer = document.createElement("div");
    let addLearningObectivesLabelContainer = document.createElement("div");
    let addLearningObectivesLabel = document.createElement("span");
    let addLearningObectivesButton = buildButtonDiv('add_learning_objectives_' + counterTeachingObjectives, 1);

    addLearningObectivesLabel.innerHTML = dictionnary.targetedLearningObjective + (counterTeachingObjectives + 1) + '.' + (2);
    addLearningObectivesLabel.setAttribute('class', 'add-text');
    addLearningObectivesContainer.setAttribute('class', 'add_learning_container');
    addLearningObectivesRowContainer.setAttribute('class', 'row course_add_learning_objectives_row');
    addLearningObectivesLabelContainer.setAttribute('class', 'col-11 add-container-text card card-header');

    addLearningObectivesLabelContainer.appendChild(addLearningObectivesLabel);
    addLearningObectivesRowContainer.appendChild(addLearningObectivesLabelContainer);
    addLearningObectivesRowContainer.appendChild(addLearningObectivesButton);

    addLearningObectivesContainer.appendChild(addLearningObectivesRowContainer);

    return addLearningObectivesContainer;
}

/**
 * @param {string} idFor
 * @param {string} labelString
 */
function buildLabelDiv(idFor, labelString) {
    let labelContainerDiv = document.createElement("div");
    let label = document.createElement("label");
    let divForLabel = document.createElement("div");

    labelContainerDiv.setAttribute('class', "col-md-3 col-form-label d-flex pb-0 pr-md-0");

    label.setAttribute('class', 'd-inline word-break ');
    if (idFor !== '' && labelString !== '') {
        label.setAttribute('for', idFor);
        label.innerHTML = labelString;
    }
    divForLabel.setAttribute('class', 'form-label-addon d-flex align-items-center align-self-start');

    labelContainerDiv.appendChild(label);
    labelContainerDiv.appendChild(divForLabel);

    return labelContainerDiv;
}

/**
 * @param {string} element
 * @param {int} actionType
 */
function buildButtonDiv(element, actionType) {
    let colContainer = document.createElement("div");
    let buttonContainer = document.createElement("div");
    let buttonSubContainer = document.createElement("div");
    let buttonElement = document.createElement("button");
    let iconElement = document.createElement("i");

    if (actionType === 0) {
        if (element.includes('learning')) {
            colContainer.setAttribute('class', 'col-1 remove_learning_action_button');
        } else {

            colContainer.setAttribute('class', 'col-1 remove_teaching_action_button');
        }
        iconElement.setAttribute('class', 'remove-button-js fa fa-minus-circle fa-2x');
    } else {
        colContainer.setAttribute('class', 'col-1 add_action_button');
        iconElement.setAttribute('class', 'add-button fa fa-plus-circle fa-2x');
    }
    buttonContainer.setAttribute('id', 'fitem_id_' + element);
    buttonSubContainer.setAttribute('data-fieldtype', 'button');
    buttonElement.setAttribute('class', 'btn ml-0');
    buttonElement.setAttribute('name', element);
    buttonElement.setAttribute('id', 'id_' + element);
    buttonElement.setAttribute('type', 'button');

    buttonElement.appendChild(iconElement);
    buttonSubContainer.appendChild(buttonElement);
    buttonContainer.appendChild(buttonSubContainer);
    colContainer.appendChild(buttonContainer);

    return colContainer;
}


// eslint-disable-next-line valid-jsdoc
/**
 * @param {string} target
 * @param {string} teachingIndex
 * @param {string} learningIndex
 */
function buildTinyMCE(target, teachingIndex, learningIndex = null) {
    let inputContainerDiv = document.createElement("textarea");
    inputContainerDiv.setAttribute('class', 'custom-editor');
    if (learningIndex) {
        inputContainerDiv.setAttribute('id', 'id_' + target + teachingIndex + '_' + learningIndex);
        inputContainerDiv.setAttribute('name', target + teachingIndex + '_' + learningIndex);
    } else {
        inputContainerDiv.setAttribute('id', 'id_' + target + teachingIndex);
        inputContainerDiv.setAttribute('name', target + teachingIndex);
    }
    return inputContainerDiv;
}

/**
 * @param {string} target
 * @param {int} index
 */
function buildSelectDiv(target, index) {
    let selectContainerDiv = document.createElement("div");
    let select = document.createElement("select");
    let divForSelect = document.createElement("div");
    let optionChoice = ['CT', 'CC', 'CP'];

    selectContainerDiv.setAttribute('class', "col-md-9 form-inline align-items-start felement");
    selectContainerDiv.setAttribute('data-fieldtype', 'select');

    select.setAttribute('class', 'custom-select');
    select.setAttribute('name', target + counterTeachingObjectives + '_' + index);
    select.setAttribute('id', 'id_' + target + counterTeachingObjectives + '_' + index);
    for (let i = 0; i < 3; i++) {
        select.appendChild(buildOption(i, optionChoice[i]));
    }
    divForSelect.setAttribute('class', 'form-control-feedback invalid-feedback');
    divForSelect.setAttribute('id', 'id_error_' + target + counterTeachingObjectives + '_' + index);

    selectContainerDiv.appendChild(select);
    selectContainerDiv.appendChild(divForSelect);

    return selectContainerDiv;
}

/**
 * @param {int} value
 * @param {string} text
 */
function buildOption(value, text) {
    let option = document.createElement('option');
    option.setAttribute('value', value.toString());
    option.innerHTML = text;
    return option;
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
        let learningTooltipCompetency = teachingObjectiveCompetency.firstElementChild.children[1].firstElementChild;
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

/**
 * @param {int} id
 * @param {string} text
 * @param {string} counter
 * @param {int} parentCounter
 */
function buildIdHiddenInput(id, text, counter, parentCounter = null) {
    let input = document.createElement("input");
    input.setAttribute('name', 'course_' + text + '_objectives_' + parentCounter + '_' + counter + '_id_value');
    input.setAttribute('value', id);
    input.hidden = true;
    if (text === 'learning') {
        let elementId = 'id_course_' + text + '_objectives_def_' + parentCounter + '_' + counter;
        let element = document.getElementById(elementId);
        element.parentNode.insertBefore(input, element);
    } else {
        let element = document.getElementById('id_course_' + text + '_objectives_' + counter);
        element.parentNode.insertBefore(input, element);
    }
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
        if (element !== null) {
            if (type === 0) {
                element.innerHTML = toAppend;
            } else {
                element.value = toAppend;
            }
            clearInterval(timereditable);
        }
    }, 100);
}


export const initObjectives = () => {
    const formContainer = document.getElementById('form_container');
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
            if (element && element.id.includes('learning') && element.hidden === false) {
                let n = element.id.lastIndexOf('_');
                let index = element.id.substring(n + 1);
                if (element.id.includes('remove')) {
                    let teachingId = element.id.charAt(element.id.length - 3);
                    removeLearningObjective(teachingId, index);
                    event.stopImmediatePropagation();
                } else {
                    event.stopImmediatePropagation();
                    addLearningObjectives(index, true);
                }
            } else if (element && element.id.includes('teaching') && element.hidden === false) {
                if (element.id.includes('remove')) {
                    let id = element.id.charAt(element.id.length - 1);
                    removeTeachingObjective(id);
                    event.stopImmediatePropagation();
                }
            } else {
                return;
            }
        }
    });
    let addButton = document.querySelector('#id_add_teaching_objective');
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
            if (element && element.id.includes('teaching') && element.hidden === false) {
                addTeachingObjectives(true);
            } else {
                return;
            }
        }
    });
    prepareActionButton([
        {type: 0, id: 'teaching_objectives_0'},
        {type: 0, id: 'learning_objectives_0_0'},
        {type: 1, id: 'teaching_objective'},
        {type: 1, id: 'learning_objectives_0'}]);
    disableTeachingDeleteButton();
    disableLearningDeleteButton();
    prepareAccordions('course_teaching_objectives_container_', 1);
    prepareAccordions('course_learning_objectives_subcontainer_', 2);
};

export const fillFormObjectives = (teachingObjectives) => {
    getTeachingTooltip(0);
    teachingObjectives.forEach(function(teachingObjective, i) {
        if (i === 0) {
            waitWithInterval('id_course_teaching_objectives_0editable', 0, teachingObjective.teachingobjective);
            waitWithInterval('id_course_teaching_objectives_0', 1, teachingObjective.teachingobjective);
        } else {
            if (document.getElementById('id_course_teaching_objectives_' + i) === null) {
                addTeachingObjectives(false);
            }
            let element = document.getElementById('id_course_teaching_objectives_' + i);
            element.value = teachingObjective.teachingobjective;
        }

        buildIdHiddenInput(teachingObjective.id, 'teaching', i);
        teachingObjective.learningobjectives.forEach(function(learningobjective, j) {
            let learningElementText = null;
            let learningElementCompetency = null;
            if (j === 0 && i === 0) {
                waitWithInterval('id_course_learning_objectives_def_0_0editable', 0, learningobjective.learningobjective);
                // eslint-disable-next-line max-len
                waitWithInterval('id_course_learning_objectives_competency_type_' + i + '_0', 1, learningobjective.learningobjectivecompetency);
                waitWithInterval('id_course_learning_objectives_def_0_0', 1, learningobjective.learningobjective);
                learningElementCompetency = document.getElementById('id_course_learning_objectives_competency_type_' + i + '_0');
            } else if (i > 0 && j === 0) {
                learningElementText = document.getElementById('id_course_learning_objectives_def_' + i + '_' + j);
                learningElementCompetency = document.getElementById('id_course_learning_objectives_competency_type_' + i + '_' + j);
                learningElementCompetency.value = learningobjective.learningobjectivecompetency;
                learningElementText.value = learningobjective.learningobjective;
            } else {
                learningElementText = document.getElementById('id_course_learning_objectives_def_' + i + '_' + j);
                learningElementCompetency = document.getElementById('id_course_learning_objectives_competency_type_' + i + '_' + j);
                if (learningElementText && learningElementCompetency) {
                    learningElementCompetency.value = learningobjective.learningobjectivecompetency;
                    learningElementText.value = learningobjective.learningobjective;
                } else {
                    addLearningObjectives(i, false);
                    learningElementText = document.getElementById('id_course_learning_objectives_def_' + i + '_' + j);
                    // eslint-disable-next-line max-len
                    learningElementCompetency = document.getElementById('id_course_learning_objectives_competency_type_' + i + '_' + j);
                    learningElementCompetency.value = learningobjective.learningobjectivecompetency;
                    learningElementText.value = learningobjective.learningobjective;
                }

            }
            learningElementCompetency.selectedIndex = learningobjective.learningobjectivecompetency;
            buildIdHiddenInput(learningobjective.id, 'learning', j, i);
        });
    });
    disableTeachingDeleteButton();
    disableLearningDeleteButton();
    prepareAccordions('course_teaching_objectives_container_', 1);
    prepareAccordions('course_learning_objectives_subcontainer_', 2);
};
