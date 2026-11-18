
document.addEventListener("DOMContentLoaded", function () {
  // ===== GET HTML ELEMENTS =====

  const searchInput = document.getElementById("searchInput");

  const filterBtn =
    document.getElementById("toggleFilter") ||
    document.getElementById("toggleHomeFilter");

  const filterPanel =
    document.getElementById("filterPanel") ||
    document.getElementById("homeFilterPanel");

  const resetBtn =
    document.getElementById("resetFilters") ||
    document.getElementById("resetHomeFilters");

  const categoryFilter =
    document.getElementById("clubCategoryFilter");

  const statusFilter =
    document.getElementById("eventStatusFilter");

  const sortFilter =
    document.getElementById("eventSortFilter");

  const clubsRow = document.getElementById("clubsRow");
  const eventsRow = document.getElementById("eventsRow");


  // ===== HELPER FUNCTION =====

  function normalise(value) {
    return String(value ?? "").trim().toLowerCase();
  }


  // ===== SHOW / HIDE FILTER PANEL =====

  if (filterBtn && filterPanel) {
    filterBtn.addEventListener("click", function () {
      filterPanel.hidden = !filterPanel.hidden;

      filterBtn.setAttribute(
        "aria-expanded",
        String(!filterPanel.hidden)
      );
    });
  }


  // ===== POPULATE CATEGORY DROPDOWN =====

  function populateCategories() {
    if (!categoryFilter) return;

    const previousValue = categoryFilter.value || "all";

    categoryFilter.innerHTML =
      '<option value="all">All categories</option>';

    const categories = [
      ...new Set(
        [
          ...(clubsRow?.querySelectorAll(".club-card") || [])
        ].map(card =>
          (card.dataset.category || "Uncategorised").trim()
        )
      )
    ].sort((a, b) => a.localeCompare(b));

    categories.forEach(category => {
      const option = document.createElement("option");

      option.value = category;
      option.textContent = category;

      categoryFilter.appendChild(option);
    });

    const matchingOption = [
      ...categoryFilter.options
    ].find(option =>
      normalise(option.value) === normalise(previousValue)
    );

    categoryFilter.value = matchingOption
      ? matchingOption.value
      : "all";
  }


  // ===== APPLY SEARCH AND FILTERS =====

  function applyFilters() {
    const query = normalise(searchInput?.value);

    const selectedCategory = normalise(
      categoryFilter?.value || "all"
    );

    const selectedStatus = normalise(
      statusFilter?.value || "active"
    );

    const sortOrder = normalise(
      sortFilter?.value || "latest"
    );


    // ===== FILTER CLUBS =====

    if (clubsRow) {
      const clubCards = [
        ...clubsRow.querySelectorAll(".club-card")
      ];

      clubCards.forEach(card => {
        const text = normalise(
          card.dataset.search || card.textContent
        );

        const category = normalise(
          card.dataset.category || "Uncategorised"
        );

        const matchesSearch = text.includes(query);

        const matchesCategory =
          selectedCategory === "all" ||
          category === selectedCategory;

        card.hidden = !(
          matchesSearch && matchesCategory
        );
      });
    }


    // ===== FILTER AND SORT EVENTS =====

    if (eventsRow) {
      const eventCards = [
        ...eventsRow.querySelectorAll(".event-card")
      ];

      eventCards.forEach(card => {
        const text = normalise(
          card.dataset.search || card.textContent
        );

        const status = normalise(
          card.dataset.status ||
          card.querySelector(".event-status")?.textContent
        );

        const matchesSearch = text.includes(query);

        let matchesStatus;

        if (selectedStatus === "all") {
          matchesStatus = true;
        } else if (selectedStatus === "active") {
          matchesStatus = [
            "upcoming",
            "ongoing"
          ].includes(status);
        } else {
          matchesStatus = status === selectedStatus;
        }

        card.hidden = !(
          matchesSearch && matchesStatus
        );
      });


      // Sort events by start date
      eventCards.sort((a, b) => {
        const dateA = a.dataset.startDate || "";
        const dateB = b.dataset.startDate || "";

        // Put events without dates at the end
        if (!dateA && !dateB) return 0;
        if (!dateA) return 1;
        if (!dateB) return -1;

        const comparison = dateA.localeCompare(dateB);

        return sortOrder === "oldest"
          ? comparison
          : -comparison;
      });

      eventCards.forEach(card => {
        eventsRow.appendChild(card);
      });
    }
  }


  // ===== EVENT LISTENERS =====

  searchInput?.addEventListener(
    "input",
    applyFilters
  );

  categoryFilter?.addEventListener(
    "change",
    applyFilters
  );

  statusFilter?.addEventListener(
    "change",
    applyFilters
  );

  sortFilter?.addEventListener(
    "change",
    applyFilters
  );


  // ===== RESET FILTERS =====

  resetBtn?.addEventListener("click", function () {
    if (searchInput) {
      searchInput.value = "";
    }

    if (categoryFilter) {
      categoryFilter.value = "all";
    }

    if (statusFilter) {
      statusFilter.value = "active";
    }

    if (sortFilter) {
      sortFilter.value = "latest";
    }

    applyFilters();
  });


  // ===== INITIALISE FILTERS =====

  populateCategories();
  applyFilters();
});
