
const form = document.getElementById("feedbackForm");
const fileInput = document.getElementById("fileInput");
const fileName = document.getElementById("fileName");

let selectedFiles = [];

// Dropdown: choose Image or Video, then open the file picker.
document.querySelectorAll(".dropdown-content button").forEach(btn => {
    btn.addEventListener("click", () => {
        fileInput.accept = btn.dataset.type;
        fileInput.click();
    });
});

// Update the file input and display the selected files.
function updateFileList() {

    const dataTransfer = new DataTransfer();

    selectedFiles.forEach(file => {
        dataTransfer.items.add(file);
    });

    fileInput.files = dataTransfer.files;

    // Clear the previous file list.
    fileName.innerHTML = "";

    selectedFiles.forEach((file, index) => {

        // Container for each selected file.
        const fileItem = document.createElement("span");
        fileItem.className = "selected-file";

        // File name.
        const name = document.createElement("span");
        name.textContent = file.name;
        name.className = "selected-file-name";

        // Remove button.
        const removeButton = document.createElement("button");
        removeButton.type = "button";
        removeButton.textContent = "×";
        removeButton.className = "remove-file";
        removeButton.setAttribute("aria-label", `Remove ${file.name}`);

        // Remove this file when clicked.
        removeButton.addEventListener("click", () => {
            selectedFiles.splice(index, 1);
            updateFileList();
        });

        fileItem.appendChild(name);
        fileItem.appendChild(removeButton);

        fileName.appendChild(fileItem);
    });
}

// Add newly selected files.
fileInput.addEventListener("change", () => {

    const newFiles = Array.from(fileInput.files);

    newFiles.forEach(file => {

        if (selectedFiles.length >= 5) {
            alert("You can upload a maximum of 5 files.");
            return;
        }

        selectedFiles.push(file);
    });

    updateFileList();
});

// Form submission is handled by PHP.
// Do not prevent the default form submission.
