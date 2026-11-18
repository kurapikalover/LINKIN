
document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("searchInput");
  const filterBtn = document.getElementById("toggleFilter");
  const filterPanel = document.getElementById("filterPanel");
  const resetBtn = document.getElementById("resetFilters");
  const categoryFilter = document.getElementById("clubCategoryFilter");
  const clubCards = [
    ...document.querySelectorAll(".club-grid .club-card")
  ];
  const noResults = document.getElementById("noResults");

  function normalise(value) {
    return String(value ?? "").trim().toLowerCase();
  }

  // Show or hide the filter panel
  if (filterBtn && filterPanel) {
    filterBtn.addEventListener("click", function () {
      filterPanel.hidden = !filterPanel.hidden;

      filterBtn.setAttribute(
        "aria-expanded",
        String(!filterPanel.hidden)
      );
    });
  }

  // Populate category dropdown
  if (categoryFilter) {
    const previousValue = categoryFilter.value || "all";

    const categories = [
      ...new Set(
        clubCards.map(card =>
          card.dataset.category?.trim() || "Uncategorised"
        )
      )
    ].sort((a, b) => a.localeCompare(b));

    categoryFilter.innerHTML =
      '<option value="all">All categories</option>';

    categories.forEach(category => {
      const option = document.createElement("option");
      option.value = category;
      option.textContent = category;
      categoryFilter.appendChild(option);
    });

    const matchingOption = [...categoryFilter.options].find(
      option => normalise(option.value) === normalise(previousValue)
    );

    categoryFilter.value = matchingOption
      ? matchingOption.value
      : "all";
  }

  // Search and filter clubs
  function filterClubs() {
    const searchText = normalise(searchInput?.value);
    const selectedCategory = normalise(
      categoryFilter?.value || "all"
    );

    let visibleCount = 0;

    clubCards.forEach(card => {
      const searchableText = normalise(
        card.dataset.search || card.textContent
      );

      const category = normalise(
        card.dataset.category || "Uncategorised"
      );

      const matchesSearch = searchableText.includes(searchText);

      const matchesCategory =
        selectedCategory === "all" ||
        category === selectedCategory;

      const isVisible = matchesSearch && matchesCategory;

      card.hidden = !isVisible;

      if (isVisible) visibleCount++;
    });

    if (noResults) {
      noResults.hidden = visibleCount > 0;
    }
  }

  // Attach search listener
  if (searchInput) {
    searchInput.addEventListener("input", filterClubs);
  }

  // Attach category listener
  if (categoryFilter) {
    categoryFilter.addEventListener("change", filterClubs);
  }

  // Reset search and filters
  if (resetBtn) {
    resetBtn.addEventListener("click", function () {
      if (searchInput) searchInput.value = "";
      if (categoryFilter) categoryFilter.value = "all";

      filterClubs();
    });
  }

  // Initial display
  filterClubs();
});
