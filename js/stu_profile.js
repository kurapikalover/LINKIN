
document.addEventListener("DOMContentLoaded", function () {
    // =========================================
    // PROFILE INFORMATION
    // =========================================

    const profileForm = document.getElementById("profileForm");
    const editProfileBtn = document.getElementById("editProfileBtn");
    const cancelEditBtn = document.getElementById("cancelEditBtn");

    const studentName = document.getElementById("studentName");
    const studentEmail = document.getElementById("studentEmail");
    const studentProgramme = document.getElementById("studentProgramme");
    const programmeText = document.getElementById("programmeText");

    const editName = document.getElementById("editName");
    const editEmail = document.getElementById("editEmail");
    const editProgramme = document.getElementById("editProgramme");

    const defaultProfile = {
        name: "Student Name",
        email: "student@example.com",
        programme: "Digital Science"
    };

    function loadProfile() {
        try {
            const saved = JSON.parse(localStorage.getItem("ubuddyProfile"));
            return saved && typeof saved === "object"
                ? { ...defaultProfile, ...saved }
                : { ...defaultProfile };
        } catch {
            return { ...defaultProfile };
        }
    }

    let profile = loadProfile();

    function renderProfile() {
        studentName.textContent = profile.name;
        studentEmail.textContent = profile.email;
        studentProgramme.textContent = profile.programme;
        programmeText.textContent = profile.programme;
    }

    editProfileBtn.addEventListener("click", function () {
        editName.value = profile.name;
        editEmail.value = profile.email;
        editProgramme.value = profile.programme;
        profileForm.hidden = false;
        editName.focus();
    });

    cancelEditBtn.addEventListener("click", function () {
        profileForm.reset();
        profileForm.hidden = true;
    });

    profileForm.addEventListener("submit", function (event) {
        event.preventDefault();

        profile = {
            name: editName.value.trim(),
            email: editEmail.value.trim(),
            programme: editProgramme.value.trim()
        };

        try {
            localStorage.setItem("ubuddyProfile", JSON.stringify(profile));
        } catch {
            alert("Unable to save changes in this browser.");
            return;
        }

        renderProfile();
        profileForm.hidden = true;
        alert("Profile updated in this browser.");
    });

    renderProfile();


    // =========================================
    // ACHIEVEMENT FORM
    // =========================================

    const showAchievementFormBtn =
        document.getElementById("showAchievementFormBtn");

    const achievementForm =
        document.getElementById("achievementForm");

    const cancelAchievementBtn =
        document.getElementById("cancelAchievementBtn");

    const achievementTitle =
        document.getElementById("achievementTitle");

    const achievementCategory =
        document.getElementById("achievementCategory");

    const achievementDate =
        document.getElementById("achievementDate");

    const achievementDescription =
        document.getElementById("achievementDescription");

    const achievementEvidence =
        document.getElementById("achievementEvidence");

    const achievementsList =
        document.getElementById("achievementsList");

    const STORAGE_KEY = "ubuddyDemoAchievements";
    const MAX_FILE_SIZE = 5 * 1024 * 1024;

    function loadAchievements() {
        try {
            const saved = JSON.parse(localStorage.getItem(STORAGE_KEY));
            return Array.isArray(saved) ? saved : [];
        } catch {
            return [];
        }
    }

    let achievements = loadAchievements();

    function saveAchievements() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(achievements));
            return true;
        } catch {
            alert(
                "The browser could not save this achievement. " +
                "Try removing the evidence file or use a backend for file storage."
            );
            return false;
        }
    }

    function createEmptyState(message) {
        const wrapper = document.createElement("div");
        wrapper.className = "empty-state";

        const icon = document.createElement("span");
        icon.className = "material-symbols-outlined";
        icon.textContent = "emoji_events";

        const text = document.createElement("p");
        text.textContent = message;

        wrapper.append(icon, text);
        return wrapper;
    }

    function formatDate(dateString) {
        if (!dateString) return "Date not provided";

        const date = new Date(dateString + "T00:00:00");

        if (Number.isNaN(date.getTime())) {
            return "Date not provided";
        }

        return date.toLocaleDateString("en-GB", {
            day: "numeric",
            month: "short",
            year: "numeric"
        });
    }

    function renderAchievements() {
        achievementsList.replaceChildren();

        if (achievements.length === 0) {
            achievementsList.appendChild(
                createEmptyState(
                    "You have not submitted any achievements yet."
                )
            );
            return;
        }

        achievements.forEach(function (achievement) {
            const card = document.createElement("article");
            card.className = "achievement-card";

            const top = document.createElement("div");
            top.className = "achievement-card-top";

            const heading = document.createElement("div");

            const title = document.createElement("h3");
            title.textContent = achievement.title;

            const meta = document.createElement("p");
            meta.className = "achievement-meta";
            meta.textContent =
                achievement.category + " · " +
                formatDate(achievement.date);

            heading.append(title, meta);

            const status = document.createElement("span");
            status.className =
                "achievement-status " + achievement.status.toLowerCase();
            status.textContent = achievement.status;

            top.append(heading, status);

            const description = document.createElement("p");
            description.textContent = achievement.description;

            card.append(top, description);

            if (achievement.evidenceName) {
                const evidence = document.createElement("p");
                evidence.className = "achievement-evidence";
                evidence.textContent =
                    "Evidence attached: " + achievement.evidenceName;
                card.appendChild(evidence);
            }

            if (achievement.status === "Rejected" && achievement.reviewReason) {
                const reason = document.createElement("p");
                reason.className = "review-reason";
                reason.textContent =
                    "Review feedback: " + achievement.reviewReason;
                card.appendChild(reason);
            }

            achievementsList.appendChild(card);
        });
    }

    showAchievementFormBtn.addEventListener("click", function () {
        achievementForm.hidden = false;
        achievementTitle.focus();
    });

    cancelAchievementBtn.addEventListener("click", function () {
        achievementForm.reset();
        achievementForm.hidden = true;
    });

    achievementForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const file = achievementEvidence.files[0];

        if (file && file.size > MAX_FILE_SIZE) {
            alert("The evidence file must be 5 MB or smaller.");
            return;
        }

        const allowedTypes = [
            "application/pdf",
            "image/png",
            "image/jpeg"
        ];

        if (file && !allowedTypes.includes(file.type)) {
            alert("Please upload a PDF, PNG or JPG file.");
            return;
        }

        const newAchievement = {
            id: Date.now(),
            title: achievementTitle.value.trim(),
            category: achievementCategory.value,
            date: achievementDate.value,
            description: achievementDescription.value.trim(),
            evidenceName: file ? file.name : "",
            status: "Pending",
            reviewReason: ""
        };

        if (!newAchievement.title || !newAchievement.description) {
            alert("Please complete all required fields.");
            return;
        }

        achievements.unshift(newAchievement);

        if (!saveAchievements()) {
            achievements.shift();
            return;
        }

        renderAchievements();
        achievementForm.reset();
        achievementForm.hidden = true;

        alert(
            "Achievement saved as Pending in this browser. " +
            "It has not yet been sent to Student Affairs."
        );
    });

    renderAchievements();
});
