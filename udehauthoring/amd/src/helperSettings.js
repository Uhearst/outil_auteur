/**
 *
 */
export function init() {
    let instructionsArray = [];
    let courseInfoArray = [];
    let objectivesArray = [];
    let modulesArray = [];
    let evaluationsArray = [];
    let subquestionsArray = [];
    let explorationsArray = [];
    let resourcesArray = [];
    let fieldset = document.getElementsByTagName('fieldset');
    let rows = fieldset[0].getElementsByClassName('form-item row');
    for (let i = 0; i < rows.length; i++) {
        if (rows[i].id.includes('udeh_instructions')) {
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
        }
    }


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
    let reesourceAccordion = buildAccordion(
        'setting_resources',
        'Ressources',
        resourcesArray);

    fieldset[0].appendChild(instructionsAccordion);
    fieldset[0].appendChild(infoAccordion);
    fieldset[0].appendChild(objectivesAccordion);
    fieldset[0].appendChild(sectionAccordion);
    fieldset[0].appendChild(evaluationAccordion);
    fieldset[0].appendChild(subquestionAccordion);
    fieldset[0].appendChild(explorationAccordion);
    fieldset[0].appendChild(reesourceAccordion);
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