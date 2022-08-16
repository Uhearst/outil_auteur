import {appendSizeToFileManager, formatEditorAndFileManager, getAndSetScrollPosition, handleEmbed, removeTags} from "./utils";
import {initRedactTools} from "./toolHelper";
import {dictionnary} from "./language/format_udehauthoring_fr";
import {validateSectionForm} from "./validator/sectionValidator";

let counterElement = 0;
let counterSubElement = 0;
let element = null;
let elementName = null;
let subElementsName = null;

/**
 * @param {array} params
 */
export function init(params) {
   const decodedParams = JSON.parse(params);
   setElementNameByType(decodedParams.type);
   handleInit(decodedParams.courseId,
       decodedParams.sectionId,
       decodedParams.subQuestionId,
       decodedParams.type,
       decodedParams.toolList);
   updateAddButtonStyle();
   updateRemoveButtonStyle(decodedParams.sectionId !== null, decodedParams.subQuestionId !== null);
   formatEditorAndFileManager();
   getAndSetScrollPosition();
   appendSizeToFileManager();
}

/**
 *
 */
function handleModuleEmbedAndValidation() {
   validateSectionForm();
   handleEmbed('fitem_id_section_introduction_embed', 'fitem_id_section_introduction');
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
 * @param {array} toolList
 */
function handleInit(courseId, sectionId, subQuestionId, type, toolList) {
   if (type === 0) {
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
                updatePreviewHeader(false);
                subElementsName.forEach(subElementName => {
                   updateCurrentAccordions(subElementName);
                });
             },
             error: function() {
                window.console.log('failure');
             }
          });
      handleModuleEmbedAndValidation();
   } else {
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
                      counterElement = parsedResponse.sectionIndex;
                      counterSubElement = i;
                   }
                });
                updatePreviewHeader(true);
                subElementsName.forEach(subElementName => {
                   updateCurrentAccordions(subElementName);
                });
             },
             error: function() {
                window.console.log('failure');
             }
          });
      initRedactTools(1, toolList);
      handleFreeActivity();
   }
}

/**
 *
 */
function handleFreeActivity() {
   let freeTypesTextArea = document.querySelectorAll('[name^="exploration_activity_type["]');
   for (let i = 0; i < freeTypesTextArea.length; i++) {
      hideShowFreeActivity(freeTypesTextArea, i);
      freeTypesTextArea[i].addEventListener('change', () => {
         hideShowFreeActivity(freeTypesTextArea, i);
      });
   }
}

/**
 * @param {NodeList} freeTypesTextArea
 * @param {int} i
 */
function hideShowFreeActivity(freeTypesTextArea, i) {
   let currentContainer = document.getElementById('fitem_id_exploration_activity_free_type_' + i);
   if (freeTypesTextArea[i].value !== '19') {
      currentContainer.setAttribute('style', 'display: none !important;');
   } else {
      if (currentContainer.getAttribute('style') !== null) {
         currentContainer.removeAttribute('style');
      }
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
 * @param {object} isSectionPage
 * @param {string} isSubQuestionPage
 */
function updateRemoveButtonStyle(isSectionPage, isSubQuestionPage) {
   let removeButtons = document.querySelectorAll('[name^="remove_"]');
   removeButtons.forEach(removeButton=> {
      if (removeButton.getAttribute('type') === 'submit') {
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
   if (isSectionPage && !isSubQuestionPage) {
      disableRemoveButton([...removeButtons], 'id_remove_section');
   } else if (isSubQuestionPage) {
      disableRemoveButton([...removeButtons]
          .filter(removeButton => removeButton.id.includes('exploration')), 'id_remove_exploration');
      disableRemoveButton([...removeButtons].filter(removeButton => removeButton.id.includes('resource')), 'id_remove_resource');
   }

}

/**
 * @param {Array} buttonList
 * @param {string} id
 */
function disableRemoveButton(buttonList, id) {
   if (buttonList.length === 1) {
      let buttons = document.querySelectorAll('[id^="' + id + '"]');
      buttons.forEach(button => {
         if (button.tagName === 'BUTTON') {
            button.hidden = true;
         }
      });
   } else {
      let buttons = document.querySelectorAll('[id^="' + id + '"]');
      buttons.forEach(button => {
         if (button.tagName === 'BUTTON') {
            button.hidden = false;
         }
      });
   }
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

   if(clone.attributes) {
      for(let i = 0; i < clone.attributes.length; i++) {
         newElem.setAttribute(clone.attributes[i].name, clone.attributes[i].value);
      }
   }

   return newElem;
}

/**
 * @param {boolean} isSubquestion
 */
function updatePreviewHeader(isSubquestion) {
   let headerPreview = document.querySelector('[id*="preview_header"]');
   if (isSubquestion) {
      headerPreview.firstElementChild.innerHTML = elementName + (counterElement + 1) + '.'
          + (counterSubElement + 1) + ' - ' + removeTags(element.title);
   } else {
      headerPreview.firstElementChild.innerHTML = elementName + (counterElement + 1) + ' - ' + removeTags(element.title);
   }

}

/**
 * @param {string} subElementName
 */
function updateCurrentAccordions(subElementName) {
   let accordions = null;
   let valueForAccordion = null;
   let valueForDiv = null;
   if (subElementName === dictionnary.trame) {
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
          subElementName + ' ' + (counterElement + 1) + '.' + (counterSubElement + 1) + '.' + (accordions.length + 1);
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
   if (array && array[counter]) {
      let title = array[counter].title;
      let headerContent = '';
      if (hasTitle) {
         headerContent = subElementName === dictionnary.trame
             ? subElementName + (counterElement + 1) + '.' + (counter + 1) + ' - ' + removeTags(title)
             // eslint-disable-next-line max-len
             : subElementName + (counterElement + 1) + '.' + (counterSubElement + 1) + '.' + (counter + 1) + ' - ' + removeTags(title);
      } else {
         headerContent = subElementName === dictionnary.trame
             ? subElementName + (counterElement + 1) + '.' + (counter + 1)
             : subElementName + (counterElement + 1) + '.' + (counterSubElement + 1) + '.' + (counter + 1);
      }
      headerLink.innerHTML = headerContent.length > 70 ? headerContent.substring(0, 70) + '...' : headerContent;
   } else {
      headerLink.innerHTML = subElementName === dictionnary.trame
      ? subElementName + (counterElement + 1) + '.' + (counter + 1)
      : subElementName + (counterElement + 1) + '.' + (counterSubElement + 1) + '.' + (counter + 1);
   }
}

