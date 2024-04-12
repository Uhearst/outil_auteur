/**
 *
 */
export function initNotification() {
    let userNotification = document.getElementById('user-notifications');
    if (userNotification) {
        for (let i = 0; i < userNotification.children.length; i++) {
            let validIcon = document.createElement('img');
            if (userNotification.children[i].children.length < 2) {
                if (userNotification.children[i]) {
                    if (userNotification.children[i].classList.contains('alert-danger')) {
                        validIcon.setAttribute('src', './../assets/icone-erreur-25x25.png');
                    } else {
                        validIcon.setAttribute('src', './../assets/icone-succes-25x25.png');
                    }
                    validIcon.setAttribute('class', 'mr-2');
                    if (userNotification.children[i]) {
                        userNotification.children[i].insertBefore(validIcon,
                            userNotification.children[i].querySelector('[type="button"]'));
                    }
                }
            }

            setTimeout(function () {
                if (userNotification.children[i]) {
                    userNotification.children[i].remove();
                }
            }, 10000);
        }
    }
}

/**
 * @param {string} msg
 * @param {int} type
 */
export function addNotification(msg, type) {
    let alertContainer = document.createElement('div');
    let alertButton = document.createElement('button');

    let notificationContainer = document.getElementById('user-notifications');
    notificationContainer.classList.add('custom-user-notifications');

    if (type === 1) {
        alertContainer.setAttribute('class', 'alert alert-success alert-block fade in ');
    } else if (type === 2) {
        alertContainer.setAttribute('class', 'alert alert-danger alert-block fade in ');
    }

    alertContainer.setAttribute('role', 'alert');
    alertContainer.setAttribute('id', 'notificationId');

    alertButton.setAttribute('type', 'button');
    alertButton.setAttribute('class', 'close');
    alertButton.setAttribute('data-dismiss', 'alert');
    alertButton.innerHTML = '';

    alertContainer.appendChild(alertButton);
    alertContainer.innerHTML = alertContainer.innerHTML + msg;

    if (window.$('#user-notifications').children().length === 0) {
        notificationContainer.appendChild(alertContainer);
    }
    window.$('html, body').animate({ scrollTop: 0 }, 'fast');
    alertButton.setAttribute('onclick', 'this.parentNode.remove()');
    initNotification();
}