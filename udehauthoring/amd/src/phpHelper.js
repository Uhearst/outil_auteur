import {formatEditorAndFileManager, removeTags} from "./utils";
import {dictionnary} from "./language/format_udehauthoring_fr";

let counterElement = 0;
let element = null;
let elementName = null;
let subElementsName = null;

/**
 * @param {array} titles
 */
export function initGlobalEvaluations(titles) {
   const decodedParams = JSON.parse(titles);
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
      if (decodedParams[i]) {
         let title = decodedParams[i];
         let headerContent = 'Evaluation Globale' + ' ' + (i + 1) + ' - ' + removeTags(title);
         headerLink.innerHTML = headerContent.length > 70 ? headerContent.substring(0, 70) + '...' : headerContent;
      } else {
         headerLink.innerHTML = 'Evaluation Globale' + ' ' + (i + 1);
      }
   });
   formatEditorAndFileManager();
}

/**
 * @param {array} params
 */
export function init(params) {
   const decodedParams = JSON.parse(params);
   setElementNameByType(decodedParams.type);
   handleInit(decodedParams.courseId, decodedParams.sectionId, decodedParams.subQuestionId, decodedParams.type);
   updateAddButtonStyle();
   updateRemoveButtonStyle();
   formatEditorAndFileManager();
}

/**
 * @param {int} type
 */
function setElementNameByType(type) {
   if (type === 0) {
      elementName = dictionnary.module;
      subElementsName = [dictionnary.trame];
   } else {
      elementName = dictionnary.trame;
      subElementsName = [dictionnary.activity, dictionnary.resource];
   }
}

/**
 * @param {int} courseId
 * @param {int} sectionId
 * @param {int} subQuestionId
 * @param {int} type
 */
function handleInit(courseId, sectionId, subQuestionId, type) {
   if(type === 0) {
      window.$.ajax(
          {
             type: "POST",
             url: "../handlers/ajax_module_handler.php",
             data: {
                courseId: courseId
             },
             success: function(response) {
                let parsedResponse = JSON.parse(response);
                parsedResponse.data.forEach(function(module, i) {
                   if (parseInt(module.id) === sectionId) {
                      element = module;
                      counterElement = i;
                   }
                });
                updatePreviewHeader();
                subElementsName.forEach(subElementsName => {
                   updateCurrentAccordions(subElementsName);
                });
             },
             error: function() {
                window.console.log('failure');
             }
          });
   } else {
/*      let dataToSend = JSON.stringify({
         sectionId: sectionId,
         courseId: courseId
      });*/
      window.$.ajax(
          {
             type: "POST",
             url: "../handlers/ajax_subquestion_handler.php",
             data: {
                sectionId: sectionId,
                courseId: courseId
             },
             success: function(response) {
                let parsedResponse = JSON.parse(response);
                parsedResponse.data.forEach(function(subQuestion, i) {
                   if (parseInt(subQuestion.id) === subQuestionId) {
                      element = subQuestion;
                      counterElement = i;
                   }
                });
                updatePreviewHeader();
                subElementsName.forEach(subElementsName => {
                   updateCurrentAccordions(subElementsName, parsedResponse.index);
                });
             },
             error: function() {
                window.console.log('failure');
             }
          });
   }
}

/**
 *
 */
function updateAddButtonStyle() {
   let addButtons = document.querySelectorAll('[name^="add"]');
   addButtons.forEach(function(addButton, i) {
      if (addButton.getAttribute('type') === 'submit') {
         let colContainers = document.querySelectorAll('.add_action_button');
         let icon = document.createElement('i');

         let updatedButton = changeTag(addButton, 'button');
         updatedButton.setAttribute('class', 'btn add-button');

         icon.setAttribute('class', 'fa fa-plus-circle fa-2x');
         updatedButton.appendChild(icon);
         colContainers[i].appendChild(updatedButton);
         addButton.hidden = true;
      }
   });
}

/**
 *
 */
// eslint-disable-next-line no-unused-vars
function updateRemoveButtonStyle() {
   let removeButtons = document.querySelectorAll('[name^="remove_"]');
   removeButtons.forEach(removeButton=> {
      if(removeButton.getAttribute('type') === 'submit') {
         let icon = document.createElement('i');

         let updatedButton = changeTag(removeButton, 'button');
         updatedButton.setAttribute('class', 'btn remove-button');

         icon.setAttribute('class', 'fa fa-minus-circle fa-2x');
         updatedButton.appendChild(icon);
         removeButton.parentElement.appendChild(updatedButton);
         removeButton.parentElement.classList.add('remove_action_button');
         removeButton.hidden = true;
      }
   });
}


/**
 * @param {object} element
 * @param {string} tag
 */
function changeTag(element, tag) {
   const newElem = document.createElement(tag);
   const clone = element.cloneNode(true);

   while (clone.firstChild) {
      newElem.appendChild(clone.firstChild);
   }

   for (const attr of clone.attributes) {
      newElem.setAttribute(attr.name, attr.value);
   }
   return newElem;
}

/**
 *
 */
function updatePreviewHeader() {
   let headerPreview = document.querySelector('[id*="preview_header"]');
   headerPreview.firstElementChild.innerHTML = elementName + (counterElement + 1) + ' - ' + removeTags(element.title);
}

/**
 * @param {string} subElementName
 * @param {String} sectionIndex
 */
function updateCurrentAccordions(subElementName, sectionIndex = null) {
   let accordions = null;
   let valueForAccordion = null;
   let valueForDiv = null;
   if(subElementName === dictionnary.trame) {
      accordions = document.querySelectorAll('[class*="row_section_subquestion_container"]');
      valueForAccordion = 'SectionSubQuestion_';
      valueForDiv = 'subquestion';
   } else if (subElementName === dictionnary.resource) {
      accordions = document.querySelectorAll('[class*="row_subquestion_resource_container"]');
      valueForAccordion = 'SubQuestionResource_';
      valueForDiv = 'resource';
   } else if (subElementName === dictionnary.activity) {
      accordions = document.querySelectorAll('[class*="row_subquestion_exploration_container"]');
      valueForAccordion = 'SubQuestionExploration_';
      valueForDiv = 'exploration';
   }
   accordions.forEach(function(accordion, i) {
      let accordionContainer = accordion.querySelector('[class*="accordion-container"]');
      let headerLink = accordionContainer.firstElementChild.firstElementChild;
      headerLink.href = '#collapse' + valueForAccordion + (i);
      headerLink.setAttribute('aria-controls', 'collapse' + valueForAccordion + (i));
      accordionContainer.children[1].setAttribute('id', 'collapse' + valueForAccordion + (i));
      if (i > 0) {
         accordionContainer.children[1].setAttribute('class', 'collapse');
         headerLink.setAttribute('aria-expanded', 'false');
         headerLink.setAttribute('class', 'collapsed');
      }
      if (subElementName === dictionnary.trame) {
         updateAccordionText(headerLink, subElementName, i, element.subquestions);
      } else if (subElementName === dictionnary.resource) {
         updateAccordionText(headerLink, subElementName, i, element.resources);
      } else if (subElementName === dictionnary.activity) {
         updateAccordionText(headerLink, subElementName, i, element.explorations, false);
      }
   });
   let addContainer = document.getElementById('add_' + valueForDiv + '_container');
   if (subElementName === dictionnary.trame) {
      addContainer.firstElementChild.firstElementChild.innerHTML =
          subElementName + ' ' + (counterElement + 1) + '.' + (accordions.length + 1);
   } else {
      addContainer.firstElementChild.firstElementChild.innerHTML =
          subElementName + ' ' + (sectionIndex + 1) + '.' + (counterElement + 1) + '.' + (accordions.length + 1);
   }

}

/**
 * @param {object} headerLink
 * @param {string} subElementName
 * @param {int} counter
 * @param {array} array
 * @param {boolean} hasTitle
 */
function updateAccordionText(headerLink, subElementName, counter, array, hasTitle = true) {
   if(array && array[counter]) {
      let title = array[counter].title;
      let headerContent = '';
      if (hasTitle) {
         headerContent = subElementName + (counterElement + 1) + '.' + (counter + 1) + ' - ' + removeTags(title);
      } else {
         headerContent = subElementName + (counterElement + 1) + '.' + (counter + 1);
      }
      headerLink.innerHTML = headerContent.length > 70 ? headerContent.substring(0, 70) + '...' : headerContent;
   } else {
      headerLink.innerHTML = subElementName + (counterElement + 1) + '.' + (counter + 1);
   }
}
