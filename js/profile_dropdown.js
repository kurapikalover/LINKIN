
document.addEventListener("DOMContentLoaded", function () {

    /* =========================
       PROFILE DROPDOWN
    ========================= */

    const profileButton = document.getElementById("profileDropdownButton");
    const profileMenu = document.getElementById("profileDropdownMenu");

    if (profileButton && profileMenu) {

        function closeProfileMenu() {
            profileMenu.hidden = true;
            profileButton.setAttribute("aria-expanded", "false");
        }

        profileButton.addEventListener("click", function (event) {
            event.stopPropagation();

            profileMenu.hidden = !profileMenu.hidden;

            profileButton.setAttribute(
                "aria-expanded",
                String(!profileMenu.hidden)
            );
        });

        document.addEventListener("click", function (event) {
            if (!event.target.closest("#profileDropdown")) {
                closeProfileMenu();
            }
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                closeProfileMenu();
                profileButton.focus();
            }
        });
    }


    /* =========================
       COMMITTEE ADMIN DROPDOWN
    ========================= */

    const committeeButton = document.getElementById("committeeAdminButton");
    const committeeMenu = document.getElementById("committeeAdminMenu");

    if (committeeButton && committeeMenu) {

        function closeCommitteeMenu() {
            committeeMenu.hidden = true;

            committeeButton.setAttribute(
                "aria-expanded",
                "false"
            );
        }

        committeeButton.addEventListener("click", function (event) {
            event.stopPropagation();

            committeeMenu.hidden = !committeeMenu.hidden;

            committeeButton.setAttribute(
                "aria-expanded",
                String(!committeeMenu.hidden)
            );
        });

        document.addEventListener("click", function (event) {
            if (!event.target.closest(".committee-admin-dropdown")) {
                closeCommitteeMenu();
            }
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                closeCommitteeMenu();
                committeeButton.focus();
            }
        });
    }

});
