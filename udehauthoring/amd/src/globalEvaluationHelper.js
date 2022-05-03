import {formatEditorAndFileManager, removeTags} from "./utils";

/**
 * @param {array} gloablevaluationplans
 */
export function initGlobalEvaluations(gloablevaluationplans) {
    const decodedParams = JSON.parse(gloablevaluationplans);
    let addButton = document.getElementById('id_add_global_evaluation');
    addButton.hidden = true;
    let accordions = document.querySelectorAll('[class*="single-accordion-container"]');
    accordions.forEach(function(accordion, i) {
        let headerLink = accordion.firstElementChild.firstElementChild;
        headerLink.href = '#collapseGlobalEvaluation' + (i);
        headerLink.setAttribute('aria-controls', 'collapseGlobalEvaluation' + (i));
        accordion.children[1].setAttribute('id', 'collapseGlobalEvaluation' + (i));
        if (i > 0) {
            accordion.children[1].setAttribute('class', 'collapse');
            headerLink.setAttribute('aria-expanded', 'false');
            headerLink.setAttribute('class', 'collapsed');
        }
        if (decodedParams[i].title) {
            let title = decodedParams[i].title;
            let headerContent = 'Evaluation Globale' + ' ' + (i + 1) + ' - ' + removeTags(title);
            headerLink.innerHTML = headerContent.length > 70 ? headerContent.substring(0, 70) + '...' : headerContent;
        } else {
            headerLink.innerHTML = 'Evaluation Globale' + ' ' + (i + 1);
        }
        let innerAccordion = accordion.querySelector('[id*="evaluation_preview_container_"]');
        innerAccordion.setAttribute('id', innerAccordion.getAttribute('id') + i);

        innerAccordion.firstElementChild
            .setAttribute('id', innerAccordion.firstElementChild.getAttribute('id') + i);
        innerAccordion.firstElementChild.firstElementChild
            .setAttribute('href', innerAccordion.firstElementChild.firstElementChild.getAttribute('href') + i);
        innerAccordion.firstElementChild.firstElementChild
            .setAttribute('aria-controls', innerAccordion.firstElementChild.firstElementChild.getAttribute('aria-controls') + i);

        innerAccordion.children[1].setAttribute('id', innerAccordion.children[1].getAttribute('id') + i);

        for (let j = 0; j < 3; j++) {
            innerAccordion.children[1].firstElementChild.children[j]
                .setAttribute('id', innerAccordion.children[1].firstElementChild.children[j].getAttribute('id') + i);
            innerAccordion.children[1].firstElementChild.children[j].firstElementChild
                .setAttribute('id', innerAccordion.children[1].firstElementChild.children[j].firstElementChild.getAttribute('id') + i);
            if (j !== 2) {
                innerAccordion.children[1].firstElementChild.children[j].children[1]
                    .setAttribute('id', innerAccordion.children[1].firstElementChild.children[j].children[1].getAttribute('id') + i);
            }
            let valToUse;
            switch (j) {
                case 0:
                    valToUse = decodedParams[i].description;
                    break;
                case 1:
                    valToUse = decodedParams[i].weight;
                    break;
                case 2:
                    valToUse = decodedParams[i].associatedobjtext;
                    break;
            }
            if (j !== 2) {
                innerAccordion.children[1].firstElementChild.children[j].children[1]
                    .innerHTML = removeTags(valToUse);
            } else {
                for (let counter = 0; counter < valToUse.length; counter++) {
                    let objContainer = document.createElement('p');
                    objContainer.setAttribute('id', 'evaluation_obj_content_' + i + '' + counter);
                    objContainer.innerHTML = valToUse[counter];
                    innerAccordion.children[1].firstElementChild.children[j].appendChild(objContainer);
                }
            }
        }

    });
    formatEditorAndFileManager();

}