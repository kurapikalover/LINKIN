console.log("Ubuddy is working!");

const passwordInput = document.getElementById("password");
const togglePassword = document.getElementById("toggle-password");

togglePassword.addEventListener("click", function () {

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        togglePassword.textContent = "◡";
    } else {
        passwordInput.type = "password";
        togglePassword.textContent = "👁";
    }

});