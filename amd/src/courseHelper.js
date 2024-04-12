import {validateCourseForm} from "./validator/courseValidator";
import {appendSizeToFileManager, handleEmbed} from "./utils";

/**
 *
 */
export function initCourse() {
    validateCourseForm();
    appendSizeToFileManager();
    handleEmbed('fitem_id_course_introduction_embed', 'fitem_id_course_introduction');
}
