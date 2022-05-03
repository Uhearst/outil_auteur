import {initModules} from "./moduleHelper";
import {initObjectives} from "./objectiveHelper";
import {fillFormObjectives} from "./objectiveHelper";
import {fillFormModules} from "./moduleHelper";
import {
    appendAnchorToForm,
    appendStyleToAnchorMenuSection,
    formatEditorAndFileManager
} from "./utils";
import {fillFormEvaluations, initEvaluations} from "./evaluationHelper";
import {initCourse} from "./courseHelper";

/**
 *
 */
function handleInit() {
    formatEditorAndFileManager();
    let n = window.location.pathname.lastIndexOf('/');
    let page = window.location.pathname.substring(n + 1);
    let anchor = window.location.href.includes("#") ?
        window.location.href.substring(window.location.href.lastIndexOf('#') + 1) : 'displayable-form-informations-container';
    if (page.includes('course')) {
        appendAnchorToForm(anchor);
        if (anchor === 'displayable-form-objectives-container') {
            initObjectives();
            appendStyleToAnchorMenuSection('item-teachingobjectives');
        } else if (anchor === 'displayable-form-sections-container') {
            initModules();
            appendStyleToAnchorMenuSection('item-coursesections');
        } else if (anchor === 'displayable-form-evaluations-container') {
            initEvaluations();
            appendStyleToAnchorMenuSection('item-learningevaluations');
        } else {
            initCourse();
            appendStyleToAnchorMenuSection('item-generalinformations');
        }
    }
}

/**
 * @param {array} params
 */
function handleFillForms(params) {
    let n = window.location.pathname.lastIndexOf('/');
    let page = window.location.pathname.substring(n + 1);
    let anchor = window.location.href.includes("#") ?
        window.location.href.substring(window.location.href.lastIndexOf('#') + 1) : 'displayable-form-informations-container';
    if (page.includes('course')) {
        let parsedResponse = null;
        const decodedParams = JSON.parse(params);
        window.$.ajax(
            {
                type: "POST",
                url: "../handlers/ajax_course_handler.php",
                data: {
                    courseId: decodedParams.courseId
                },
                success: function(response) {
                    parsedResponse = JSON.parse(response);
                    if (anchor === 'displayable-form-objectives-container') {
                        fillFormObjectives(parsedResponse.data.teachingobjectives);
                    } else if (anchor === 'displayable-form-sections-container') {
                        fillFormModules(parsedResponse.data.sections);
                    } else if (anchor === 'displayable-form-evaluations-container') {
                        fillFormEvaluations(parsedResponse.data.evaluations,
                            parsedResponse.data.sections);
                    }
                    formatEditorAndFileManager();
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
export function init() {
    handleInit();
    // getAndSetScrollPosition();
    window.addEventListener('hashchange', function() {
        handleInit();
    });
}

/**
 * @param {array} array
 */
export function fillForm(array) {
    handleFillForms(array);
    window.addEventListener('hashchange', function() {
        handleFillForms(array);
    });
}

/**
 * @param {int} courseId
 */
export function exportCourse(courseId) {
    let publishButton = document.getElementById('publishCourse');
    publishButton.addEventListener('click', function() {
        window.$.ajax(
            {
                type: "POST",
                url: "../handlers/ajax_export_handler.php",
                data: {
                    courseId: courseId,
                },
                success: function() {
                    // TODO add Modal instead of reload
                    location.reload();
                },
                error: function() {
                    window.console.log('failure');
                }
            });
    });

    let publishFlushButton = document.getElementById('publishFlushCourse');
    publishFlushButton.addEventListener('click', function() {
        window.$.ajax(
            {
                type: "POST",
                url: "../handlers/ajax_export_handler.php",
                data: {
                    courseId: courseId,
                    flush: "flush",
                },
                success: function() {
                    // TODO add Modal instead of reload
                    location.reload();
                },
                error: function() {
                    window.console.log('failure');
                }
            });
    });
}

/**
 * @param {array} Ids
 */
export function publishCoursePlan(Ids) {
    let publishButton = document.getElementById('publishCoursePlan');
    publishButton.addEventListener('click', function() {
        window.open("../handlers/pdf_syllabus_handler.php?courseId=" + Ids[1]);
    });
}