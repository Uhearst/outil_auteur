import {addIssueNotification} from "./notificationHelper";

/**
 * @param {array} params
 */
export function initEditTools(params) {
    let form = document.querySelector('[action^="modedit.php"]');
    form.action = "udeh_modedit.php";
    let subquestionIdInput = document.createElement('input');
    subquestionIdInput.name = 'subquestionid';
    subquestionIdInput.type = 'hidden';
    subquestionIdInput.value = params[0];
    let explorationIdInput = document.createElement('input');
    explorationIdInput.name = 'explorationid';
    explorationIdInput.type = 'hidden';
    explorationIdInput.value = params[1];
    form.firstElementChild.appendChild(subquestionIdInput);
    form.firstElementChild.appendChild(explorationIdInput);
    let buttonarr = document.getElementById('fgroup_id_buttonar');
    if (buttonarr === null) {
        window.console.error('Back button to redact couldn\'t be appended. Contact a programmer.');
    } else {
        let buttonsContainer = buttonarr.children[1].children[0].children[1];
        let newButton = buttonsContainer.children[1].cloneNode(true);
        newButton.children[0].children[0].name = 'submitbutton3';
        newButton.children[0].children[0].id = 'id_submitbutton3';
        newButton.children[0].children[0].value = 'Save and return to redact';
        newButton.children[1].id = 'id_error_submitbutton3';
        buttonsContainer.insertBefore(newButton, buttonsContainer.children[2]);
    }
}

/**
 *
 */
export function initRedactTools() {
    appendLinkToRedactForm();
    handleReactButton();
}

/**
 *
 */
function handleReactButton() {
    let generateButtons = document.querySelectorAll('[id*="_generate_tool"][type="button"]');
    generateButtons.forEach(function(button, i) {
        button.addEventListener('click', () => {
            let select = document.getElementById('id_tool_group_' + i + '_exploration_tool');
            let activityType = select.options[select.selectedIndex].text.toLowerCase();
            let courseId = button.getAttribute('courseid');
            let explorationId = null;
            let explorationIdHolder = document.querySelector('[name="exploration_id[' + i + ']"]');
            if (explorationIdHolder === null || explorationIdHolder.value === "0") {
                addIssueNotification('Vous devez enregistré l\'exploration avant d\'y associer un outil');
                return;
            } else {
                explorationId = explorationIdHolder.value;
            }
            let subquestionId = null;
            if (window.location.href.indexOf('=') === -1) {
                subquestionId = 0;
            } else {
                subquestionId = window.location.href.substring(window.location.href.indexOf('=') + 1);
            }
            let url = window.location.href.substring(0, window.location.href.indexOf('redact'));
            url = url + 'udeh_modedit.php?add=' + activityType + '&type=&course=' + courseId +
                '&section=0&return=0&sr=&subquestionid=' + subquestionId + '&explorationid=' + explorationId;
            window.location.assign(url);
        });
    });
}

/**
 *
 */
function appendLinkToRedactForm() {
    let explorationToolsLink = document.querySelectorAll('[name^="exploration_tool_cmid["]');
    explorationToolsLink.forEach(explorationToolLink => {
        let toolIndex = explorationToolLink.name.slice(explorationToolLink.name.indexOf('[') + 1,
            explorationToolLink.name.lastIndexOf(']'));
        let urlDisplayer = document.getElementById('fitem_id_exploration_tool_url_display_' + toolIndex);

        let subquestionId = null;
        if (window.location.href.indexOf('=') === -1) {
            subquestionId = 0;
        } else {
            subquestionId = window.location.href.substring(window.location.href.indexOf('=') + 1);
        }
        let explorationId = null;
        let explorationIdHolder = document.querySelector('[name="exploration_id[' + toolIndex + ']"]');
        if (explorationIdHolder === null
            || explorationIdHolder.value === "0"
            || explorationToolLink.value === null
            || explorationToolLink.value === '') {
            urlDisplayer.style = "display:none;";
            return;
        } else {
            let currentButton = document.getElementById('id_tool_group_' + toolIndex + '_generate_tool');
            currentButton.disabled = true;
            explorationId = explorationIdHolder.value;
        }

        let linkContainer = document.createElement('a');
        let url = window.location.href.substring(0, window.location.href.indexOf('redact'));
        url = url + 'udeh_modedit.php?update=' + explorationToolLink.value + '&return=0&sr0=&subquestionid='
            + subquestionId + '&explorationid=' + explorationId;
        linkContainer.setAttribute('href', url);
        linkContainer.appendChild(urlDisplayer.children[1]);

        urlDisplayer.appendChild(linkContainer);
    });
}