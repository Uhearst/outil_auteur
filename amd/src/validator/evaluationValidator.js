import {validateIsEmbed} from "./validatorUtils";
import {addNotification} from "../notificationHelper";
/**
 *
 */
export function validateEvaluationForm() {
    document.getElementById('udeh-form').addEventListener("submit", function(e) {
        e.preventDefault();

        let validObj = validateObjectives();
        let validModules = validateModules();
        if (!validObj || !validModules) {
            return;
        }
        window.$('#udeh-form').submit();
    });
}

/**
 * @param {Integer} length
 */
export function validateEvaluationDataForm(length = null) {
    document.getElementById('udeh-form').addEventListener("submit", function(e) {
        e.preventDefault();

        if (length !== null) {
            let isGood = true;
            for (let i = 0; i < length; i++) {
                validateIsEmbed(i);
            }
            if (!isGood) {
                return;
            }
        } else {
            validateIsEmbed();
        }

        window.$('#udeh-form').submit();
    });
}

/**
 *
 */
function validateObjectives() {
    let isValid = true;
    let evaluations = document.querySelectorAll('[id^="row_course_evaluation_container_"]');

    let notCheckedEvaluationsId = [];
    evaluations.forEach(function(evaluation, index) {
        let hasChecked = false;
        let learningObjContainer = document.getElementById('fitem_id_evaluation_learning_objectives_' + index);
        let checkboxes = learningObjContainer.querySelectorAll('[id^="id_evaluation_learning_objectives_"]');
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                hasChecked = true;
            }
        });
        if (!hasChecked) {
            notCheckedEvaluationsId.push(index);
        }
    });

    if (notCheckedEvaluationsId.length > 0) {
        notCheckedEvaluationsId.forEach(notCheckedEvaluationId => {
            let errorMsg = document.getElementById('error_msg_obj_' + notCheckedEvaluationId);
            if (errorMsg === null) {
                let span = document.createElement('span');
                span.style.color = 'red';
                span.id = 'error_msg_obj_' + notCheckedEvaluationId;
                span.style.margin = '0 0 0 1rem';
                span.innerHTML = '* Un objectif d\'apprentissage est requis';
                document.getElementById('course_evaluation_content_' + notCheckedEvaluationId).insertBefore(
                    span, document.getElementById('fitem_id_evaluation_learning_objectives_' + notCheckedEvaluationId));
            }
        });
        isValid = false;
    }
    if (!isValid) {
        addNotification('Vous devez ajouter au moins un objectif d\'apprentissage par évaluation', 2);
    } else {
        let errorMsgs = document.querySelectorAll('[id^="error_msg_obj_"]');
        errorMsgs.forEach(errorMsg => {
            errorMsg.remove();
        });
    }
    return isValid;
}

/**
 *
 */
function validateModules() {
    let isValid = true;
    let selectedValues = [];
    let moduleContainers = document.querySelectorAll('[id^="fitem_id_evaluation_module_"]');

    moduleContainers.forEach(function(moduleContainer) {
        let select = moduleContainer.querySelector('.custom-select');
        selectedValues.push((select.value));
    });

    selectedValues.forEach(function(selectedValue, index) {
        let results = selectedValues.filter(val => val === selectedValue);
        if (results.length > 1 && selectedValue !== "0") {
            let errorMsg = document.getElementById('error_msg_module_' + index);
            if (errorMsg === null) {
                let span = document.createElement('span');
                span.style.color = 'red';
                span.id = 'error_msg_module_' + index;
                span.style.margin = '0 0 0.5rem 1rem';
                span.innerHTML = '* Le module doit être unique à chaque évaluation';
                document.getElementById('course_evaluation_content_' + index).insertBefore(
                    span, document.getElementById('fitem_id_evaluation_module_' + index));
            }
            isValid = false;
        }
    });
    if(!isValid) {
        addNotification('Le module doit être unique à chaque évaluation', 2);
    }
    else {
        let errorMsgs = document.querySelectorAll('[id^="error_msg_module_"]');
        errorMsgs.forEach(errorMsg => {
            errorMsg.remove();
        });
    }
    return isValid;
}

