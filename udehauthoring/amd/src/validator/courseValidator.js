/**
 *
 */
export function validateCourseForm() {
    document.getElementById('udeh-form').addEventListener("submit", function(e) {
        e.preventDefault();
        validateIsEmbed();
        let validEmbed = validateEmbed();
        if (!validEmbed) {
            return;
        }
        window.$('#udeh-form').submit();
    });
}

/**
 *
 */
function validateIsEmbed() {
    let isembedval = document.querySelector('[name="isembed"]');
    let embedSelector = document.getElementById('embed_selector');
    if(embedSelector.checked) {
        isembedval.value = "1";
    } else {
        isembedval.value = "0";
    }
}

/**
 *
 */
function validateEmbed() {
    let embedInput = document.getElementById('id_course_introduction_embed');
    let input = embedInput.value;
    if (input !== '' || input !== null) {
        return true;
    }
    let isValid = true;
    if (input.slice(0, 7) !== '<iframe') {
        isValid = false;
    }
    if (input.slice(-9) !== '</iframe>' && input.slice(-2) !== '/>') {
        isValid = false;
    }

    if(!isValid) {
        let errorMsg = document.getElementById('error_msg_introduction_embed');
        if (errorMsg === null) {
            let span = document.createElement('span');
            span.style.color = 'red';
            span.id = 'error_msg_introduction_embed';
            span.style.margin = '0 0 0.5rem 1rem';
            span.innerHTML =
                '* L\'url du fichier embed doit être au format: \'&lt;iframe&gt;&lt;/iframe&gt;\' ou \'&lt;iframe/&gt;\'';
            document.getElementById('displayable-form-informations-container').insertBefore(
                span, document.getElementById('fitem_id_course_introduction_embed'));
        }
    }
    return isValid;
}
