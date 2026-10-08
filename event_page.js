const searchInput = document.getElementById("searchInput");
const eventCards = document.querySelectorAll(".event-card");


// ============================
// FILTER EVENTS
// ============================

function filterEvents() {

  const searchText = searchInput.value.toLowerCase().trim();

  eventCards.forEach(function (card) {

    const eventName = card.querySelector("h2").textContent.toLowerCase();

    if (eventName.includes(searchText)) {
      card.style.display = "";
    } else {
      card.style.display = "none";
    }

  });

}


// ============================
// SEARCH WHILE TYPING
// ============================

searchInput.addEventListener("input", function () {
  filterEvents();
});