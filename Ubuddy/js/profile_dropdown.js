
document.addEventListener("DOMContentLoaded", function () {
    const button = document.getElementById("profileDropdownButton");
    const menu = document.getElementById("profileDropdownMenu");

    if (!button || !menu) return;

    function closeMenu() {
        menu.hidden = true;
        button.setAttribute("aria-expanded", "false");
    }

    button.addEventListener("click", function (event) {
        event.stopPropagation();

        menu.hidden = !menu.hidden;
        button.setAttribute(
            "aria-expanded",
            String(!menu.hidden)
        );
    });

    document.addEventListener("click", function (event) {
        if (!event.target.closest("#profileDropdown")) {
            closeMenu();
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeMenu();
            button.focus();
        }
    });
});
