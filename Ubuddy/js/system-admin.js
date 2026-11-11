const roleSelect = document.getElementById("roleSelect");
const clubField  = document.getElementById("clubField");
const clubSelect = document.getElementById("clubSelect");

roleSelect.addEventListener("change", () => {
  const needsClub = roleSelect.value === "committee_admin";

  clubField.hidden = !needsClub;
  clubSelect.required = needsClub;      // only required while visible

  if (!needsClub) {
    clubSelect.selectedIndex = 0;       // reset, so a stale club isn't submitted
  }
});