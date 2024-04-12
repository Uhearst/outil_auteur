import {areIframesEmpty, validateIsEmbed} from "./validatorUtils";
import {addNotification} from "../notificationHelper";
import {get_string as getString} from 'core/str';

/**
 *
 */
export function validateSectionForm() {
    document.getElementById('udeh-form').addEventListener("submit", async function(e) {
        if (e.submitter.id === 'save-button') {
            e.preventDefault();
            let areTitlesMissing = await areTitlesFilled();
            if (!areTitlesMissing) {
                validateIsEmbed();
                window.$('#udeh-form').submit();
            }
        }

    });
}

const areTitlesFilled = async() => {
    let subQTitles = document.querySelectorAll('[id^="id_subquestion_title_"][id$="_ifr"]');

    let missingTitle = areIframesEmpty(subQTitles);

    if (missingTitle) {
        addNotification(await getString('notificationerrortitle', 'format_udehauthoring'), 2);
    }

    return missingTitle;
};
