import {addNotification} from "../notificationHelper";
import {areIframesEmpty} from "./validatorUtils";
import {get_string as getString} from 'core/str';


/**
 *
 */
export const validateSubquestionForm = () => {
    document.getElementById('udeh-form').addEventListener("submit", async function(e) {
        if (e.submitter.id === 'save-button') {
            e.preventDefault();
            let areTitlesMissing = await areTitlesFilled();
            if (!areTitlesMissing) {
                window.$('#udeh-form').submit();
            }
        }

    });
};

const areTitlesFilled = async() => {
    let resourceTitles = document.querySelectorAll('[id^="id_resource_title_"][id$="_ifr"]');

    let missingTitle = areIframesEmpty(resourceTitles);

    if (missingTitle) {
        addNotification(await getString('notificationerrortitle', 'format_udehauthoring'), 2);
    }

    return missingTitle;
};