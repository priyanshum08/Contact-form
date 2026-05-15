// ── Validation helpers ────────────────────────────────────────────────────────

function showError(fieldId, errorId, msg) {
  const field = document.getElementById(fieldId);
  const err = document.getElementById(errorId);
  field.classList.add("invalid");
  err.textContent = msg;
  return false;
}

function clearError(fieldId, errorId) {
  const field = document.getElementById(fieldId);
  const err = document.getElementById(errorId);
  field.classList.remove("invalid");
  err.textContent = "";
}

function validateEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function validatePhone(phone) {
  if (!phone) return true; // optional
  // Indian mobile: 10 digits, starting with 6-9
  const digits = phone.replace(/\D/g, "");
  return /^[6-9]\d{9}$/.test(digits);
}

function validateForm() {
  let valid = true;

  // Name
  const name = document.getElementById("name").value.trim();
  clearError("name", "nameError");
  if (!name) {
    valid = showError("name", "nameError", "Full name is required.");
  } else if (name.length < 2) {
    valid = showError(
      "name",
      "nameError",
      "Name must be at least 2 characters.",
    );
  }

  // Email
  const email = document.getElementById("email").value.trim();
  clearError("email", "emailError");
  if (!email) {
    valid = showError("email", "emailError", "Email address is required.");
  } else if (!validateEmail(email)) {
    valid = showError(
      "email",
      "emailError",
      "Please enter a valid email address.",
    );
  }

  // Phone (optional)
  const phone = document.getElementById("phone").value.trim();
  clearError("phone", "phoneError");
  if (phone && !validatePhone(phone)) {
    valid = showError(
      "phone",
      "phoneError",
      "Enter a valid 10-digit Indian mobile number.",
    );
  }

  // Message
  const message = document.getElementById("message").value.trim();
  clearError("message", "messageError");
  if (!message) {
    valid = showError("message", "messageError", "Message is required.");
  } else if (message.length < 10) {
    valid = showError(
      "message",
      "messageError",
      "Message must be at least 10 characters.",
    );
  }

  return valid;
}

// ── Live validation on blur ────────────────────────────────────────────────────

["name", "email", "phone", "message"].forEach((id) => {
  const el = document.getElementById(id);
  if (!el) return;
  el.addEventListener("blur", () => {
    validateForm(); // re-run silently to update state
  });
  el.addEventListener("input", () => {
    if (el.classList.contains("invalid")) validateForm();
  });
});

// ── Form submission ───────────────────────────────────────────────────────────

document
  .getElementById("contactForm")
  .addEventListener("submit", async function (e) {
    e.preventDefault();

    if (!validateForm()) return;

    const btn = document.getElementById("submitBtn");
    const btnText = btn.querySelector(".btn-text");
    const btnLoader = btn.querySelector(".btn-loader");
    const btnArrow = btn.querySelector(".btn-arrow");

    // Loading state
    btn.disabled = true;
    btnText.textContent = "Sending…";
    btnLoader.hidden = false;
    btnArrow.hidden = true;

    const formData = new FormData();
    formData.append("name", document.getElementById("name").value.trim());
    formData.append("email", document.getElementById("email").value.trim());
    const rawPhone = document.getElementById("phone").value.trim();
    formData.append("phone", rawPhone ? "+91 " + rawPhone : "");
    formData.append("subject", document.getElementById("subject").value.trim());
    formData.append("message", document.getElementById("message").value.trim());

    try {
      const response = await fetch("php/submit_form.php", {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.status === "success") {
        document.getElementById("contactForm").hidden = true;
        document.getElementById("successPanel").hidden = false;
      } else {
        alert(
          "Error: " +
            (data.message || "Something went wrong. Please try again."),
        );
        resetButton();
      }
    } catch (err) {
      console.error(err);
      alert("Network error. Please check your connection and try again.");
      resetButton();
    }

    function resetButton() {
      btn.disabled = false;
      btnText.textContent = "Send Message";
      btnLoader.hidden = true;
      btnArrow.hidden = false;
    }
  });

// ── Reset form ────────────────────────────────────────────────────────────────

function resetForm() {
  document.getElementById("contactForm").reset();
  document.getElementById("contactForm").hidden = false;
  document.getElementById("successPanel").hidden = true;

  const btn = document.getElementById("submitBtn");
  const btnText = btn.querySelector(".btn-text");
  const btnLoader = btn.querySelector(".btn-loader");
  const btnArrow = btn.querySelector(".btn-arrow");
  btn.disabled = false;
  btnText.textContent = "Send Message";
  btnLoader.hidden = true;
  btnArrow.hidden = false;

  ["name", "email", "phone", "message"].forEach((id) => {
    clearError(id, id + "Error");
  });
}
