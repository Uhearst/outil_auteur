import {
    buildHiddenInput,
    handleEmbed,
    initEditor,
    prepareActionButton,
    removeTinyEditor, setDeleteButton,
    setEditorAfterCloning,
    updateEditorAndLabel,
} from "./utils";
import {validateEvaluationDataForm, validateEvaluationForm} from "./validator/evaluationValidator";
import {initRedactTools} from "./toolHelper";
import {get_string as getString} from 'core/str';
import {handleAccordions, handleNewAccordion, initAccordionsAfterFillingForm} from "./accordionHandler";

const editorFields = ['title', 'description'];
let counterEvaluations = 0;
let learningObjectives = [];

/**
 * @param {boolean} fromBtnClick
 */
async function addEvaluation(fromBtnClick) {
    counterEvaluations += 1;
    let evaluationParent = document.querySelector('#row_course_evaluation_container_0');
    let evaluation = evaluationParent.cloneNode(true);
    evaluation.setAttribute('id', 'row_course_evaluation_container_' + counterEvaluations);

    // Header
    evaluation.querySelector('#course_evaluation_objectives_header_0')
        .setAttribute('id', 'course_evaluation_objectives_header_' + counterEvaluations);
    evaluation.querySelector('[href="#collapse_evaluation_0"]').innerText =
        await getString('evaluation', 'format_udehauthoring') + ' ' + (counterEvaluations + 1);
    evaluation.querySelector('[href="#collapse_evaluation_0"]')
        .setAttribute('aria-controls', 'collapse_evaluation_' + counterEvaluations);
    evaluation.querySelector('[href="#collapse_evaluation_0"]')
        .setAttribute('href', '#collapse_evaluation_' + counterEvaluations);

    evaluation.querySelector('#collapse_evaluation_0')
        .setAttribute('id', 'collapse_evaluation_' + counterEvaluations);

    evaluation.querySelector('#course_evaluation_content_0')
        .setAttribute('id', 'course_evaluation_content_' + counterEvaluations);

    if (evaluation.querySelector('[name="evaluation_0_id_value"]')) {
        evaluation.querySelector('[name="evaluation_0_id_value"]').setAttribute('value', '');
        evaluation.querySelector('[name="evaluation_0_id_value"]')
            .setAttribute('name', 'evaluation_' + counterEvaluations + '_id_value');
    }

    // Content
    editorFields.forEach(editorField => {
        setEditorAfterCloning(evaluation, editorField, 'evaluation', counterEvaluations, null, fromBtnClick);
    });

    // Delete button
    setDeleteButton(evaluation, 'remove_evaluation', counterEvaluations);

    // Weight
    let weight = evaluation.querySelector('#fitem_id_evaluation_weight_0');
    weight.setAttribute('id', 'fitem_id_evaluation_weight_' + counterEvaluations);
    weight.querySelector('[for="id_evaluation_weight_0"]')
        .setAttribute('for', 'id_evaluation_weight_' + counterEvaluations);
    weight.querySelector('#id_evaluation_weight_0')
        .setAttribute('name', 'evaluation_weight_' + counterEvaluations);
    weight.querySelector('#id_evaluation_weight_0').value = '';
    weight.querySelector('#id_evaluation_weight_0')
        .setAttribute('id', 'id_evaluation_weight_' + counterEvaluations);
    weight.querySelector('#id_error_evaluation_weight_0')
        .setAttribute('id', 'id_error_evaluation_weight_' + counterEvaluations);

    // Learning objective and module
    ['learning_objectives', 'module'].forEach(elm => {
        let container = evaluation.querySelector('#fitem_id_evaluation_' + elm + '_0');
        container.setAttribute('name', 'evaluation_' + elm + '_' + counterEvaluations);
        container.setAttribute('id', 'fitem_id_evaluation_' + elm + '_' + counterEvaluations);
        container.querySelector('#evaluation_' + elm + '_title_0')
            .setAttribute('id', 'evaluation_' + elm + '_title_' + counterEvaluations);
        container.querySelector('[for="fitem_id_evaluation_' + elm + '_0"]')
            .setAttribute('for', 'fitem_id_evaluation_' + elm + '_' + counterEvaluations);

        let checkboxes = container.querySelectorAll('.form-group.row');
        checkboxes.forEach(objective => {
            let hiddenInput = objective.querySelector('input[type="hidden"][name^="evaluation_' + elm + '_0"]');
            hiddenInput.setAttribute(
                'name',
                hiddenInput.getAttribute('name').replace('0', counterEvaluations)
            );
            let input = objective.querySelector('input[type="checkbox"][name^="evaluation_' + elm + '_0"]');
            input.setAttribute('name', input.getAttribute('name').replace('0', counterEvaluations));
            input.setAttribute('id', input.getAttribute('id').replace('0', counterEvaluations));
            input.checked = false;

            let label = objective.querySelector('[for^="id_evaluation_' + elm + '_0_"]');
            label.setAttribute('for', label.getAttribute('for').replace('0', counterEvaluations));
        });
    });

    let container = document.querySelector('#displayable-form-evaluations-container');
    container.insertBefore(evaluation, document.querySelector('#evaluation-add-container'));

    disableEvaluationDeleteButton();
    await updateAddEvaluationButton();

    if (fromBtnClick) {
        handleNewAccordion('#course_evaluation_objectives_header_', counterEvaluations);
        await handleEditor(counterEvaluations);
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
                    elementId: 'id_evaluation_' + editorField + '_' + counter,
                    text: ''
                },
                success: function(response) {
                    let draftIdElm = document.querySelector('#row_course_evaluation_container_' + counter)
                        .querySelector('[name = "evaluation_' + editorField + '_' + counter + '[itemid]"]');
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
async function removeEvaluation(index) {
    let evaluation = document.getElementById('row_course_evaluation_container_' + index);
    for (const editorField of editorFields) {
        await removeTinyEditor('id_section_' + editorField + '_' + index);
    }
    evaluation.remove();
    await updateExistingEvaluations(index);
    await updateAddEvaluationButton();
    disableEvaluationDeleteButton();
    updateRemoveEvaluationButton();
    counterEvaluations = counterEvaluations - 1;
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
async function updateAddEvaluationButton() {
    let x = document.querySelectorAll('[id^="row_course_evaluation_container_"]');
    let addEvaluationButtonContainer = document.getElementById('evaluation-add-container');
    let addEvaluationButtonText = addEvaluationButtonContainer.querySelector('.add-text');
    addEvaluationButtonText.innerHTML = await getString('evaluation', 'format_udehauthoring') + ' ' + (x.length + 1);
}

/**
 * @param {string} index
 */
async function updateExistingEvaluations(index) {
    let evaluations = document.querySelectorAll('[id^="row_course_evaluation_container_"]');
    for (const evaluation of evaluations) {
        if (parseInt(evaluation.id.substring(evaluation.id.lastIndexOf('_') + 1)) > parseInt(index)) {
            let currentIndex = parseInt(evaluation.id.substring(evaluation.id.lastIndexOf('_') + 1));
            evaluation.setAttribute('id', 'row_course_evaluation_container_' + (currentIndex - 1));
            let header = evaluation.querySelector('.accordion-header');
            header.setAttribute('id', 'course_evaluation_header_' + (currentIndex - 1));
            header.firstElementChild.setAttribute('href', '#collapse_evaluation_' + (currentIndex - 1));
            header.firstElementChild.setAttribute('aria-controls', 'collapse_evaluation_' + (currentIndex - 1));
            header.firstElementChild.innerHTML = await getString('evaluation', 'format_udehauthoring') + ' ' + (currentIndex);


            let collapsible = evaluation.querySelector('.collapse');
            collapsible.setAttribute('id', 'collapse_evaluation_' + (currentIndex - 1));
            collapsible.firstElementChild.setAttribute('id', 'course_evaluation_content_' + (currentIndex - 1));
            collapsible.firstElementChild.children[0].setAttribute('name', 'evaluation_' + (currentIndex - 1) + '_id_value');
            updateExistingWeight(evaluation, currentIndex);
            updateExistingEvaluationObjectives(evaluation, currentIndex);
            updateExistingModules(evaluation, currentIndex);
            await updateEditorAndLabel(evaluation, currentIndex, 'title', 'evaluation');
            await updateEditorAndLabel(evaluation, currentIndex, 'description', 'evaluation');
            await handleEditor((currentIndex - 1));
        }
    }
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
 * @param {int} index
 */
function fillFormCommonPart(evaluation, index) {
    let weight = document.getElementById('id_evaluation_weight_' + index);
    weight.value = evaluation.weight;

    evaluation.learningobjectiveids.forEach(element => {

        let learningObjectiveElement = document
            .getElementById('id_evaluation_learning_objectives_' + index + '_' + element.audehlearningobjectiveid);
        if (learningObjectiveElement) {
            learningObjectiveElement.checked = true;
        }

    });

    if (evaluation.audehsectionids) {
        for (const prop in evaluation.audehsectionids) {
            if (document.getElementById('id_evaluation_module_' + index + '_' + prop)) {
                document.getElementById('id_evaluation_module_' + index + '_' + prop).checked = true;
            }
        }
    }
}

/**
 *
 */
function placeLearningObjectivesTooltip() {
    let initialContainer = document.getElementById('fitem_id_evaluation_learning_objectives_0');
    let rows = initialContainer.querySelectorAll('.form-group');
    if (typeof rows[rows.length - 1] !== 'undefined') {
        let tooltipContainer = rows[rows.length - 1].children[1].firstElementChild.getElementsByTagName('div');

        if (tooltipContainer[0]) {
            let headerContainer = document.getElementById('evaluation_learning_objectives_title_0');
            headerContainer.appendChild(tooltipContainer[0]);
        }
    }
}

/**
 *
 */
export function initEvaluations() {
    validateEvaluationForm();
    formatInitialObjList();

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
            if (element && element.id.includes('evaluation') && element.hidden === false) {
                if (element.id.includes('remove')) {
                    let id = element.id.substring(element.id.lastIndexOf('_') + 1);
                    await removeEvaluation(id);
                    document.getElementById('udeh-form').dataset.changed = 'true';
                    event.stopImmediatePropagation();
                }
            } else {
                return;
            }
        }
    });
    let addButton = document.querySelector('#id_add_evaluation');
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
            if (element && element.id.includes('evaluation') && element.hidden === false) {
                await addEvaluation(true);
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
    disableEvaluationDeleteButton();
    // prepareAccordions('row_course_evaluation_container_');
    placeLearningObjectivesTooltip();
    formatInitialObjList();
}

/**
 * @param {array} evaluations
 * @param {array} objList
 */
export async function fillFormEvaluations(evaluations, objList) {
    handleObjList(objList);
    for (const evaluation of evaluations) {
        const i = evaluations.indexOf(evaluation);
        if (i === 0) {
            buildHiddenInput(evaluation.id, i, 'fitem_id_evaluation_title_' + (i), 'evaluation_');
            document.querySelector('[name = "evaluation_0_id_value"]').setAttribute('value', evaluation.id);
        } else {
            if (document.getElementById('id_evaluation_title_' + i) === null) {
                await addEvaluation(false);
            }
            editorFields.forEach(editorField => {
                document.querySelector('#id_evaluation_' + editorField + '_' + i).value = evaluation[editorField];
            });
            document.querySelector('[name = "evaluation_' + i + '_id_value"]')
                .setAttribute('value', evaluation.id);
            await handleEditor(i);
        }
        fillFormCommonPart(evaluation, i);
    }
    prepareActionButton([
        {type: 0, id: 'evaluation_0'},
        {type: 1, id: 'evaluation'}]);
    disableEvaluationDeleteButton();
    styleCheckbox();
    initAccordionsAfterFillingForm();
    handleAccordions();
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

const styleCheckbox = () => {
    let checkboxes = document.querySelectorAll('[class~="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.querySelector('label').classList.add(['pr-5'], ['w-100']);
    });
};

/**
 * @param {string} evalName
 */
export function initPhpEvaluationValidation(evalName) {
    validateEvaluationDataForm();
    handleEmbed('fitem_id_evaluation_introduction_embed', 'fitem_id_evaluation_introduction');
    initRedactTools(2, [evalName]);
}