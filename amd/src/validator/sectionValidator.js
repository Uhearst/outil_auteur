import {validateIsEmbed} from "./validatorUtils";

/**
 *
 */
export function validateSectionForm() {
    document.getElementById('udeh-form').addEventListener("submit", function(e) {
        if(e.submitter.id === "save-button") {
            e.preventDefault();
            validateIsEmbed();
            window.$('#udeh-form').submit();
        }
    });
}