// ===== DATA =====
// Later, replace these arrays with data fetched from your database.
const newsItems = [
  "PMUBD Showcase will be held on 6-9th September at Main Library!",
  "Club Registration is now open!",
  "BJFA Club will held their Origami Workshop next week!"
];
 
const clubs = [
  { name: "Swimming Club", category: "Sports",          color: "#ef5b6a" },
  { name: "Crochet Society Club",      category: "Arts and Crafts", color: "#f58fb0" },
  { name: "Gardening Club",   category: "Recreational",    color: "#4cc36b" },
  { name: "BJFA Club",  category: "Culture",         color: "#ff9a52" },
  { name: "Muslim Youth Club", category: "Religious",       color: "#c68af0" },      
];
 
const events = ["Opening Ceremony", "Yukata Workshop", "Silat Workshop", "Origami Workshop", "Movie Night"];
 
 
// ===== NEWS CAROUSEL =====
let currentNews = 0;
 
function showNews() {
  document.getElementById("newsText").textContent = newsItems[currentNews];
 
  // Rebuild the dots, marking the current one as active
  document.getElementById("newsDots").innerHTML = newsItems
    .map((_, i) => `<span class="dot ${i === currentNews ? "active" : ""}"></span>`)
    .join("");
}
 
document.getElementById("newsPrev").addEventListener("click", () => {
  currentNews = (currentNews - 1 + newsItems.length) % newsItems.length;
  showNews();
});
 
document.getElementById("newsNext").addEventListener("click", () => {
  currentNews = (currentNews + 1) % newsItems.length;
  showNews();
});
 
 
// ===== CLUB & EVENT CARDS =====
function renderCards(filterText = "") {
  const clubsHTML = clubs
    .filter(c => c.name.toLowerCase().includes(filterText))
    .map(c => `
      <div class="card">
        <div class="card-img"></div>
        ${c.name}<br>
        <span class="tag" style="background:${c.color}">${c.category}</span>
      </div>`)
    .join("");
 
  const eventsHTML = events
    .filter(e => e.toLowerCase().includes(filterText))
    .map(e => `
      <div class="card">
        <div class="card-img"></div>
        ${e}
      </div>`)
    .join("");
 
  document.getElementById("clubsRow").innerHTML = clubsHTML;
  document.getElementById("eventsRow").innerHTML = eventsHTML;
}
 
 
// ===== SEARCH =====
document.getElementById("searchInput").addEventListener("input", (e) => {
  const text = e.target.value.replace("🔍", "").trim().toLowerCase();
  renderCards(text);
});
 
 
// ===== ROW ARROWS (scroll the card rows left/right) =====
document.querySelectorAll("[data-target]").forEach(button => {
  button.addEventListener("click", () => {
    const row = document.getElementById(button.dataset.target);
    row.scrollBy({ left: button.dataset.dir * 320 });
  });
});
 
 
// ===== START =====
showNews();
renderCards();
 