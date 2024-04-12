/**
 * @param {Integer} i
 */
const validateIsEmbed = (i = null) => {

    let isembedval = null;
    let embedSelector = null;
    if (i !== null) {
        isembedval = document.querySelector('[name="isembed[' + i + ']"]');
        embedSelector = document.getElementById('embed_selector_' + i);
    } else {
        isembedval = document.querySelector('[name="isembed"]');
        embedSelector = document.getElementById('embed_selector');
    }
    if (embedSelector.checked) {
        isembedval.value = "1";
    } else {
        isembedval.value = "0";
    }
};

const areIframesEmpty = (nodeArray) => {
    let isEmpty = false;
    if (nodeArray.length >= 2) {
        nodeArray.forEach(function(node, index) {

            let IframeNode = node.contentWindow.document.body.firstElementChild;
            let value = null;
            if (IframeNode.tagName === 'INPUT' || IframeNode.tagName === 'TEXTAREA') {
                value = IframeNode.value;
            } else {
                value = IframeNode.textContent;
            }
            if (index !== 0 && value.length === 0) {
                isEmpty = true;
            }
        });
    }
    return isEmpty;
};

export {validateIsEmbed, areIframesEmpty};

