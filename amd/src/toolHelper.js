import * as Str from 'core/str';
import {addNotification} from "./notificationHelper";

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
    let evaluationIdInput = document.createElement('input');
    evaluationIdInput.name = 'evaluationid';
    evaluationIdInput.type = 'hidden';
    evaluationIdInput.value = params[2];
    let isglobalInput = document.createElement('input');
    isglobalInput.name = 'isglobal';
    isglobalInput.type = 'hidden';
    isglobalInput.value = params[3];
    form.firstElementChild.appendChild(subquestionIdInput);
    form.firstElementChild.appendChild(explorationIdInput);
    form.firstElementChild.appendChild(evaluationIdInput);
    form.firstElementChild.appendChild(isglobalInput);
    let buttonarr = document.getElementById('fgroup_id_buttonar');
    if (buttonarr === null) {
        window.console.error('Back button to redact couldn\'t be appended. Contact a programmer.');
    } else {
        let buttonsContainer = buttonarr.children[1].children[0].children[0];
        let newButton = buttonsContainer.children[1].cloneNode(true);
        newButton.children[0].children[0].name = 'submitbutton3';
        newButton.children[0].children[0].id = 'id_submitbutton3';
        var strings = [
            {
                key: 'saveandreturntoredact',
                component: 'format_udehauthoring'
            },
        ];
        Str.get_strings(strings).then(function(results) {
            if (results === [] || results === null) {
                newButton.children[0].children[0].value = 'Enregistrer et retourner à la rédaction';
            } else {
                newButton.children[0].children[0].value = results[0];
            }
        });

        newButton.children[1].id = 'id_error_submitbutton3';
        buttonsContainer.insertBefore(newButton, buttonsContainer.children[2]);
    }
}

/**
 * @param {int} type
 * @param {array} toolList
 */
export function initRedactTools(type, toolList) {
    if (type === 1) {
        appendExplorationLinkToRedactForm(toolList);
    } else if (type === 2) {
        appendEvaluationLinkToRedactForm(toolList);
    }
    appendDataToSubmit();
}

/**
 *
 */
function appendDataToSubmit() {
    let inputToAppend = document.createElement('input');
    document.getElementById('udeh-form').addEventListener("submit", function(e) {
        if (e.submitter && e.submitter.name.includes('generate_tool')) {
            e.preventDefault();
            let n = window.location.pathname.lastIndexOf('/');
            let page = window.location.pathname.substring(n + 1);
            if (page.includes('subquestion')) {
                let index = e.submitter.name.substring(
                    e.submitter.name.indexOf('[') + 1,
                    e.submitter.name.indexOf(']')
                );
                let explorationIdHolder = document.querySelector('[name="exploration_id[' + index + ']"]');
                if (explorationIdHolder === null || explorationIdHolder.value === "0") {
                    addNotification('Vous devez enregistrer l\'exploration avant d\'y associer un outil.', 2);
                    return;
                }
            }
            inputToAppend.type = 'hidden';
            inputToAppend.name = e.submitter.name;
            inputToAppend.value = 'Generate Tool';
            window.$('#udeh-form')[0].append(inputToAppend);
            window.$('#udeh-form').submit();
        }
    });
}

/**
 * @param {array} toolList
 */
function appendEvaluationLinkToRedactForm(toolList) {
    let evaluationToolsLink = document.querySelectorAll('[name^="evaluation_tool_cmid"]');
    if (location.href.includes('globalevaluation')) {
        appendMultipleEvaluationLinkToRedactForm(evaluationToolsLink, toolList);
    } else {
        appendSingleEvaluationLinkToRedactForm(toolList[0]);
    }
}

/**
 * @param {string} evalName
 */
function appendSingleEvaluationLinkToRedactForm(evalName) {
    let evaluationToolLink = document.querySelector('[name^="evaluation_tool_cmid"]');

    let urlDisplayer = document.getElementById('fgroup_id_url_group');

    let evaluationId = null;
    let evaluationIdHolder = document.querySelector('[name="id"]');
    if (evaluationToolLink.value === null
        || evaluationToolLink.value === '') {
        urlDisplayer.style = "display:none;";
        return;
    } else {
        let currentButton = document.getElementById('id_generate_tool');
        currentButton.disabled = true;
        let select = document.getElementById('id_evaluation_tool');
        select.disabled = true;
        evaluationId = evaluationIdHolder.value;
    }

    let container = document.createElement('div');
    container.setAttribute('class', 'form-group fitem');
    let linkContainer = document.createElement('a');
    let url = window.location.href.substring(0, window.location.href.indexOf('redact'));
    url = url + 'udeh_modedit.php?update=' + evaluationToolLink.value + '&return=0&sr0=&evaluationid='
        + evaluationId;
    linkContainer.setAttribute('href', url);
    linkContainer.setAttribute('id', 'evaluation_tool_name');
    linkContainer.innerHTML = evalName;
    container.appendChild(linkContainer);

    urlDisplayer.children[1].children[0].children[1].insertBefore(
        container, urlDisplayer.children[1].children[0].children[1].firstElementChild);

}

/**
 * @param {array} evaluationToolsLink
 * @param {array} toolList
 */
function appendMultipleEvaluationLinkToRedactForm(evaluationToolsLink, toolList) {
    evaluationToolsLink.forEach(evaluationToolLink => {
        let toolIndex = evaluationToolLink.name.slice(evaluationToolLink.name.indexOf('[') + 1,
            evaluationToolLink.name.lastIndexOf(']'));
        let urlDisplayer = document.getElementById('fgroup_id_url_group_' + toolIndex);

        let evaluationId = null;
        let evaluationIdHolder = document.querySelector('[name="evaluation_id[' + toolIndex + ']"]');
        if (evaluationToolLink.value === null
            || evaluationToolLink.value === '') {
            urlDisplayer.style = "display:none;";
            return;
        } else {
            let currentButton = document.getElementById('id_tool_group_' + toolIndex + '_generate_tool');
            currentButton.disabled = true;
            let select = document.getElementById('id_tool_group_' + toolIndex + '_evaluation_tool');
            select.disabled = true;
            evaluationId = evaluationIdHolder.value;
        }

        let container = document.createElement('div');
        container.setAttribute('class', 'form-group fitem');
        let linkContainer = document.createElement('a');
        let url = window.location.href.substring(0, window.location.href.indexOf('redact'));
        url = url + 'udeh_modedit.php?update=' + evaluationToolLink.value + '&return=0&sr0=&evaluationid='
            + evaluationId + '&isglobal=1';
        linkContainer.setAttribute('href', url);
        linkContainer.setAttribute('id', 'evaluation_tool_name_' + toolIndex);
        linkContainer.innerHTML = toolList[toolIndex];
        container.appendChild(linkContainer);

        urlDisplayer.children[1].children[0].children[1].insertBefore(
            container, urlDisplayer.children[1].children[0].children[1].firstElementChild);
    });
}

/**
 * @param {array} toolList
 */
function appendExplorationLinkToRedactForm(toolList) {
    let explorationToolsLink = document.querySelectorAll('[name^="exploration_tool_cmid["]');
    explorationToolsLink.forEach(explorationToolLink => {
        let toolIndex = explorationToolLink.name.slice(explorationToolLink.name.indexOf('[') + 1,
            explorationToolLink.name.lastIndexOf(']'));
        let urlDisplayer = document.getElementById('fgroup_id_url_group_' + toolIndex);

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
            let select = document.getElementById('id_tool_group_' + toolIndex + '_exploration_tool');
            select.disabled = true;
            explorationId = explorationIdHolder.value;
        }
        let container = document.createElement('div');
        container.setAttribute('class', 'form-group fitem');
        let linkContainer = document.createElement('a');
        let url = window.location.href.substring(0, window.location.href.indexOf('redact'));
        url = url + 'udeh_modedit.php?update=' + explorationToolLink.value + '&return=0&sr0=&subquestionid='
            + subquestionId + '&explorationid=' + explorationId;
        linkContainer.setAttribute('href', url);
        linkContainer.setAttribute('id', 'exploration_tool_name_' + toolIndex);
        linkContainer.innerHTML = toolList[toolIndex];
        container.appendChild(linkContainer);
        urlDisplayer.children[1].children[0].children[1].insertBefore(
            container, urlDisplayer.children[1].children[0].children[1].firstElementChild);
    });
}