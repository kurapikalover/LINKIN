const form = document.getElementById("feedbackForm");
const sent = document.getElementById("feedbackSent");
const fileInput = document.getElementById("fileInput");
const fileName = document.getElementById("fileName");

// Dropdown: choose Image or Video, then open the file picker
document.querySelectorAll(".dropdown-content button").forEach(btn => {
  btn.addEventListener("click", () => {
    fileInput.accept = btn.dataset.type;   // limits the picker to images or videos
    fileInput.click();
  });
});

// Show the chosen file's name
fileInput.addEventListener("change", () => {
  fileName.textContent = fileInput.files[0] ? fileInput.files[0].name : "";
});

// Send
form.addEventListener("submit", (e) => {
  e.preventDefault();                       // stop the page from reloading

  const text = document.getElementById("typeFeedback").value.trim();
  const file = fileInput.files[0];

  // TODO: send `text` and `file` to the backend here (see below)
  //right now fake feedback id since alum connect to supabase 
  //also check dari supabase storage after submit the feedback, can see student id/name or not (check anonymous)

  document.getElementById("feedbackId").textContent =
    "FB-" + Date.now().toString().slice(-6);   // placeholder ID

  form.hidden = true;
  sent.hidden = false;
});