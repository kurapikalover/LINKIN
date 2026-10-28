
 // ===== NEWS CAROUSEL =====

const newsItems = window.latestNewsItems || [];

let currentNews = 0;
let newsTimer = null;

const newsBanner = document.getElementById("newsBanner");
const newsTitle = document.getElementById("newsTitle");
const newsText = document.getElementById("newsText");
const newsType = document.getElementById("newsType");
const newsCounter = document.getElementById("newsCounter");
const newsDots = document.getElementById("newsDots");
const newsPrev = document.getElementById("newsPrev");
const newsNext = document.getElementById("newsNext");
const newsDetailsBtn = document.getElementById("newsDetailsBtn");

const bannerColors = [
  ["#172A46", "#263F63"],
  ["#1D3557", "#426FB1"],
  ["#263F63", "#648BC2"],
  ["#172A46", "#426FB1"]
];

function showNews() {
  if (
    !newsTitle ||
    !newsText ||
    !newsType ||
    !newsCounter ||
    !newsDots ||
    !newsPrev ||
    !newsNext ||
    !newsDetailsBtn ||
    !newsBanner
  ) {
    return;
  }

  if (newsItems.length === 0) {
    newsTitle.textContent = "Stay tuned!";
    newsText.textContent =
      "There are no announcements or upcoming events right now.";

    newsType.textContent = "Latest News";
    newsCounter.textContent = "00 / 00";
    newsDots.innerHTML = "";

    newsPrev.disabled = true;
    newsNext.disabled = true;
    newsDetailsBtn.disabled = true;
    newsDetailsBtn.onclick = null;

    return;
  }

  const item = newsItems[currentNews];

  newsTitle.textContent = item.title || "Latest News";
  newsText.textContent = item.message || "";

  newsType.textContent =
    item.item_type === "event" ? "Upcoming Event" : "Announcement";

  newsCounter.textContent =
    `${String(currentNews + 1).padStart(2, "0")} / ${String(newsItems.length).padStart(2, "0")}`;

  const colors = bannerColors[currentNews % bannerColors.length];

  newsBanner.style.setProperty("--news-start", colors[0]);
  newsBanner.style.setProperty("--news-end", colors[1]);

  newsDots.innerHTML = newsItems
    .map((_, index) => `
      <button
        type="button"
        class="dot ${index === currentNews ? "active" : ""}"
        aria-label="Show news item ${index + 1}"
        aria-current="${index === currentNews ? "true" : "false"}"
      ></button>
    `)
    .join("");

  newsDots.querySelectorAll(".dot").forEach((dot, index) => {
    dot.addEventListener("click", () => {
      currentNews = index;
      showNews();
      restartNewsTimer();
    });
  });

  newsPrev.disabled = newsItems.length < 2;
  newsNext.disabled = newsItems.length < 2;
  newsDetailsBtn.disabled = false;

  // ===== VIEW DETAILS NAVIGATION =====

  newsDetailsBtn.onclick = () => {
    if (item.item_type === "event") {
      window.location.href =
        `../pages/event_detail.php?id=${encodeURIComponent(item.item_id)}`;
    } else {
      window.location.href = "../pages/club_page.php";
    }
  };
}

function nextNews() {
  if (newsItems.length < 2) return;

  currentNews = (currentNews + 1) % newsItems.length;
  showNews();
}

function previousNews() {
  if (newsItems.length < 2) return;

  currentNews =
    (currentNews - 1 + newsItems.length) % newsItems.length;

  showNews();
}

function restartNewsTimer() {
  clearInterval(newsTimer);
  newsTimer = null;

  if (newsItems.length > 1) {
    newsTimer = setInterval(nextNews, 5000);
  }
}


// ===== NEWS NAVIGATION =====

newsPrev?.addEventListener("click", () => {
  previousNews();
  restartNewsTimer();
});

newsNext?.addEventListener("click", () => {
  nextNews();
  restartNewsTimer();
});

newsBanner?.addEventListener("mouseenter", () => {
  clearInterval(newsTimer);
});

newsBanner?.addEventListener("mouseleave", restartNewsTimer);

newsBanner?.addEventListener("focusin", () => {
  clearInterval(newsTimer);
});

newsBanner?.addEventListener("focusout", restartNewsTimer);


// ===== START NEWS CAROUSEL =====

showNews();
restartNewsTimer();
