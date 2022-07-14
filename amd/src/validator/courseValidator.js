import {validateIsEmbed} from "./validatorUtils";

/**
 *
 */
export function validateCourseForm() {
    document.getElementById('udeh-form').addEventListener("submit", function(e) {
        e.preventDefault();
        validateIsEmbed();
        window.$('#udeh-form').submit();
    });
}
