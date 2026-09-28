const customerModeRadios = document.querySelectorAll('input[name="customer_mode"]');
const newCustomerField = document.getElementById("new-customer-field");
const existingCustomerFld = document.getElementById("existing-customer-field");
const customerNameInput = document.getElementById("customerName");
const customerSelect = document.getElementById("customer_id");
const addressInput = document.getElementById("address");

function updateCustomerMode(mode) {
  if (mode === "existing") {
    newCustomerField.style.display = "none";
    existingCustomerFld.style.display = "";
    customerNameInput.required = false;
    customerSelect.required = true;
  } else {
    newCustomerField.style.display = "";
    existingCustomerFld.style.display = "none";
    customerNameInput.required = true;
    customerSelect.required = false;
  }
}

customerModeRadios.forEach((radio) => {
  radio.addEventListener("change", (e) => updateCustomerMode(e.target.value));
});
updateCustomerMode("new");

customerSelect.addEventListener("change", function () {
  const opt = this.options[this.selectedIndex];
  const addr = opt.getAttribute("data-address") || "";
  if (addr) {
    addressInput.value = addr;
  }
});

const modeRadios = document.querySelectorAll('input[name="mode"]');
const scheduleSection = document.getElementById("schedule-section");
const addressField = document.getElementById("address-field");
const deliveryNoteField = document.getElementById("delivery-note-field");
const scheduleTitle = document.getElementById("schedule-title");
const dateLabel = document.getElementById("date-label");
const timeLabel = document.getElementById("time-label");

const modeConfig = {
  pickup: {
    showAddress: false,
    showDeliveryNote: false,
    scheduleTitle: "PICKUP SCHEDULE",
    dateLabel: "PICKUP DATE",
    timeLabel: "PICKUP TIME",
  },
  delivery: {
    showAddress: true,
    showDeliveryNote: true,
    scheduleTitle: "DELIVERY SCHEDULE",
    dateLabel: "DELIVERY DATE",
    timeLabel: "DELIVERY TIME",
  },
};

function updateMode(mode) {
  const config = modeConfig[mode];
  if (!config) return;

  addressField.style.display = config.showAddress ? "" : "none";
  deliveryNoteField.style.display = config.showDeliveryNote ? "" : "none";
  scheduleTitle.textContent = config.scheduleTitle;
  dateLabel.textContent = config.dateLabel;
  timeLabel.textContent = config.timeLabel;
  addressInput.required = config.showAddress;
}

modeRadios.forEach((radio) => {
  radio.addEventListener("change", (e) => updateMode(e.target.value));
});

const initialMode = document.querySelector('input[name="mode"]:checked');
if (initialMode) updateMode(initialMode.value);
