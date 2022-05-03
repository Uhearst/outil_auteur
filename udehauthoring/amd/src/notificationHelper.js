/**
 *
 */
export function initNotification() {
    let userNotification = document.getElementById('user-notifications');
    userNotification.style.color = 'white';
    if (userNotification) {
        let validIcon = document.createElement('img');
        if(userNotification.firstElementChild) {
            if(userNotification.firstElementChild.classList.contains('alert-danger')) {
                validIcon.setAttribute('src', './../assets/icone-erreur-25x25.png');
            } else {
                validIcon.setAttribute('src', './../assets/icone-succes-25x25.png');
            }
            validIcon.setAttribute('class', 'mr-2');
            if(userNotification.firstElementChild) {
                userNotification.firstElementChild.insertBefore(validIcon, userNotification.querySelector('[type="button"]'));
            }
        }
        setTimeout(function() {
            userNotification.style.display = "none";
            }, 20000);
    }
}

/**
 * @param {string} msg
 */
export function addIssueNotification(msg) {
    let alertContainer = document.createElement('div');
    let alertImg = document.createElement('img');
    let alertbutton = document.createElement('button');

    let notificationContainer = document.getElementById('user-notifications');

    alertContainer.setAttribute('class', 'alert alert-danger alert-block fade in ');
    alertContainer.setAttribute('role', 'alert');

    alertImg.setAttribute('class', 'mr-2');
    alertImg.setAttribute('src', './../assets/icone-erreur-25x25.png');

    alertbutton.setAttribute('type', 'button');
    alertbutton.setAttribute('class', 'close');
    alertbutton.setAttribute('data-dismiss', 'alert');
    alertbutton.innerHTML = 'x';

    alertContainer.appendChild(alertImg);
    alertContainer.appendChild(alertbutton);
    alertContainer.innerHTML = msg;
    notificationContainer.appendChild(alertContainer);
    window.$('html, body').animate({scrollTop: 0}, 'fast');
    alertbutton.setAttribute('onclick', 'this.parentNode.remove()');
}