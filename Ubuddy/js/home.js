
 // ===== HOMEPAGE CLUBS AND EVENTS =====

const clubs = window.homeClubs || [];
const events = window.homeEvents || [];

const clubsRow = document.getElementById("clubsRow");
const eventsRow = document.getElementById("eventsRow");

// Escape HTML to safely display database values
function escapeHTML(value) {
  return String(value ?? "").replace(/[&<>"']/g, character => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;"
  })[character]);
}

// Get CSS class for each club category
function getClubCategoryClass(categoryName) {
  const category = String(categoryName || "").toLowerCase();

  if (category.includes("sports")) return "category-sports";
  if (category.includes("culture")) return "category-culture";
  if (category.includes("recreational")) return "category-recreational";
  if (category.includes("arts")) return "category-arts";
  if (category.includes("religious")) return "category-religious";
  if (category.includes("community service")) return "category-community";

  return "";
}

// Get CSS class for each event status
function getEventStatusClass(status) {
  const value = String(status || "").toLowerCase();

  if (value === "upcoming") return "status-upcoming";
  if (value === "ongoing") return "status-ongoing";
  if (value === "completed") return "status-completed";
  if (value === "cancelled") return "status-cancelled";

  return "status-default";
}

// Create a club card
function renderClubCard(club) {
  const rawClubName = club.club_name || "Unnamed Club";
  const rawCategoryName = club.category_name || "Uncategorised";

  const clubName = escapeHTML(rawClubName);
  const categoryName = escapeHTML(rawCategoryName);
  const categoryClass = getClubCategoryClass(rawCategoryName);
  const clubId = encodeURIComponent(club.club_id ?? "");

  return `
    <a
      href="../pages/club.php?id=${clubId}"
      class="club-card"
      data-category="${categoryName}"
      data-search="${escapeHTML(
        `${rawClubName} ${rawCategoryName}`
      )}"
    >
      <div class="club-card-image">
        <div class="club-image-placeholder">Club Image</div>
      </div>

      <div class="club-card-content">
        <h2 class="club-name">${clubName}</h2>

        <p class="category ${categoryClass}">
          ${categoryName}
        </p>
      </div>
    </a>
  `;
}

// Create an event card

function renderEventCard(event) {
  const rawEventName = event.event_name || "Unnamed Event";
  const rawClubName = event.club_name || "Unknown club";
  const status = String(event.event_status || "").toLowerCase();

  const eventName = escapeHTML(rawEventName);
  const clubName = escapeHTML(rawClubName);

  const statusLabel = status
    ? status.charAt(0).toUpperCase() + status.slice(1)
    : "Unknown";

  const eventId = encodeURIComponent(event.event_id ?? "");

  const imageHTML = event.event_image
    ? `
      <img
        src="${escapeHTML(event.event_image)}"
        alt="${eventName}"
        onerror="this.remove()"
      >
    `
    : `
      <div class="event-image-placeholder">
        Event Image
      </div>
    `;

  return `
    <a
      href="../pages/event_detail.php?id=${eventId}"
      class="event-card"
      data-search="${escapeHTML(
        `${rawEventName} ${rawClubName}`
      )}"
      data-status="${escapeHTML(status)}"
      data-start-date="${escapeHTML(event.start_date || "")}"
      aria-label="View details for ${eventName}"
    >
      <div class="event-card-image">
        ${imageHTML}
      </div>

      <div class="event-card-content">
        <h2 class="event-name">${eventName}</h2>

        <p class="event-club-name">${clubName}</p>

        <div class="event-status-row">
          <span class="event-status ${getEventStatusClass(status)}">
            ${escapeHTML(statusLabel)}
          </span>
        </div>
      </div>
    </a>
  `;
}


// Render all club and event cards
function renderCards() {
  if (clubsRow) {
    clubsRow.innerHTML = clubs.length
      ? clubs.map(renderClubCard).join("")
      : "<p>No clubs are available yet.</p>";
  }

  if (eventsRow) {
    eventsRow.innerHTML = events.length
      ? events.map(renderEventCard).join("")
      : "<p>No events are available yet.</p>";
  }
}

// Run the rendering function
renderCards();
