const searchInput = document.getElementById("searchInput");
const filterBtn = document.getElementById("filterBtn");
const filterMenu = document.getElementById("filterMenu");
const filterOptions = document.querySelectorAll(".filter-option");
const eventCards = document.querySelectorAll(".event-card");

let selectedCategory = "all";


// ============================
// SHOW / HIDE FILTER MENU
// ============================

filterBtn.addEventListener("click", function () {
  filterMenu.classList.toggle("show");
});


// ============================
// FILTER EVENTS
// ============================

function filterEvents() {

  const searchText = searchInput.value.toLowerCase().trim();

  let visibleEvents = 0;

  eventCards.forEach(function (card) {

    const eventName = card.querySelector("h2").textContent.toLowerCase();

    const category = card
      .querySelector(".category")
      .textContent
      .trim();

    const matchesSearch = eventName.includes(searchText);

    const matchesCategory =
      selectedCategory === "all" ||
      category === selectedCategory;

    if (matchesSearch && matchesCategory) {

      card.style.display = "";

      visibleEvents++;

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


// ============================
// CATEGORY FILTER
// ============================

filterOptions.forEach(function (option) {

  option.addEventListener("click", function () {

    selectedCategory = option.dataset.category;

    filterOptions.forEach(function (button) {
      button.classList.remove("active");
    });

    option.classList.add("active");

    filterEvents();

  });

});