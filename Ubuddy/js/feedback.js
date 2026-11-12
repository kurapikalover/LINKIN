
// ========================================
// FEEDBACK MODAL
// ========================================

const feedbackModal = document.getElementById("feedbackModal");
const openFeedbackModal = document.getElementById("openFeedbackModal");
const closeFeedbackModal = document.getElementById("closeFeedbackModal");

// Open the modal when Give Feedback is clicked.
if (openFeedbackModal && feedbackModal) {

    openFeedbackModal.addEventListener("click", () => {

        feedbackModal.hidden = false;
        document.body.classList.add("modal-open");

    });
}


// Close the modal when the close button is clicked.
if (closeFeedbackModal && feedbackModal) {

    closeFeedbackModal.addEventListener("click", () => {

        feedbackModal.hidden = true;
        document.body.classList.remove("modal-open");

    });
}


// Close the modal when clicking outside its content.
if (feedbackModal) {

    feedbackModal.addEventListener("click", (event) => {

        if (event.target === feedbackModal) {

            feedbackModal.hidden = true;
            document.body.classList.remove("modal-open");

        }
    });
}


// Close the modal when pressing Escape.
document.addEventListener("keydown", (event) => {

    if (
        event.key === "Escape" &&
        feedbackModal &&
        !feedbackModal.hidden
    ) {

        feedbackModal.hidden = true;
        document.body.classList.remove("modal-open");

    }
});


// ========================================
// FEEDBACK CATEGORY SELECTION
// ========================================

const categoryStep = document.getElementById("categoryStep");
const formStep = document.getElementById("formStep");

const categoryButtons = document.querySelectorAll(".category-btn");
const backToCategories = document.getElementById("backToCategories");

const recipientType = document.getElementById("recipientType");
const selectedCategoryTitle = document.getElementById("selectedCategoryTitle");

const clubSelection = document.getElementById("clubSelection");
const recipientClub = document.getElementById("recipientClub");


// Open the feedback form after selecting a category.
categoryButtons.forEach(button => {

    button.addEventListener("click", () => {

        const category = button.dataset.category;

        if (
            !recipientType ||
            !selectedCategoryTitle ||
            !categoryStep ||
            !formStep
        ) {
            return;
        }

        recipientType.value = category;
        selectedCategoryTitle.textContent = category;

        // Show club selection only for Club Feedback.
        if (category === "Club Feedback") {

            if (clubSelection) {
                clubSelection.hidden = false;
            }

            if (recipientClub) {
                recipientClub.required = true;
            }

        } else {

            if (clubSelection) {
                clubSelection.hidden = true;
            }

            if (recipientClub) {
                recipientClub.required = false;
                recipientClub.value = "";
            }
        }

        categoryStep.hidden = true;
        formStep.hidden = false;

    });
});


// Return to category selection.
if (backToCategories) {

    backToCategories.addEventListener("click", () => {

        if (formStep && categoryStep) {

            formStep.hidden = true;
            categoryStep.hidden = false;

        }
    });
}


// ========================================
// FILE UPLOAD
// ========================================

const form = document.getElementById("feedbackForm");
const fileInput = document.getElementById("fileInput");
const fileName = document.getElementById("fileName");

let selectedFiles = [];


// Only run upload functionality when the form exists.
if (form && fileInput && fileName) {

    // Dropdown: choose Image or Video, then open the file picker.
    document.querySelectorAll(".dropdown-content button").forEach(button => {

        button.addEventListener("click", () => {

            fileInput.accept = button.dataset.type;
            fileInput.click();

        });
    });


    // Update the file input and display selected files.
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

            removeButton.setAttribute(
                "aria-label",
                `Remove ${file.name}`
            );

            // Remove selected file.
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

            // Prevent more than 5 files.
            if (selectedFiles.length >= 5) {
                alert("You can upload a maximum of 5 files.");
                return;
            }

            // Avoid adding the same file twice.
            const alreadySelected = selectedFiles.some(
                selected =>
                    selected.name === file.name &&
                    selected.size === file.size &&
                    selected.lastModified === file.lastModified
            );

            if (!alreadySelected) {
                selectedFiles.push(file);
            }

        });

        updateFileList();

    });

}


// Form submission is handled by PHP.
// Do not prevent the default form submission.
