import {addNotification} from "./notificationHelper";
import {get_string as getString} from 'core/str';
import {saveWarningModal} from "./helper";

/**
 */
export function initNavBar() {
    window.addEventListener('load', function() {
        window.$('body').css('zoom', localStorage.getItem('zoomLevel'));
    });
    zoom();
    preview();
    configure();
}

/**
 *
 */
function configure() {
    let button = document.querySelector('#config-button');
    if (button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            window.$('#config-dialog').show();
            window.$('#save-warning-container').show();

            window.$('#config-dialog').find('.btn-secondary').on('click', function() {
                window.$('#config-dialog').hide();
                window.$('#save-warning-container').hide();
            });

            window.$('#config-dialog').find('.btn-primary').on('click', function() {
                window.$('#progress-circle').show();
                window.$.ajax({
                    method: "POST",
                    url: "../handlers/ajax_config_handler.php",
                    data: {
                        courseId: window.$('#courseId').val(),
                        module: window.$('#etiModule').val(),
                        question: window.$('#etiQuestion').val(),
                        questionExplore: window.$('#etiExplore').val(),
                        questionHide: window.$('#etiHide').val(),
                        questionSub: window.$('#etiSub').val()
                    }
                }).done(async function() {
                    window.$('#config-dialog').hide();
                    window.$('#save-warning-container').hide();
                    window.$('#progress-circle').hide();
                    addNotification(await getString('notificationsavelabel', 'format_udehauthoring'), 1);
                }).fail(async function() {
                    window.$('#config-dialog').hide();
                    window.$('#save-warning-container').hide();
                    window.$('#progress-circle').hide();
                    addNotification(await getString('notificationerrorsave', 'format_udehauthoring'), 2);

                });
            });
        });
    }
}

/**
 *
 */
function zoom() {
    let zoomButtons = document.querySelectorAll('.zoom-level');
    zoomButtons.forEach(zoomButton => {
        zoomButton.addEventListener('click', function(event) {
            localStorage.setItem('zoomLevel', (event.target.value / 100));
            window.$('body').css('zoom', (event.target.value / 100));
        });
    });
}

/**
 *
 */
function preview() {
    let previewButton = document.querySelector('#preview-button');
    if (previewButton) {
        previewButton.addEventListener('click', async function() {
            if (window.location.href.includes('section.php')) {
                let sectionTitle = document.querySelector('#udeh-form').querySelector('input[name="section_title"]');
                if (sectionTitle.value === '' || sectionTitle.value === undefined || sectionTitle.value === null) {
                    addNotification(await getString('notificationerrormissingsectionsnamepreview', 'format_udehauthoring'), 2);
                    return;
                }
            }
            if ("url" in previewButton.dataset) {
                saveWarningModal(previewButton.dataset.url);
            }
            if ("urls" in previewButton.dataset) {
                let anchor = location.hash.substr(1);
                let urls = JSON.parse(previewButton.dataset.urls);
                if (anchor in urls) {
                    saveWarningModal(urls[anchor]);
                }
            }
        });
    }
}