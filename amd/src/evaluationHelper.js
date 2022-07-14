import {
    handleEmbed,
    handleNewAccordionElement,
    initTinyMce,
    prepareAccordions,
    prepareActionButton,
    // removeTags,
    removeTinyMce
} from "./utils";
import {dictionnary} from "./language/format_udehauthoring_fr";
import {validateEvaluationDataForm, validateEvaluationForm} from "./validator/evaluationValidator";
import {initRedactTools} from "./toolHelper";

let counterEvaluations = 0;
let modules = [];
let learningObjectives = [];

class Module {
    constructor(title, id) {
        this.title = title;
        this.id = id;
    }
}

/**
 * @param {boolean} fromBtnClick
 */
function addEvaluation(fromBtnClick) {
    let x = buildEvaluation();
    let container = document.getElementById('displayable-form-evaluations-container');
    container.insertBefore(x, document.getElementById('evaluation-add-container'));
    updateAddEvaluationButton();
    disableEvaluationDeleteButton();
    initTinyMce('id_evaluation_title_' + counterEvaluations, 'superscript subscript | undo redo');
    initTinyMce('id_evaluation_description_' + counterEvaluations,
        ['formatselect bold italic | numlist bullist indent outdent | link unlink | emoticons image ',
            // eslint-disable-next-line max-len
            'underline strikethrough superscript subscript | alignleft aligncenter alignright | charmap table removeformat | undo redo ']);
    getTooltips(counterEvaluations);
    if (fromBtnClick) {
        handleNewAccordionElement(x);
    }
}

/**
 * @param {string} index
 */
function removeEvaluation(index) {
    let evaluation = document.getElementById('row_course_evaluation_container_' + index);
    removeTinyMce("id_evaluation_title_" + index);
    removeTinyMce("id_evaluation_description_" + index);

    evaluation.remove();
    updateExistingEvaluations(index);
    updateAddEvaluationButton();
    disableEvaluationDeleteButton();
    updateRemoveEvaluationButton();
    counterEvaluations = counterEvaluations - 1;
}

/**
 *
 */
function buildEvaluation() {
    counterEvaluations = counterEvaluations + 1;
    let rowEvaluationContainer = document.createElement("div");
    let colEvaluationContainer = document.createElement("div");
    let colButtonContainer = document.createElement("div");

    rowEvaluationContainer.setAttribute('class', 'row row-container mb-3');
    rowEvaluationContainer.setAttribute('id', 'row_course_evaluation_container_' + counterEvaluations);
    colEvaluationContainer.setAttribute('class', 'col-11 accordion-container card');
    colButtonContainer.setAttribute('class', 'col-1 remove_evaluation_action_button');

    let header = buildEvaluationHeader();
    let content = buildEvaluationContent();
    colEvaluationContainer.appendChild(header);
    colEvaluationContainer.appendChild(content);

    let button = buildEvaluationButton();
    colButtonContainer.appendChild(button);

    rowEvaluationContainer.appendChild(colEvaluationContainer);
    rowEvaluationContainer.appendChild(colButtonContainer);

    return rowEvaluationContainer;
}

/**
 *
 */
function buildEvaluationHeader() {
    let headerContainer = document.createElement("div");
    let linkForCollapseDiv = document.createElement('a');

    headerContainer.setAttribute('class', 'accordion-header card-header');
    headerContainer.setAttribute('id', 'course_evaluation_header_' + (counterEvaluations));

    linkForCollapseDiv.setAttribute('data-toggle', 'collapse');
    linkForCollapseDiv.setAttribute('href', '#collapse_evaluation_' + (counterEvaluations));
    linkForCollapseDiv.setAttribute('role', 'button');
    linkForCollapseDiv.setAttribute('aria-expanded', 'false');
    linkForCollapseDiv.setAttribute('aria-controls', 'collapse_evaluation_' + (counterEvaluations));
    linkForCollapseDiv.setAttribute('class', 'collapsed');
    linkForCollapseDiv.innerHTML = 'Evaluation ' + (counterEvaluations + 1);

    headerContainer.appendChild(linkForCollapseDiv);

    return headerContainer;
}

/**
 *
 */
function buildEvaluationContent() {
    let contentContainer = document.createElement("div");
    let collapseDiv = document.createElement("div");

    collapseDiv.setAttribute('class', 'collapse');
    collapseDiv.setAttribute('id', 'collapse_evaluation_' + (counterEvaluations));
    collapseDiv.setAttribute('data-parent', '#displayable-form-evaluations-container');

    contentContainer.setAttribute('class', 'card-body accordion-content');
    contentContainer.setAttribute('id', 'course_evaluation_content_' + (counterEvaluations));

    let title = buildEditorContainer('evaluation_title', dictionnary.evaluationTitle);
    let description = buildEditorContainer('evaluation_description', dictionnary.evaluationDescription);
    let weight = buildInputContainer('evaluation_weight', dictionnary.evaluationWeight);
    let learningObjectivesList = buildCheckboxContainer('evaluation_learning_objectives',
        dictionnary.associatedLearningObjective);
    let module = buildSelectContainer();

    contentContainer.appendChild(title);
    contentContainer.appendChild(description);
    contentContainer.appendChild(weight);
    contentContainer.appendChild(learningObjectivesList);
    contentContainer.appendChild(module);
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
    fieldContainer.setAttribute('id', 'fitem_id_' + id + '_' + counterEvaluations);

    labelContainer.setAttribute('class', 'col-md-3 col-form-label d-flex pb-0 pr-md-0');

    label.setAttribute('class', 'd-inline word-break');
    label.setAttribute('for', 'id_' + id + '_' + counterEvaluations);
    label.innerHTML = labelText;

    editorContainer.setAttribute('class', 'col-md-9 align-items-start felement');
    editorContainer.setAttribute('data-fieldtype', 'editor');

    let editor = buildTinyMCE(id, counterEvaluations);

    editorContainer.appendChild(editor);
    labelContainer.appendChild(label);

    fieldContainer.appendChild(labelContainer);
    fieldContainer.appendChild(editorContainer);

    return fieldContainer;
}

/**
 * @param {string} id
 * @param {string} labelText
 */
function buildInputContainer(id, labelText) {
    let fieldContainer = document.createElement("div");
    let labelContainer = document.createElement("div");
    let label = document.createElement("label");
    let inputContainer = document.createElement("div");
    let input = document.createElement("input");


    fieldContainer.setAttribute('class', 'form-group row  fitem   ');
    fieldContainer.setAttribute('id', 'fitem_id_' + id + '_' + counterEvaluations);

    labelContainer.setAttribute('class', 'col-md-3 col-form-label d-flex pb-0 pr-md-0');

    label.setAttribute('class', 'd-inline word-break');
    label.setAttribute('for', 'id_' + id + '_' + counterEvaluations);
    label.innerHTML = labelText;

    inputContainer.setAttribute('class', 'col-md-9 form-inline align-items-start felement');
    inputContainer.setAttribute('data-fieldtype', 'text');

    input.setAttribute('type', 'text');
    input.setAttribute('class', 'form-control');
    input.setAttribute('name', id + '_' + counterEvaluations);
    input.setAttribute('id', 'id_' + id + '_' + counterEvaluations);

    inputContainer.appendChild(input);
    labelContainer.appendChild(label);

    fieldContainer.appendChild(labelContainer);
    fieldContainer.appendChild(inputContainer);

    return fieldContainer;
}

/**
 * @param {string} id
 * @param {string} labelText
 */
function buildCheckboxContainer(id, labelText) {
    let fieldContainer = document.createElement("div");
    let titleContainer = document.createElement("div");
    let labelField = document.createElement("label");

    fieldContainer.setAttribute('id', 'fitem_id_' + id + '_' + counterEvaluations);

    titleContainer.setAttribute('id', 'evaluation_learning_objectives_title_' + counterEvaluations);
    titleContainer.setAttribute('class', 'd-flex');

    labelField.setAttribute('for', 'evaluation_learning_objectives_title_' + counterEvaluations);
    labelField.setAttribute('class', 'd-inline word-break ml-3 eval-obj-title');
    labelField.innerHTML = labelText;

    titleContainer.appendChild(labelField);
    fieldContainer.appendChild(titleContainer);

    if (learningObjectives !== null && learningObjectives !== []) {
        learningObjectives.forEach(function(parent, index) {
            let element = null;
            if(Array.isArray(parent)) {
                element = parent;
            } else {
                element = parent.learningobjectives;
            }
            element.forEach(function(obj, subIndex) {
                let labelContainer = document.createElement("div");
                let subContainer = document.createElement("div");
                let checkBoxContainer = document.createElement("div");
                let checkBoxSubContainer = document.createElement("div");
                let checkbox = document.createElement("input");
                let hiddenCheckbox = document.createElement("input");
                let labelCheckbox = document.createElement("label");

                subContainer.setAttribute('class', 'form-group row  fitem  ');

                labelContainer.setAttribute('class', 'col-md-3');
                checkBoxContainer.setAttribute('class', 'col-md-9 checkbox');

                checkBoxSubContainer.setAttribute('class', 'form-check d-flex');

                hiddenCheckbox.setAttribute('type', 'hidden');
                hiddenCheckbox.setAttribute('name', id + '_' + counterEvaluations + '[' + obj.id + ']');
                hiddenCheckbox.setAttribute('value', '0');

                checkbox.setAttribute('type', 'checkbox');
                checkbox.setAttribute('class', 'form-check-input ');
                checkbox.setAttribute('name', id + '_' + counterEvaluations + '[' + obj.id + ']');
                checkbox.setAttribute('id', 'id_' + id + '_' + counterEvaluations + '_' + obj.id);
                checkbox.setAttribute('value', '1');

                labelCheckbox.setAttribute('for', 'id_' + id + '_' + counterEvaluations);
                labelCheckbox.setAttribute('style', 'display: flex;');
                labelCheckbox.innerHTML = '<span style="margin-right: 0.5rem;">' + (index + 1)
                    + '.' + (subIndex + 1) + ' - ' + '</span>' + obj.learningobjective;

                checkBoxSubContainer.appendChild(hiddenCheckbox);
                checkBoxSubContainer.appendChild(checkbox);
                checkBoxSubContainer.appendChild(labelCheckbox);
                checkBoxContainer.appendChild(checkBoxSubContainer);

                subContainer.appendChild(labelContainer);
                subContainer.appendChild(checkBoxContainer);

                fieldContainer.appendChild(subContainer);
            });
        });
    }

    return fieldContainer;
}

/**
 *
 */
function formatInitialObjList() {
    let labels = document.querySelectorAll('[for^="id_evaluation_learning_objectives_0"]');
    for (let i = 0; i < labels.length; i++) {
        labels[i].setAttribute('style', 'display:flex;');
    }
}

/**
 *
 */
function buildSelectContainer() {
    let fieldContainer = document.createElement("div");
    let labelContainer = document.createElement('div');
    let selectContainer = document.createElement('div');
    let label = document.createElement('label');
    let select = document.createElement('select');

    fieldContainer.setAttribute('id', "fitem_id_evaluation_module_" + counterEvaluations);
    fieldContainer.setAttribute('class', "form-group row  fitem   ");

    labelContainer.setAttribute('class', "col-md-3 col-form-label d-flex pb-0 pr-md-0");

    label.setAttribute('class', 'd-inline word-break ');
    label.setAttribute('for', 'id_evaluation_module_' + counterEvaluations);
    label.innerHTML = dictionnary.associatedModule;

    selectContainer.setAttribute('class', 'col-md-9 form-inline align-items-start felement');
    selectContainer.setAttribute('data-fieldtype', 'select');

    select.setAttribute('class', 'custom-select');
    select.setAttribute('name', 'evaluation_module_' + counterEvaluations);
    select.setAttribute('id', 'id_evaluation_module_' + counterEvaluations);

    modules.forEach(module => {
        select.appendChild(buildOption(module));
    });

    labelContainer.appendChild(label);
    selectContainer.appendChild(select);
    fieldContainer.appendChild(labelContainer);
    fieldContainer.appendChild(selectContainer);

    return fieldContainer;
}

/**
 * @param {object} module
 */
function buildOption(module) {
    let option = document.createElement('option');
    option.setAttribute('value', module.id);
    option.innerHTML = module.title;
    return option;
}

/**
 * @param {string} target
 * @param {string} evaluationIndex
 */
function buildTinyMCE(target, evaluationIndex) {
    let inputContainerDiv = document.createElement("textarea");
    inputContainerDiv.setAttribute('class', 'custom-editor');
    inputContainerDiv.setAttribute('id', 'id_' + target + '_' + evaluationIndex);
    inputContainerDiv.setAttribute('name', target + '_' + evaluationIndex);
    return inputContainerDiv;
}

/**
 *
 */
function buildEvaluationButton() {
    let rowEvaluationContainer = document.createElement("div");
    let buttonContainer = document.createElement("div");
    let buttonElement = document.createElement("button");
    let icon = document.createElement("i");

    rowEvaluationContainer.setAttribute('id', 'fitem_id_remove_evaluation_' + counterEvaluations);

    buttonContainer.setAttribute('data-fieldtype', 'button');

    buttonElement.setAttribute('class', 'btn ml-0');
    buttonElement.setAttribute('name', 'remove_evaluation_' + counterEvaluations);
    buttonElement.setAttribute('id', 'id_remove_evaluation_' + counterEvaluations);
    buttonElement.setAttribute('type', 'button');

    icon.setAttribute('class', 'remove-button-js fa fa-minus-circle fa-2x');

    buttonElement.appendChild(icon);
    buttonContainer.appendChild(buttonElement);
    rowEvaluationContainer.appendChild(buttonContainer);

    return rowEvaluationContainer;
}

/**
 *
 */
function disableEvaluationDeleteButton() {
    let evaluations = document.querySelectorAll('[id^="row_course_evaluation_container_"]');
    if (evaluations.length === 1) {
        let button = document.getElementById('id_remove_evaluation_0');
        button.hidden = true;
    } else {
        let buttons = document.querySelectorAll('[id^="id_remove_evaluation"]');
        buttons.forEach(button => {
            button.hidden = false;
        });
    }
}

/**
 *
 */
function updateRemoveEvaluationButton() {
    let buttons = document.querySelectorAll('[id^="fitem_id_remove_evaluation_"]');
    buttons.forEach(function(button, index) {
        button.setAttribute('id', 'fitem_id_remove_evaluation_' + index);
        let buttonElm = button.querySelector('[type="button"]');
        buttonElm.setAttribute('name', 'remove_evaluation_' + index);
        buttonElm.setAttribute('id', 'id_remove_evaluation_' + index);
    });
}

/**
 *
 */
function updateAddEvaluationButton() {
    let x = document.querySelectorAll('[id^="row_course_evaluation_container_"]');
    let addEvaluationButtonContainer = document.getElementById('evaluation-add-container');
    let addEvaluationButtonText = addEvaluationButtonContainer.querySelector('.add-text');
    addEvaluationButtonText.innerHTML = dictionnary.evaluation + (x.length + 1);
}

/**
 * @param {string} index
 */
function updateExistingEvaluations(index) {
    let evaluations = document.querySelectorAll('[id^="row_course_evaluation_container_"]');
    evaluations.forEach(evaluation => {
        if (parseInt(evaluation.id.substring(evaluation.id.lastIndexOf('_') + 1)) > parseInt(index)) {
            let currentIndex = parseInt(evaluation.id.substring(evaluation.id.lastIndexOf('_') + 1));
            evaluation.setAttribute('id', 'row_course_evaluation_container_' + (currentIndex - 1));
            let header = evaluation.querySelector('.accordion-header');
            header.setAttribute('id', 'course_evaluation_header_' + (currentIndex - 1));
            header.firstElementChild.setAttribute('href', '#collapse_evaluation_' + (currentIndex - 1));
            header.firstElementChild.setAttribute('aria-controls', 'collapse_evaluation_' + (currentIndex - 1));
            header.firstElementChild.innerHTML = 'Evaluation ' + (currentIndex);


            let collapsible = evaluation.querySelector('.collapse');
            collapsible.setAttribute('id', 'collapse_evaluation_' + (currentIndex - 1));
            collapsible.firstElementChild.setAttribute('id', 'course_evaluation_content_' + (currentIndex - 1));
            collapsible.firstElementChild.children[0].setAttribute('name', 'evaluation_' + (currentIndex - 1) + '_id_value');
            updateExistingWeight(evaluation, currentIndex);
            updateExistingEvaluationObjectives(evaluation, currentIndex);
            updateExistingModules(evaluation, currentIndex);
            updateEditorAndLabel(evaluation, currentIndex, 'title', 'superscript subscript | undo redo');
            updateEditorAndLabel(evaluation, currentIndex, 'description',
                ['formatselect bold italic | numlist bullist indent outdent | link unlink | emoticons image ',
                    // eslint-disable-next-line max-len
                    'underline strikethrough superscript subscript | alignleft aligncenter alignright | charmap table removeformat | undo redo ']);

        }
    });
}

/**
 * @param {object} evaluation
 * @param {string} currentIndex
 */
function updateExistingModules(evaluation, currentIndex) {
    let moduleContainer = evaluation.querySelector('[id^="fitem_id_evaluation_module_"]');
    moduleContainer.setAttribute('id', 'fitem_id_evaluation_module_' + (currentIndex - 1));
    let labelTitle = moduleContainer.querySelector('[for^="id_evaluation_module_"]');
    labelTitle.setAttribute('for', 'id_evaluation_module_' + (currentIndex - 1));
    let select = moduleContainer.querySelector('.custom-select');
    select.name = "evaluation_module_" + (currentIndex - 1);
    select.id = "id_evaluation_module_" + (currentIndex - 1);
}

/**
 * @param {object} evaluation
 * @param {string} currentIndex
 */
function updateExistingEvaluationObjectives(evaluation, currentIndex) {
    let objContainer = evaluation.querySelector('[id^="fitem_id_evaluation_learning_objectives_"]');
    objContainer.setAttribute('id', 'fitem_id_evaluation_learning_objectives_' + (currentIndex - 1));
    objContainer.children[0].setAttribute('id', 'evaluation_learning_objectives_title_' + (currentIndex - 1));
    objContainer.children[0].children[0].setAttribute('for', 'evaluation_learning_objectives_title_' + (currentIndex - 1));
    let checkboxes = objContainer.querySelectorAll('[class^="form-group row  fitem  "]');
    checkboxes.forEach(checkbox => {
        let innerContainer = checkbox.querySelector('[class^="form-check d-flex"]');
        let currentObjId = innerContainer.children[0].name
            .substring(innerContainer.children[0].name.indexOf('[') + 1, innerContainer.children[0].name.indexOf(']'));
        innerContainer.children[0].name = 'evaluation_learning_objectives_' + (currentIndex - 1) + '[' + currentObjId + ']';
        innerContainer.children[1].name = 'evaluation_learning_objectives_' + (currentIndex - 1) + '[' + currentObjId + ']';
        innerContainer.children[1].id = 'id_evaluation_learning_objectives_' + (currentIndex - 1) + '_' + currentObjId;
        innerContainer.children[2].setAttribute('for', 'id_evaluation_learning_objectives_' + (currentIndex - 1));
    });
}

/**
 * @param {object} evaluation
 * @param {string} currentIndex
 */
function updateExistingWeight(evaluation, currentIndex) {
    let weightContainer = evaluation.querySelector('[id^="fitem_id_evaluation_weight_"]');
    weightContainer.setAttribute('id', 'fitem_id_evaluation_weight_' + (currentIndex - 1));
    let labelTitle = weightContainer.querySelector('[for^="id_evaluation_weight_"]');
    labelTitle.setAttribute('for', 'id_evaluation_weight_' + (currentIndex - 1));
    let input = evaluation.querySelector('[id^="id_evaluation_weight_"]');
    input.setAttribute('id', 'id_evaluation_weight_' + (currentIndex - 1));
    input.setAttribute('name', 'evaluation_weight_' + (currentIndex - 1));
}

/**
 * @param {object} evaluation
 * @param {string} currentIndex
 * @param {string} element
 * @param {string} toolbarOptions
 */
function updateEditorAndLabel(evaluation, currentIndex, element, toolbarOptions) {
    let titleContainer = evaluation.querySelector('[id^="fitem_id_evaluation_' + element + '_"]');
    titleContainer.setAttribute('id', 'fitem_id_evaluation_' + element + '_' + (currentIndex - 1));
    let labelTitle = titleContainer.querySelector('[for^="id_evaluation_' + element + '_"]');
    labelTitle.setAttribute('for', 'id_evaluation_' + element + '_' + (currentIndex - 1));
    removeTinyMce('id_evaluation_' + element + '_' + (currentIndex));
    let editorTitle = titleContainer.querySelector('[id^="id_evaluation_' + element + '_"]');
    editorTitle.setAttribute('id', 'id_evaluation_' + element + '_' + (currentIndex - 1));
    editorTitle.setAttribute('name', 'evaluation_' + element + '_' + (currentIndex - 1));
    initTinyMce('id_evaluation_' + element + '_' + (currentIndex - 1), toolbarOptions);
}

/**
 * @param {object} evaluation
 * @param {int} index
 */
function fillFormCommonPart(evaluation, index) {
    let weight = document.getElementById('id_evaluation_weight_' + index);
    weight.value = evaluation.weight;

    evaluation.learningobjectiveids.forEach(element=> {

        let learningObjectiveElement = document
            .getElementById('id_evaluation_learning_objectives_' + index + '_' + element.audehlearningobjectiveid);
        if(learningObjectiveElement) {
            learningObjectiveElement.checked = true;
        }

     });

    let module = document.getElementById('id_evaluation_module_' + index);
    module.value = evaluation.audehsectionid;
}

/**
 * @param {int} index
 */
function getTooltips(index) {
    const titleContainer = document.getElementById('fitem_id_evaluation_title_0');
    const descriptionContainer = document.getElementById('fitem_id_evaluation_description_0');
    const weightContainer = document.getElementById('fitem_id_evaluation_weight_0');
    const evaluationObjContainer = document.getElementById('fitem_id_evaluation_learning_objectives_0');
    const evaluationModuleContainer = document.getElementById('fitem_id_evaluation_module_0');

    const titleToolTip = titleContainer.firstElementChild.children[1];
    const descriptionToolTip = descriptionContainer.firstElementChild.children[1];
    const weightToolTip = weightContainer.firstElementChild.children[1];
    const evaluationObjToolTip = evaluationObjContainer.firstElementChild.children[1];
    const evaluationModuleToolTip = evaluationModuleContainer.firstElementChild.children[1];

    let titleToFillContainer = document.getElementById('fitem_id_evaluation_title_' + index);
    titleToFillContainer.firstElementChild.appendChild(titleToolTip.cloneNode(true));

    let descriptionToFillContainer = document.getElementById('fitem_id_evaluation_description_' + index);
    descriptionToFillContainer.firstElementChild.appendChild(descriptionToolTip.cloneNode(true));

    let weightToFillContainer = document.getElementById('fitem_id_evaluation_weight_' + index);
    weightToFillContainer.firstElementChild.appendChild(weightToolTip.cloneNode(true));

    let evaluationObjToFillContainer = document.getElementById('fitem_id_evaluation_learning_objectives_' + index);
    evaluationObjToFillContainer.firstElementChild.appendChild(evaluationObjToolTip.cloneNode(true));

    let evaluationModuleToFillContainer = document.getElementById('fitem_id_evaluation_module_' + index);
    evaluationModuleToFillContainer.firstElementChild.appendChild(evaluationModuleToolTip.cloneNode(true));
}

/**
 *
 */
function placeLearningObjectivesTooltip() {
    let initialContainer = document.getElementById('fitem_id_evaluation_learning_objectives_0');
    let rows = initialContainer.querySelectorAll('.form-group');
    let tooltipContainer = rows[rows.length - 1].children[1].firstElementChild.getElementsByTagName('div');
    if (tooltipContainer[0]) {
        let headerContainer = document.getElementById('evaluation_learning_objectives_title_0');
        headerContainer.appendChild(tooltipContainer[0]);
    }
}

/**
 * @param {int} id
 * @param {int} counter
 */
function buildIdHiddenInput(id, counter) {
    let input = document.createElement("input");
    input.setAttribute('name', 'evaluation_' + (counter) + '_id_value');
    input.setAttribute('value', id);
    input.hidden = true;

    let element = document.getElementById('fitem_id_evaluation_title_' + (counter));
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

/**
 *
 */
export function initEvaluations() {
    validateEvaluationForm();
    formatInitialObjList();

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
            if (element && element.id.includes('evaluation') && element.hidden === false) {
                if (element.id.includes('remove')) {
                    let id = element.id.substring(element.id.lastIndexOf('_') + 1);
                    removeEvaluation(id);
                    event.stopImmediatePropagation();
                }
            } else {
                return;
            }
        }
    });
    let addButton = document.querySelector('#id_add_evaluation');
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
            if (element && element.id.includes('evaluation') && element.hidden === false) {
                addEvaluation(true);
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
    disableEvaluationDeleteButton();
    prepareAccordions('row_course_evaluation_container_');
    placeLearningObjectivesTooltip();
    formatInitialObjList();
}

/**
 * @param {array} evaluations
 * @param {array} moduleslist
 * @param {array} objList
 */
export function fillFormEvaluations(evaluations, moduleslist, objList) {
    modules = moduleslist;
    modules.push(new Module('Aucun', 0));
    handleObjList(objList);
    evaluations.forEach(function(evaluation, i) {
        if (i === 0) {
            waitWithInterval('id_evaluation_title_0editable', 0, evaluation.title);
            waitWithInterval('id_evaluation_title_0', 1, evaluation.title);

            waitWithInterval('id_evaluation_description_0editable', 0, evaluation.description);
            waitWithInterval('id_evaluation_description_0', 1, evaluation.description);

        } else {
            if (document.getElementById('id_evaluation_title_' + i) === null) {
                addEvaluation(false);
            }
            let title = document.getElementById('id_evaluation_title_' + i);
            title.value = evaluation.title;

            let description = document.getElementById('id_evaluation_description_' + i);
            description.value = evaluation.description;

        }
        fillFormCommonPart(evaluation, i);
        buildIdHiddenInput(evaluation.id, i);
    });
    prepareActionButton([
        {type: 0, id: 'evaluation_0'},
        {type: 1, id: 'evaluation'}]);
    disableEvaluationDeleteButton();
    prepareAccordions('row_course_evaluation_container_');
}

/**
 * @param {array} objList
 */
function handleObjList(objList) {
    learningObjectives = [];
    objList.forEach(function(teachingObj) {
        learningObjectives.push(teachingObj.learningobjectives);
    });
}

/**
 * @param {string} evalName
 */
export function initPhpEvaluationValidation(evalName) {
    validateEvaluationDataForm();
    handleEmbed('fitem_id_evaluation_introduction_embed', 'fitem_id_evaluation_introduction');
    initRedactTools(2, [evalName]);
}