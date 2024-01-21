"use strict";

// Automatically set the current date
function formatDateToCustom() {
  var today = new Date();
  var day = today.getDate(); // Day without leading zero
  var month = today.getMonth() + 1; // Month is zero-based, add 1
  var year = today.getFullYear().toString().substr(-2); // Last two digits of the year

  var formattedDate = day + "-" + month + "-" + year;
  return formattedDate;
}

document.addEventListener("DOMContentLoaded", (event) => {
  document.getElementById("date").value = formatDateToCustom();
});

// Form submit handler
function submitForm(event) {
  event.preventDefault();
  const form = event.currentTarget;

  // Check if the form has the 'dmt' class
  if (!form.classList.contains("dmt")) {
    return; // Exit the function if the form doesn't have the 'dmt' class
  }

  const honeypotField = form.querySelector("#mobile-phone");
  if (honeypotField && honeypotField.value !== "") {
    // Potentially a bot, do not submit the form
    console.log("Honeypot field was filled out.");
    return; // Prevent form submission
  }

  // Get references
  const phoneField = form.querySelector("[name='phone-number']");
  const nameField = form.querySelector("[name='customer-name']");
  const phoneError = form.querySelector("#phone-error");
  const nameError = form.querySelector("#name-error");

  // Phone validation
  if (phoneField) {
    phoneField.classList.remove("invalid");
    phoneError.textContent = "";
    phoneError.style.display = "none";

    const validationResponse = validatePhone(phoneField.value);
    if (!validationResponse.isValid) {
      displayPhoneError(phoneField, phoneError);
      event.preventDefault();
      return;
    } else {
      phoneField.value = validationResponse.phoneNumber; // Update with formatted number
    }
  }
  // Name validation
  if (nameField) {
    nameField.classList.remove("invalid");
    nameError.textContent = "";
    nameError.style.display = "none";

    // Check if the name field is empty
    if (nameField.value.trim() === "") {
      displayNameError(nameField, nameError, "יש למלא שם תקין");
      return; // Prevent form submission
    }
  }

  // Submit if all validation passed
  if (!phoneError.textContent && !nameError.textContent) {
    submitFormData(form);
  }
}

// Phone helpers
function displayPhoneError(field, error) {
  field.classList.add("invalid");
  error.textContent = "יש לבדוק שמספר הטלפון תקין";
  error.style.display = "block";
}

// Phone validation helpers
function validatePhone(phoneNumber) {
  const localPhoneRegexMobile = /^05\d{8}$/;
  const localPhoneRegexLandline = /^0[23489]\d{7}$/;
  const internationalPhoneRegex = /^\+?972\d{9}$/;

  let isValid =
    localPhoneRegexMobile.test(phoneNumber) ||
    localPhoneRegexLandline.test(phoneNumber) ||
    internationalPhoneRegex.test(phoneNumber);

  if (isValid) {
    if (phoneNumber.startsWith("+972")) {
      phoneNumber = "0" + phoneNumber.slice(4);
    } else if (phoneNumber.startsWith("972")) {
      phoneNumber = "0" + phoneNumber.slice(3);
    }
  }

  return { isValid: isValid, phoneNumber: phoneNumber };
}

// Name validation helper
function displayNameError(field, error) {
  field.classList.add("invalid");
  error.textContent = "יש למלא שם תקין";
  error.style.display = "block";
}

// Form submission
function submitFormData(form) {
  const submitButton = form.querySelector("[type='submit']");
  submitButton.value = "שולח ...";
  submitButton.disabled = true;

  const data = new FormData(form);

  // First fetch request to submit the form data to Google Sheets
  fetch(form.action, {
    method: "POST",
    //    mode: "no-cors",
    body: data,
  })
    .then((response) => response.text())
    .then(() => {
      // If form submission to Google Sheets is successful, proceed to send the email
      const newData = new FormData(form);

      // Second fetch request to send the email using PHP script
      return fetch(ajax_object.ajax_url + "?action=submit_custom_form", {
        method: "POST",
        body: newData,
      });
    })
    .then((response) => response.text())
    .then((emailResponse) => {
      // Handle email sending success
      form.innerHTML = `<div class="success">תודה שפנית אלינו. נחזור אליך בהקדם.</div>`;
    })
    .catch((error) => {
      // Handle any errors
      console.error("Error:", error);
      submitButton.value = "שליחה";
      submitButton.disabled = false;
    });
}
