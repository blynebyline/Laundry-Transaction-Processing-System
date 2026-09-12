function showNotif(message) {
    let notif = document.getElementById('notif');
    let notifMsg = document.getElementById('notif-message');
    if (notif && notifMsg) {
        notifMsg.textContent = message;
        notif.style.display = 'flex';
        void notif.offsetWidth;
        notif.classList.add('show');
    }
}

function hideNotif() {
    let notif = document.getElementById('notif');
    if (notif) {
        notif.classList.remove('show');
        
        setTimeout(function() {
            notif.style.display = 'none';
        }, 300); 
    }
}
