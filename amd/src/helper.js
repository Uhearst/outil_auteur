/* eslint-disable no-undef */
import {initModules} from "./moduleHelper";
import {initObjectives} from "./objectiveHelper";
import {fillFormObjectives} from "./objectiveHelper";
import {fillFormAdditional} from "./additionalHelper";
import {fillFormModules} from "./moduleHelper";
import {
    appendAnchorToForm,
    appendStyleToAnchorMenuSection,
    formatEditorAndFileManager
} from "./utils";
import {fillFormEvaluations, initEvaluations} from "./evaluationHelper";
import {initCourse} from "./courseHelper";
import {initAdditional} from "./additionalHelper";
import {addNotification} from "./notificationHelper";
import {get_string as getString} from 'core/str';

/**
 *
 */
function handleInit() {
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
        } else if (anchor === 'displayable-form-additional-information-container') {
            initAdditional();
            appendStyleToAnchorMenuSection('item-additionalinformation');
        } else {
            initCourse();
            appendStyleToAnchorMenuSection('item-generalinformations');
        }
    }
    window.$(document).scrollTop(0);
    formatEditorAndFileManager();
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
                success: async function(response) {
                    parsedResponse = JSON.parse(response);
                    if (anchor === 'displayable-form-objectives-container') {
                        await fillFormObjectives(parsedResponse.data.teachingobjectives);
                    } else if (anchor === 'displayable-form-sections-container') {
                        await fillFormModules(parsedResponse.data.sections);
                    } else if (anchor === 'displayable-form-evaluations-container') {
                        await fillFormEvaluations(parsedResponse.data.evaluations,
                            parsedResponse.data.sections);
                    } else if (anchor === 'displayable-form-additional-information-container') {
                        await fillFormAdditional(parsedResponse.data.additionalinformation);
                    }
                    formatEditorAndFileManager();
                },
                error: function () {
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
    window.addEventListener('hashchange', function () {
        handleInit();
    });
}

/**
 * @param {array} array
 */
export function fillForm(array) {
    handleFillForms(array);
    window.addEventListener('hashchange', function () {
        handleFillForms(array);
    });
}

/**
 *
 */
export function initSaveWarningModal() {
    let links = document.querySelectorAll('.router');
    links.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            if (e.target.nodeName === 'A') {
                e.preventDefault();
                saveWarningModal(e.target.href);
            } else if (e.target.closest('a') !== '') {
                e.preventDefault();
                saveWarningModal(e.target.closest('a').href);
            } else if (e.target.first('a') !== '') {
                e.preventDefault();
                saveWarningModal(e.target.first('a').href);
            }
        });
    });
}

/**
 * @param {string | null} href
 * @param {int} courseId
 */
export function saveWarningModal(href, courseId = 0) {
    let form = document.getElementById('udeh-form');
    if (form.dataset.changed === 'true') {
        let modalContainer = window.$("#save-warning-container");
        modalContainer.show();
        let modal = window.$(".save-warning");
        let modalButton = document.querySelector(".save-warning").querySelector('.btn-secondary');

        getString(courseId !== 0 ? 'publishwarning' : 'savewarning', 'format_udehauthoring')
            .then(
                (valueString) => {
                    let body = document.querySelector(".save-warning").querySelector('p');
                    body.innerHTML = valueString;
                }
            )
            .catch(() => {
                document.querySelector(".save-warning").querySelector('p').innerHTML =
                    'Êtes-vous certain(e) de vouloir publier sans sauvegarder?';
            });

        let key = '';
        let component = '';
        if (courseId !== 0) {
            key = 'publish';
            component = 'format_udehauthoring';
        } else {
            key = 'continue';
            component = 'theme_remui';
        }

        getString(key, component)
            .then(
                (valueString) => {
                    modalButton.innerHTML = valueString;
                }
            )
            .catch(() => {
                modalButton.innerHTML('Publier');
            });

        modal.show();
        modalButton.addEventListener('click', () => {
            modalContainer.hide();
            modal.hide();
            if (href) {
                form.dataset.changed = 'false';
                window.location.href = href;
            } else if (courseId) {
                publishCourse(courseId);
            }
        });

        document.querySelector(".save-warning").querySelector('.btn-primary').addEventListener('click', () => {
            modalContainer.hide();
            modal.hide();
        });
    } else {
        if (href) {
            window.location.href = href;
        } else if (courseId) {
            publishCourse(courseId);
        }
    }
}

/**
 * @param {int} courseId
 */
function publishCourse(courseId) {
    window.$("#save-warning-container").show();
    window.$("#progress-circle").show();
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    window.$.ajax({
        type: "POST",
        url: "../handlers/ajax_export_handler.php",
        data: {
            courseId: courseId,
            id: urlParams.get('id')
        },
        success: async function(response) {
            let parsedResponse = JSON.parse(response);
            if (parsedResponse.error) {
                addNotification(
                    await getString('notificationerrorpublishcourse', 'format_udehauthoring') + ' ' + parsedResponse.msg,
                    2);
            } else {
                let previewButton = document.querySelector('#preview-button');
                if (parsedResponse.data.hasOwnProperty('previewurls')) {
                    previewButton.setAttribute('data-urls', JSON.stringify(parsedResponse.data.previewurls));
                } else {
                    previewButton.setAttribute('data-url', parsedResponse.data.previewurl);
                }
                addNotification(await getString('notificationpublishcourse', 'format_udehauthoring'), 1);
            }
            window.$("#save-warning-container").hide();
            window.$("#progress-circle").hide();
        },
        error: async function() {
            window.$("#save-warning-container").hide();
            window.$("#progress-circle").hide();
            addNotification(await getString('notificationerrorpublishcourse', 'format_udehauthoring'), 2);
        }
    });
}

/**
 * @param {int} courseId
 */
export function exportCourse(courseId) {
    let publishButton = document.getElementById('publishCourse');
    publishButton.addEventListener('click', function (evt) {
        evt.preventDefault();
        saveWarningModal(null, courseId);
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

