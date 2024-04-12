import {addNotification} from "../notificationHelper";
import {get_string as getString} from 'core/str';

export const validateAdditionalInfo = () => {
    let form = document.getElementById('udeh-form');
    form.addEventListener("submit", async function(e) {
        e.preventDefault();
        if (window.location.href.includes('displayable-form-additional-information-container')) {
            let missingTitle = false;
            window.$("[id^=id_add_info_title_]").each(function() {
                if (window.$(this).val().length === 0) {
                    missingTitle = true;
                }
            });

            if (missingTitle) {
                addNotification(await getString('notificationerrortitle', 'format_udehauthoring'), 2);
            }

            if (!missingTitle) {
                window.$('#udeh-form').submit();
            }
        } else {
            window.$('#udeh-form').submit();
        }
    });
};