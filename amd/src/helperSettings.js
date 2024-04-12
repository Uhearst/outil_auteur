import {get_string as getString} from 'core/str';

let unitCounter = 0;

/**
 *
 */
export function init() {
    fetchData();
    initRequest();
    initAccordions();
    initActions();
}

/**
 *
 */
function fetchData() {
    let unitsconfig = [];

    window.$.ajax(
        {
            type: "GET",
            url: "../course/format/udehauthoring/handlers/ajax_unit_fetch_handler.php",
            success: function(response) {
                let parsedResponse = JSON.parse(response);
                unitsconfig = parsedResponse.data;
                if (unitsconfig !== [] && unitsconfig.length > 1) {
                    unitsconfig.forEach(function(unitconfig, index) {
                        if (index > 0) {
                            addUnit(unitconfig.value);
                        }
                    });
                }
            },
            error: function() {
                window.console.log('failure');
            }
        });
}

/**
 *
 */
function initRequest() {
    document.getElementById('adminsettings').addEventListener("submit", function(e) {
        e.preventDefault();
        let unitContainer = document.getElementById('setting_units');
        let units = unitContainer.firstElementChild.querySelectorAll('[id^="admin-udeh_unit_"]');
        let unitArray = [];
        units.forEach(function(unit, index) {
            if (index > 0) {
                let val = unit.children[0].children[1].children[0].children[0].value;
                if (val) {
                    unitArray.push(val);
                }
            } else {
                let val = unit.children[1].children[0].children[0].value;
                if (val) {
                    unitArray.push(val);
                }
            }
        });
        window.$.ajax(
            {
                type: "POST",
                url: "../course/format/udehauthoring/handlers/ajax_unit_handler.php",
                data: {
                    units: unitArray,
                },
                success: async function(response) {
                    let parsedResponse = JSON.parse(response);
                    if (parsedResponse.success == "1") {
                        window.$('#adminsettings').submit();
                    } else {
                        await addIssueNotificationUnit();
                        window.$('#adminsettings').submit();
                    }
                },
                error: async function() {
                    await addIssueNotificationUnit();
                    window.$('#adminsettings').submit();
                }
            });
    });
}

/**
 *
 */
function initActions() {
    let addButton = document.querySelector('#id_add_unit');
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
            if (element && element.id.includes('unit') && element.hidden === false) {
                addUnit();
            } else {
                return;
            }
        }
    });

    let unitContnainer = document.getElementById('setting_units');
    unitContnainer.firstElementChild.addEventListener('click', event => {
        const isButton = event.target.nodeName === 'BUTTON'
            || (event.target.nodeName === 'I'
                && event.target.parentNode && event.target.parentNode.nodeName === 'BUTTON');
        if (!isButton) {
            return;
        } else {
            let element = null;
            if (event.target.nodeName === 'I') {
                element = event.target.parentNode;
            } else {
                element = event.target;
            }
            if (element && element.id.includes('unit') && element.hidden === false) {
                if (element.id.includes('remove')) {
                    let id = element.id.substring(element.id.lastIndexOf('_') + 1);
                    removeUnit(id);
                }
            } else {
                return;
            }
        }
    });
}

/**
 * @param {String} value
 */
function addUnit(value = null) {
    let x = buildUnit(value);
    let container = document.getElementById('setting_units');
    container.firstElementChild.appendChild(x);
}

/**
 * @param {String} value
 */
function buildUnit(value = null) {
    unitCounter = unitCounter + 1;
    let initialUnit = document.getElementById('admin-udeh_unit_0');
    let newElem = initialUnit.cloneNode(true);

    newElem.id = 'admin-udeh_unit_' + unitCounter;
    newElem.style.margin = '0 0 0 1rem';
    newElem.firstElementChild.firstElementChild.setAttribute('for', 'id_s__udeh_unit_' + unitCounter);
    newElem.firstElementChild.children[1].innerHTML = 'udeh_unit_' + unitCounter;

    newElem.children[1].removeAttribute('id');
    newElem.children[1].firstElementChild.removeAttribute('id');
    newElem.children[1].firstElementChild.firstElementChild.setAttribute('id', 'id_s__udeh_unit_' + unitCounter);
    newElem.children[1].firstElementChild.firstElementChild.setAttribute('name', 's__udeh_unit_' + unitCounter);
    if (value === null) {
        newElem.children[1].firstElementChild.firstElementChild.setAttribute('value', '');
        newElem.children[1].firstElementChild.firstElementChild.value = '';
    } else {
        newElem.children[1].firstElementChild.firstElementChild.setAttribute('value', value);
    }

    let contentContainer = document.createElement('div');
    contentContainer.setAttribute('class', 'col-11 row');
    let deleteContainer = document.createElement('div');
    deleteContainer.setAttribute('class', 'col-1');

    contentContainer.appendChild(newElem.children[0]);
    contentContainer.appendChild(newElem.children[0]);

    newElem.appendChild(contentContainer);

    let button = document.createElement('button');
    let icon = document.createElement("i");

    button.setAttribute('class', 'btn remove-button');
    button.setAttribute('name', 'remove_unit_' + unitCounter);
    button.setAttribute('id', 'id_remove_unit_' + unitCounter);
    button.setAttribute('type', 'button');

    icon.setAttribute('class', 'remove-button-js fa fa-minus-circle fa-2x');
    button.appendChild(icon);
    deleteContainer.appendChild(button);
    newElem.appendChild(deleteContainer);

    return newElem;
}

/**
 * @param {string} index
 */
function removeUnit(index) {
    let unit = document.getElementById('admin-udeh_unit_' + index);
    unit.remove();
    updateExistingUnits(index);
    unitCounter = unitCounter - 1;

}

/**
 * @param {string} index
 */
function updateExistingUnits(index) {
    let units = document.querySelectorAll('[id^="admin-udeh_unit_"]');
    units.forEach(unit => {
        if (unit.id.substring(unit.id.lastIndexOf('_') + 1) > index) {
            let currentIndex = unit.id.substring(unit.id.lastIndexOf('_') + 1);
            unit.id = "admin-udeh_unit_" + (currentIndex - 1);
            unit.firstElementChild.firstElementChild.firstElementChild.setAttribute('for', 'id_s__udeh_unit_' + (currentIndex - 1));
            unit.firstElementChild.firstElementChild.children[1].innerHTML = "udeh_unit_" + (currentIndex - 1);

            unit.firstElementChild.children[1].firstElementChild.firstElementChild
                .setAttribute('name', 's__udeh_unit_' + (currentIndex - 1));
            unit.firstElementChild.children[1].firstElementChild.firstElementChild
                .setAttribute('id', 'id_s__udeh_unit_' + (currentIndex - 1));

            unit.children[1].firstElementChild.setAttribute('id', 'id_remove_unit_' + (currentIndex - 1));
            unit.children[1].firstElementChild.setAttribute('name', 'remove_unit_' + (currentIndex - 1));
        }
    });
}


/**
 *
 */
function initAccordions() {
    let unitsArray = [];
    let instructionsArray = [];
    let courseInfoArray = [];
    let objectivesArray = [];
    let modulesArray = [];
    let evaluationsArray = [];
    let subquestionsArray = [];
    let explorationsArray = [];
    let resourcesArray = [];
    let toolsArray = [];
    let fieldset = document.getElementsByTagName('fieldset');
    let rows = fieldset[0].getElementsByClassName('form-item row');
    for (let i = 0; i < rows.length; i++) {
        if (rows[i].id.includes('udeh_unit')) {
            unitsArray.push(rows[i]);
        } else if (rows[i].id.includes('udeh_instructions')) {
            instructionsArray.push(rows[i]);
        } else if (rows[i].id.includes('udeh_course')) {
            courseInfoArray.push(rows[i]);
        } else if (rows[i].id.includes('udeh_teaching') || rows[i].id.includes('udeh_learning')) {
            objectivesArray.push(rows[i]);
        } else if (rows[i].id.includes('udeh_section')) {
            modulesArray.push(rows[i]);
        } else if (rows[i].id.includes('udeh_evaluation')) {
            evaluationsArray.push(rows[i]);
        } else if (rows[i].id.includes('udeh_subquestion')) {
            subquestionsArray.push(rows[i]);
        } else if (rows[i].id.includes('udeh_exploration')) {
            explorationsArray.push(rows[i]);
        } else if (rows[i].id.includes('udeh_resource')) {
            resourcesArray.push(rows[i]);
        } else if (rows[i].id.includes('udeh_tool')) {
            toolsArray.push(rows[i]);
        }
    }

    let unitsAccordion = buildAccordion(
        'setting_units',
        'Unités d\'enseignements',
        unitsArray);
    let instructionsAccordion = buildAccordion(
        'setting_instructions',
        'Instructions de page',
        instructionsArray);
    let infoAccordion = buildAccordion(
        'setting_course_informations',
        'Informations générales du cours',
        courseInfoArray);
    let objectivesAccordion = buildAccordion(
        'setting_objectives',
        'Objectifs',
        objectivesArray);
    let sectionAccordion = buildAccordion(
        'setting_sections',
        'Modules',
        modulesArray);
    let evaluationAccordion = buildAccordion(
        'setting_evaluations',
        'Evaluations',
        evaluationsArray);
    let subquestionAccordion = buildAccordion(
        'setting_subquestions',
        'Trames',
        subquestionsArray);
    let explorationAccordion = buildAccordion(
        'setting_explorations',
        'Explorations',
        explorationsArray);
    let resourceAccordion = buildAccordion(
        'setting_resources',
        'Ressources',
        resourcesArray);
    let toolAccordion = buildAccordion(
        'setting_tools',
        'Tools',
        toolsArray);

    fieldset[0].appendChild(unitsAccordion);
    fieldset[0].appendChild(instructionsAccordion);
    fieldset[0].appendChild(infoAccordion);
    fieldset[0].appendChild(objectivesAccordion);
    fieldset[0].appendChild(sectionAccordion);
    fieldset[0].appendChild(evaluationAccordion);
    fieldset[0].appendChild(subquestionAccordion);
    fieldset[0].appendChild(explorationAccordion);
    fieldset[0].appendChild(resourceAccordion);
    fieldset[0].appendChild(toolAccordion);

    let buttonContainer = document.createElement('div');
    buttonContainer.setAttribute('data-fieldtype', 'button');
    let button = document.createElement('button');
    let icon = document.createElement("i");
    icon.setAttribute('class', 'add-button fa fa-plus-circle fa-2x');
    button.setAttribute('class', 'btn ml-0 unit-btn');
    button.setAttribute('name', 'add_unit');
    button.setAttribute('type', 'button');
    button.setAttribute('id', 'id_' + 'add_unit');

    let unitsContainer = document.getElementById('setting_units');
    button.appendChild(icon);
    buttonContainer.appendChild(button);
    unitsContainer.appendChild(buttonContainer);
}


/**
 * @param {string} toggableId
 * @param {string} headerLabel
 * @param {array} inputs
 */
function buildAccordion(toggableId, headerLabel, inputs) {
    let accordionContainer = document.createElement('div');
    let headerContainer = document.createElement('div');
    let header = document.createElement('a');
    let contentContainer = document.createElement('div');
    let content = document.createElement('div');

    header.setAttribute('data-toggle', 'collapse');
    header.setAttribute('href', '#' + toggableId);
    header.setAttribute('role', 'button');
    header.setAttribute('aria-expanded', 'false');
    header.setAttribute('class', 'collapsed');
    header.setAttribute('aria-controls', toggableId);
    header.innerHTML = headerLabel;

    contentContainer.setAttribute('class', 'collapse mb-3');
    contentContainer.setAttribute('id', toggableId);

    content.setAttribute('class', 'card card-body p-3');
    inputs.forEach(input => {
        content.appendChild(input);
    });

    accordionContainer.setAttribute('class', 'setting_container');
    headerContainer.setAttribute('class', 'setting-header');
    headerContainer.appendChild(header);
    contentContainer.appendChild(content);
    accordionContainer.appendChild(headerContainer);
    accordionContainer.appendChild(contentContainer);
    return accordionContainer;
}

/**
 *
 */
async function addIssueNotificationUnit() {
    let alertContainer = document.createElement('div');

    let notificationContainer = document.getElementById('user-notifications');

    alertContainer.setAttribute('class', 'alert alert-danger alert-block fade in ');
    alertContainer.setAttribute('role', 'alert');
    alertContainer.innerHTML = await getString('notificationerrorunit', 'format_udehauthoring');
    notificationContainer.appendChild(alertContainer);
    window.$('html, body').animate({scrollTop: 0}, 'fast');
}