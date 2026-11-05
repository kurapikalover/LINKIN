document.addEventListener('DOMContentLoaded', () => {

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

            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.id = 'noMatchingMembers';

                const cell = document.createElement('td');
                cell.colSpan = 2;
                cell.textContent = 'No matching members found.';

                emptyRow.appendChild(cell);
                memberRowsContainer.appendChild(emptyRow);
            }

            emptyRow.style.display = '';

        } else if (emptyRow) {
            emptyRow.style.display = 'none';
        }
    }

    searchInput.addEventListener('input', filterMembers);
    positionFilter.addEventListener('change', filterMembers);
    adminFilter.addEventListener('change', filterMembers);

});