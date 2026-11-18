
<?php
$filterPage = $filterPage ?? 'home';
?>

<div class="home-filter-panel" id="filterPanel" hidden>

    <div class="home-filter-heading">
        <h2>Filter & Sort</h2>

        <button type="button" id="resetFilters" class="btn-small">
            Reset
        </button>
    </div>

    <div class="home-filter-groups">

        <?php if ($filterPage === 'home' || $filterPage === 'clubs'): ?>

            <!-- CLUB FILTERS -->
            <div class="home-filter-group">
                <h3>Clubs</h3>

                <label for="clubCategoryFilter">Category</label>

                <select id="clubCategoryFilter">
                    <option value="all">All categories</option>
                </select>
            </div>

        <?php endif; ?>


        <?php if ($filterPage === 'home' || $filterPage === 'events'): ?>

            <!-- EVENT FILTERS -->
            <div class="home-filter-group">
                <h3>Events</h3>

                <label for="eventStatusFilter">Status</label>

                <select id="eventStatusFilter">
                    <option value="active">Upcoming & ongoing</option>
                    <option value="all">All statuses</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <label for="eventSortFilter">Sort by start date</label>

                <select id="eventSortFilter">
                    <option value="latest">Latest first</option>
                    <option value="oldest">Oldest first</option>
                </select>
            </div>

        <?php endif; ?>

    </div>
</div>

