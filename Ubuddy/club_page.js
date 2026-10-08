const searchInput = document.getElementById("searchInput");
const filterBtn = document.getElementById("filterBtn");
const filterMenu = document.getElementById("filterMenu");
const filterOptions = document.querySelectorAll(".filter-option");
const clubCards = document.querySelectorAll(".club-card");

let selectedCategory = "all";


// ============================
// SHOW / HIDE FILTER MENU
// ============================

filterBtn.addEventListener("click", function () {
  filterMenu.classList.toggle("show");
});


// ============================
// FILTER CLUBS
// ============================

function filterClubs() {

  const searchText = searchInput.value.toLowerCase().trim();

  let visibleClubs = 0;

  clubCards.forEach(function (card) {

    const clubName = card.querySelector("h2").textContent.toLowerCase();

    const category = card
      .querySelector(".category")
      .textContent
      .trim();

    const matchesSearch = clubName.includes(searchText);

    const matchesCategory =
      selectedCategory === "all" ||
      category === selectedCategory;

    if (matchesSearch && matchesCategory) {

      card.style.display = "";

      visibleClubs++;

    } else {

      card.style.display = "none";

    }

  });

}


// ============================
// SEARCH WHILE TYPING
// ============================

searchInput.addEventListener("input", function () {
  filterClubs();
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

    filterClubs();

  });

});