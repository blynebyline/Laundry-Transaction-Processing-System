const modeRadios = document.querySelectorAll('input[name="mode"]');
const scheduleSection = document.getElementById('schedule-section');
const addressField = document.getElementById('address-field');
const scheduleTitle = document.getElementById('schedule-title');
const dateLabel = document.getElementById('date-label');
const timeLabel = document.getElementById('time-label');
const addressInput = document.getElementById('address');

const modeConfig = {
    'pickup': {
        showSchedule: true,
        showAddress: false,
        scheduleTitle: 'PICKUP SCHEDULE',
        dateLabel: 'PICKUP DATE',
        timeLabel: 'PICKUP TIME'
    },
    'delivery': {
        showSchedule: true,
        showAddress: true,
        scheduleTitle: 'DELIVERY SCHEDULE',
        dateLabel: 'DELIVERY DATE',
        timeLabel: 'DELIVERY TIME'
    }
};

function updateMode(mode) {
    const config = modeConfig[mode];
    if (!config) return;

    scheduleSection.style.display = config.showSchedule ? '' : 'none';

    if (config.showSchedule) {
        addressField.style.display = config.showAddress ? '' : 'none';
        scheduleTitle.textContent = config.scheduleTitle;
        dateLabel.textContent = config.dateLabel;
        timeLabel.textContent = config.timeLabel;
        addressInput.required = config.showAddress;
    } else {
        addressInput.required = false;
    }
}

modeRadios.forEach(function (radio) {
    radio.addEventListener('change', function (e) {
        updateMode(e.target.value);
    });
});

// this the one in the statr 

const initialMode = document.querySelector('input[name="mode"]:checked');
if (initialMode) {
    updateMode(initialMode.value);
}


