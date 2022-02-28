/**
 *
 */
export function init() {
    let userNotification = document.getElementById('user-notifications');
    userNotification.style.color = 'white';
    if (userNotification) {
        let validIcon = document.createElement('img');
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
}