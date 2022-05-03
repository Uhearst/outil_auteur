import {validateCourseForm} from "./validator/courseValidator";

/**
 *
 */
export function initCourse() {
    validateCourseForm();
    handleEmbed();
}

/**
 *
 */
function handleEmbed() {
    let customCheckbox = document.getElementById('embed_selector');
    let embedValContainer = document.querySelector('[name="isembed"]');
    if (embedValContainer.value === "1") {
        customCheckbox.checked = true;
    }
    setEmbedVisible(customCheckbox);

    customCheckbox.addEventListener('click', function() {
        setEmbedVisible(customCheckbox);
    });
}

/**
 * @param {object} customCheckbox
 */
function setEmbedVisible(customCheckbox) {
    let embedContainer = document.getElementById('fitem_id_course_introduction_embed');
    let introductionContainer = document.getElementById('fitem_id_course_introduction');
    if (customCheckbox.checked) {
        embedContainer.style = '';
        introductionContainer.style = 'display:none !important';
    } else {
        embedContainer.style = 'display:none';
        introductionContainer.style = '';
    }
}