export const init = () => {

    const progressBar = document.getElementById("inner-progress-bar");
    const steps = document.querySelectorAll(".step");

    window.onresize = updateSize;

    let n = window.location.pathname.lastIndexOf('/');
    let result = window.location.pathname.substring(n + 1);
    let active = 1;
    if (result.includes('course')) {
        active = 1;
    } else if (result.includes('section') || result.includes('evaluation')) {
        active = 2;
    } else if (result.includes('subquestion') ||
        result.includes('exploration') ||
        result.includes('resource')) {
        active = 3;
    }

    updateProgress();


    /**
     *
     */
    function updateProgress() {
        steps.forEach((step, i) => {
            if (i < active) {
                step.classList.add("active");
            } else {
                step.classList.remove("active");
            }
        });
        updateSize();
    }

    /**
     *
     */
    function updateSize() {
        if (window.innerWidth <= 400) {
            progressBar.style.width = ((active - 1) / (steps.length - 1)) * 82 + "%";
        } else if (window.innerWidth > 400 && window.innerWidth < 768) {
            progressBar.style.width = ((active - 1) / (steps.length - 1)) * 91 + "%";
        } else if (window.innerWidth >= 768) {
            progressBar.style.width = ((active - 1) / (steps.length - 1)) * 97 + "%";
        }
    }

};