
document.addEventListener('DOMContentLoaded', () => {

    // =========================
    // MEMBER SEARCH AND FILTERS
    // =========================

    const searchInput = document.getElementById('memberSearch');
    const positionFilter = document.getElementById('positionFilter');
    const adminFilter = document.getElementById('adminFilter');

    const memberRows = document.querySelectorAll('.member-row');
    const memberRowsContainer = document.getElementById('memberRows');

    function filterMembers() {

        const searchValue = searchInput.value.toLowerCase().trim();
        const selectedPosition = positionFilter.value;
        const selectedAdmin = adminFilter.value;

        let visibleCount = 0;

        memberRows.forEach(row => {

            const name = row.dataset.name;
            const userId = row.dataset.userId;
            const position = row.dataset.position;
            const admin = row.dataset.admin;

            const matchesSearch =
                name.includes(searchValue) ||
                userId.includes(searchValue);

            const matchesPosition =
                selectedPosition === 'all' ||
                position === selectedPosition;

            const matchesAdmin =
                selectedAdmin === 'all' ||
                admin === selectedAdmin;

            const shouldShow =
                matchesSearch &&
                matchesPosition &&
                matchesAdmin;

            row.style.display = shouldShow ? '' : 'none';

            if (shouldShow) {
                visibleCount++;
            }
        });

        // Display a message when no members match.
        let emptyRow = document.getElementById('noMatchingMembers');

        if (visibleCount === 0 && memberRows.length > 0) {

            if (!emptyRow && memberRowsContainer) {

                emptyRow = document.createElement('tr');
                emptyRow.id = 'noMatchingMembers';

                const cell = document.createElement('td');
                cell.colSpan = 2;
                cell.textContent = 'No matching members found.';

                emptyRow.appendChild(cell);
                memberRowsContainer.appendChild(emptyRow);
            }

            if (emptyRow) {
                emptyRow.style.display = '';
            }

        } else if (emptyRow) {

            emptyRow.style.display = 'none';

        }
    }

    // Member search and filter listeners.
    if (searchInput && positionFilter && adminFilter) {

        searchInput.addEventListener('input', filterMembers);
        positionFilter.addEventListener('change', filterMembers);
        adminFilter.addEventListener('change', filterMembers);

    }


    // =========================
    // PAST & CANCELLED EVENTS DROPDOWN
    // =========================

    const pastEventsToggle = document.getElementById('pastEventsToggle');
    const pastEventsContent = document.getElementById('pastEventsContent');

    function setPastEventsExpanded(expanded) {

        if (!pastEventsToggle || !pastEventsContent) return;

        pastEventsToggle.setAttribute(
            'aria-expanded',
            String(expanded)
        );

        pastEventsContent.hidden = !expanded;

    }

    if (pastEventsToggle && pastEventsContent) {

        pastEventsToggle.addEventListener('click', function () {

            const isExpanded =
                this.getAttribute('aria-expanded') === 'true';

            setPastEventsExpanded(!isExpanded);

        });

    }


    // =========================
    // SHARED EVENT SEARCH
    // =========================

    const eventSearch = document.getElementById('eventSearch');

    const activeEventsList = document.getElementById('activeEventsList');
    const pastEventsList = document.getElementById('pastEventsList');

    const eventNoResults = document.getElementById('eventNoResults');

    if (eventSearch) {

        eventSearch.addEventListener('input', function () {

            const searchValue = this.value.toLowerCase().trim();

            const eventLists = [
                activeEventsList,
                pastEventsList
            ];

            let totalVisibleEvents = 0;
            let pastEventMatches = false;

            eventLists.forEach((list, index) => {

                if (!list) return;

                const cards = list.querySelectorAll('.event-card');

                cards.forEach(card => {

                    const eventName =
                        (card.dataset.eventName || '').toLowerCase();

                    const matches =
                        eventName.includes(searchValue);

                    card.style.display = matches ? '' : 'none';

                    if (matches) {

                        totalVisibleEvents++;

                        // Check whether a past event matches.
                        if (index === 1) {
                            pastEventMatches = true;
                        }

                    }

                });

            });


            // Automatically expand past events if a matching
            // past or cancelled event is found.
            if (searchValue !== '' && pastEventMatches) {

                setPastEventsExpanded(true);

            }


            // Show one shared no-results message.
            if (eventNoResults) {

                eventNoResults.hidden =
                    totalVisibleEvents !== 0 ||
                    searchValue === '';

            }

        });

    }


    // =========================
    // DELETE EVENT CONFIRMATION
    // =========================

    const deleteModal = document.getElementById('deleteModal');
    const cancelDelete = document.getElementById('cancelDelete');
    const confirmDelete = document.getElementById('confirmDelete');

    let selectedDeleteForm = null;
    let isDeleting = false;

    const deleteForms = document.querySelectorAll('.delete-event-form');

    if (deleteModal && cancelDelete && confirmDelete) {

        // Open the custom modal when Delete is clicked.
        deleteForms.forEach(form => {

            form.addEventListener('submit', function (e) {

                e.preventDefault();

                if (isDeleting) return;

                selectedDeleteForm = this;

                deleteModal.hidden = false;

            });

        });


        // Cancel deletion.
        cancelDelete.addEventListener('click', function () {

            if (isDeleting) return;

            deleteModal.hidden = true;
            selectedDeleteForm = null;

        });


        // Confirm deletion and submit the selected form.
        confirmDelete.addEventListener('click', function () {

            if (!selectedDeleteForm || isDeleting) return;

            isDeleting = true;

            confirmDelete.disabled = true;
            confirmDelete.textContent = 'Deleting...';

            selectedDeleteForm.submit();

        });


        // Close modal when clicking outside the content.
        deleteModal.addEventListener('click', function (e) {

            if (e.target === deleteModal && !isDeleting) {

                deleteModal.hidden = true;
                selectedDeleteForm = null;

            }

        });


        // Close modal using the Escape key.
        document.addEventListener('keydown', function (e) {

            if (
                e.key === 'Escape' &&
                !deleteModal.hidden &&
                !isDeleting
            ) {

                deleteModal.hidden = true;
                selectedDeleteForm = null;

            }

        });

    }

});
