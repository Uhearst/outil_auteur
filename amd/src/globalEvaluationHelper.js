import {formatEditorAndFileManager, handleEmbed, removeTags} from "./utils";
import {validateEvaluationDataForm} from "./validator/evaluationValidator";
import {initRedactTools} from "./toolHelper";
import {get_string as getString} from 'core/str';
import {handleAccordions, initAccordionsAfterFillingForm} from "./accordionHandler";

/**
 * @param {array} gloablevaluationplans
 */
export async function initGlobalEvaluations(gloablevaluationplans) {
    const decodedParams = JSON.parse(gloablevaluationplans);
    let addButton = document.getElementById('id_add_global_evaluation');
    addButton.hidden = true;
    let accordions = document.querySelectorAll('[class*="single-accordion-container"]');
    for (let i = 0; i < accordions.length; i++) {
        const accordion = accordions[i];
        let headerLink = accordion.firstElementChild.firstElementChild;
        headerLink.href = '#collapseGlobalEvaluation' + (i);
        headerLink.setAttribute('aria-controls', 'collapseGlobalEvaluation' + (i));
        accordion.children[1].setAttribute('id', 'collapseGlobalEvaluation' + (i));
        if (i > 0) {
            accordion.children[1].setAttribute('class', 'collapse');
            headerLink.setAttribute('aria-expanded', 'false');
            headerLink.setAttribute('class', 'collapsed');
        }
        let globalEvalTranslation = await getString('globalevaluation', 'format_udehauthoring');
        if (decodedParams[i].title) {
            let title = decodedParams[i].title;
            let headerContent = globalEvalTranslation + ' ' + (i + 1) + ' - ' + removeTags(title);
            headerLink.innerHTML = headerContent.length > 70 ? headerContent.substring(0, 70) + '...' : headerContent;
        } else {
            headerLink.innerHTML = globalEvalTranslation + ' ' + (i + 1);
        }

        let currentEmbedSelector = accordion.querySelector('[class*="custom-switch"]');
        currentEmbedSelector.children[0].id = "embed_selector_" + i;
        currentEmbedSelector.children[1].setAttribute('for', ("embed_selector_" + i));

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
                // eslint-disable-next-line max-len
                .setAttribute('id', innerAccordion.children[1].firstElementChild.children[j].firstElementChild.getAttribute('id') + i);
            if (j !== 2) {
                innerAccordion.children[1].firstElementChild.children[j].children[1]
                    // eslint-disable-next-line max-len
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
            switch (j) {
                case 0: {
                    innerAccordion.children[1].firstElementChild.children[j].children[1]
                        .innerHTML = valToUse;
                    break;
                }
                case 1: {
                    innerAccordion.children[1].firstElementChild.children[j].children[1]
                        .innerHTML = removeTags(valToUse);
                    break;
                }
                case 2: {
                    for (let counter = 0; counter < valToUse.length; counter++) {
                        let objContainer = document.createElement('div');
                        objContainer.setAttribute('style', 'display: flex;');
                        objContainer.setAttribute('id', 'evaluation_obj_content_' + i + '_' + counter);
                        objContainer.innerHTML = valToUse[counter];
                        innerAccordion.children[1].firstElementChild.children[j].appendChild(objContainer);
                    }
                    break;
                }
            }
        }

    }
    formatEditorAndFileManager();
    initPhpGlobalEvaluationValidation(decodedParams);
    initRedactTools(2, decodedParams.map(value => value.toolname));
    handleAccordions();
    initAccordionsAfterFillingForm();
}

/**
 * @param {array} decodedParams
 */
function initPhpGlobalEvaluationValidation(decodedParams) {
    if (decodedParams.length > 0) {
        for (let i = 0; i < decodedParams.length; i++) {
            handleEmbed(('fitem_id_evaluation_introduction_embed_' + i), ('fitem_id_evaluation_introduction_' + i), i);
        }
        validateEvaluationDataForm(decodedParams.length);
    }
}
