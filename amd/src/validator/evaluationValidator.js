import {validateIsEmbed} from "./validatorUtils";
import {addNotification} from "../notificationHelper";
import {get_string as getString} from 'core/str';
/**
 *
 */
export function validateEvaluationForm() {
    document.getElementById('udeh-form').addEventListener("submit", async function(e) {
        if (window.location.href.includes('displayable-form-evaluations-container')) {
            e.preventDefault();
            let isValidObjectives = await validateObjectives();
            let isValidModules = await validateModules();
            if (isValidObjectives && isValidModules) {
                window.$('#udeh-form').submit();
            }
        }
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
async function validateObjectives() {
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
        for (const notCheckedEvaluationId of notCheckedEvaluationsId) {
            let errorMsg = document.getElementById('error_msg_obj_' + notCheckedEvaluationId);
            if (errorMsg === null) {
                let span = document.createElement('span');
                span.style.color = 'red';
                span.id = 'error_msg_obj_' + notCheckedEvaluationId;
                span.style.margin = '0 0 0 1rem';
                span.innerHTML = await getString('evaluationsaveerrorobjmsg', 'format_udehauthoring');
                document.getElementById('course_evaluation_content_' + notCheckedEvaluationId).insertBefore(
                    span, document.getElementById('fitem_id_evaluation_learning_objectives_' + notCheckedEvaluationId));
            }
        }
        isValid = false;
    }
    if (!isValid) {
        addNotification(await getString('notificationerroroneobjective', 'format_udehauthoring'), 2);
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
async function validateModules() {
    let isValid = true;
    let selectedValues = [];
    let moduleContainers = document.querySelectorAll('[id^="fitem_id_evaluation_module_"]');

    moduleContainers.forEach(function(moduleContainer) {
        let select = moduleContainer.querySelector('.form-check-input');
        if (select.checked) {
            selectedValues.push(parseInt(select.name.substring(select.name.indexOf('[') + 1, select.name.indexOf(']'))));
        }
    });
    for (const selectedValue of selectedValues) {
        const index = selectedValues.indexOf(selectedValue);
        let results = selectedValues.filter(val => val === selectedValue);
        if (results.length > 1 && selectedValue !== 0) {
            let errorMsg = document.getElementById('error_msg_module_' + index);
            if (errorMsg === null) {
                let span = document.createElement('span');
                span.style.color = 'red';
                span.id = 'error_msg_module_' + index;
                span.style.margin = '0 0 0.5rem 1rem';
                span.innerHTML = await getString('notificationerroruniquemodule', 'format_udehauthoring');
                document.getElementById('course_evaluation_content_' + index).insertBefore(
                    span, document.getElementById('fitem_id_evaluation_module_' + index));
            }
            isValid = false;
        }
    }
    if (!isValid) {
        addNotification(await getString('notificationerroruniquemodule', 'format_udehauthoring'), 2);
    } else {
        let errorMsgs = document.querySelectorAll('[id^="error_msg_module_"]');
        errorMsgs.forEach(errorMsg => {
            errorMsg.remove();
        });
    }
    return isValid;
}

