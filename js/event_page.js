
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const filterBtn = document.getElementById("toggleFilter");
    const filterPanel = document.getElementById("filterPanel");
    const resetBtn = document.getElementById("resetFilters");
    const statusFilter = document.getElementById("eventStatusFilter");
    const sortFilter = document.getElementById("eventSortFilter");
    const eventGrid = document.querySelector(".event-grid");
    const noResults = document.getElementById("noResults");

    if (!eventGrid) {
        console.warn("Event grid not found.");
        return;
    }

    const eventCards = [...eventGrid.querySelectorAll(".event-card")];

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

    // Search and filter events
    function filterEvents() {
        const searchText = normalise(searchInput?.value);
        const selectedStatus = normalise(statusFilter?.value || "all");
        const sortOrder = sortFilter?.value || "latest";

        eventCards.forEach(function (card) {
            // Search both event name and club name
            const searchableText = normalise(
                card.dataset.search ||
                (
                    (card.querySelector(".event-name")?.textContent || "") +
                    " " +
                    (card.querySelector(".event-club-name")?.textContent || "")
                )
            );

            const status = normalise(
                card.dataset.status ||
                card.querySelector(".event-status")?.textContent
            );

            const matchesSearch = searchableText.includes(searchText);

            const matchesStatus =
                selectedStatus === "all" ||
                (
                    selectedStatus === "active" &&
                    ["upcoming", "ongoing"].includes(status)
                ) ||
                status === selectedStatus;

            card.hidden = !(matchesSearch && matchesStatus);
        });

        // Sort events by start date
        eventCards.sort(function (a, b) {
            const dateA = a.dataset.startDate || "";
            const dateB = b.dataset.startDate || "";
            const comparison = dateA.localeCompare(dateB);

            return sortOrder === "oldest"
                ? comparison
                : -comparison;
        });

        // Reorder cards in the event grid
        eventCards.forEach(function (card) {
            eventGrid.appendChild(card);
        });

        // Show the no-results message if no events match
        const visibleCount = eventCards.filter(
            card => !card.hidden
        ).length;

        if (noResults) {
            noResults.hidden = visibleCount > 0;
        }
    }

    // Search while typing
    if (searchInput) {
        searchInput.addEventListener("input", filterEvents);
    }

    // Filter by status
    if (statusFilter) {
        statusFilter.addEventListener("change", filterEvents);
    }

    // Sort by date
    if (sortFilter) {
        sortFilter.addEventListener("change", filterEvents);
    }

    // Reset search, status, and sorting
    if (resetBtn) {
        resetBtn.addEventListener("click", function () {
            if (searchInput) {
                searchInput.value = "";
            }

            if (statusFilter) {
                statusFilter.value = "all";
            }

            if (sortFilter) {
                sortFilter.value = "latest";
            }

            filterEvents();
        });
    }

    // Show all statuses by default
    if (statusFilter) {
        statusFilter.value = "all";
    }

    // Apply the default filters
    filterEvents();
});
