
const socialLinksContainer = document.getElementById("socialLinksContainer");
const addSocialLinkButton = document.getElementById("addSocialLink");
const socialEmptyMessage = document.getElementById("socialEmptyMessage");

let socialLinkIndex = socialLinksContainer
    ? socialLinksContainer.querySelectorAll(".social-link-row").length
    : 0;

const platforms = [
    "instagram",
    "tiktok",
    "facebook",
    "youtube",
    "twitter",
    "linkedin",
    "whatsapp"
];

function updateSocialEmptyMessage() {
    if (!socialLinksContainer || !socialEmptyMessage) return;

    socialEmptyMessage.hidden =
        socialLinksContainer.querySelectorAll(".social-link-row").length > 0;
}

if (addSocialLinkButton && socialLinksContainer) {

    addSocialLinkButton.addEventListener("click", () => {

        const row = document.createElement("div");
        row.className = "social-link-row";

        const platformOptions = platforms.map(platform =>
            `<option value="${platform}">${platform.charAt(0).toUpperCase() + platform.slice(1)}</option>`
        ).join("");

        row.innerHTML = `
            <div class="form-group">
                <label>Platform</label>
                <select name="social_links[${socialLinkIndex}][platform]">
                    <option value="">Select platform</option>
                    ${platformOptions}
                </select>
            </div>

            <div class="form-group">
                <label>Account Name</label>
                <input
                    type="text"
                    name="social_links[${socialLinkIndex}][social_media_name]"
                    placeholder="@username"
                >
            </div>

            <div class="form-group">
                <label>Account URL</label>
                <input
                    type="url"
                    name="social_links[${socialLinkIndex}][account_url]"
                    placeholder="https://..."
                >
            </div>

            <button type="button" class="remove-social-btn">
                Remove
            </button>
        `;

        socialLinksContainer.appendChild(row);

        socialLinkIndex++;

        updateSocialEmptyMessage();
    });

    socialLinksContainer.addEventListener("click", event => {

        if (event.target.classList.contains("remove-social-btn")) {

            event.target.closest(".social-link-row").remove();

            updateSocialEmptyMessage();
        }

    });

    updateSocialEmptyMessage();
}
