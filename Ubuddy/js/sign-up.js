console.log("Ubuddy is working!");

// ----- Show / hide password (one eye controls both fields on signup) -----
const toggleBtn = document.getElementById("toggle-password");

    if (toggleBtn) {
        toggleBtn.addEventListener("click", () => {
            const passwordField = document.getElementById("password");
            const confirmField = document.getElementById("confirm_password"); // null on the login page

            const showing = passwordField.type === "text";
            const newType = showing ? "password" : "text";

            passwordField.type = newType;
            if (confirmField) confirmField.type = newType;

            toggleBtn.textContent = showing ? "👁" : "◡";
            toggleBtn.setAttribute("aria-label", showing ? "Show password" : "Hide password");
    });
}

// ----- Password match check (signup page only) -----
const form = document.getElementById("signupForm");
const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirm_password");
const message = document.getElementById("matchMessage");

if (form && password && confirmPassword) {

    function checkMatch() {
        // stay quiet until they start typing in the confirm box
        if (confirmPassword.value === "") {
            message.textContent = "";
            message.className = "form-message";
            return true;
        }

        if (password.value === confirmPassword.value) {
            message.textContent = "Passwords match";
            message.className = "form-message ok";
            return true;
        }

        message.textContent = "Passwords do not match";
        message.className = "form-message error";
        return false;
    }

    // live feedback while typing in either box
    password.addEventListener("input", checkMatch);
    confirmPassword.addEventListener("input", checkMatch);

    // block submission if they differ
    form.addEventListener("submit", (e) => {
        if (!checkMatch() || confirmPassword.value === "") {
            e.preventDefault();
            if (confirmPassword.value === "") {
                message.textContent = "Please confirm your password";
                message.className = "form-message error";
            }
            confirmPassword.focus();
        }
    });
}
