/**
 * @param {Integer} i
 */
export function validateIsEmbed(i = null) {

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
}