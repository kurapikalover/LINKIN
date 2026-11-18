
document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('editClubModal');
    const openButton = document.getElementById('openEditModal');
    const closeButton = document.getElementById('closeEditModal');
    const cancelButton = document.getElementById('cancelEdit');

    const socialContainer = document.getElementById('socialLinksContainer');
    const addSocialButton = document.getElementById('addSocialLink');

    // Open modal
    function openModal() {
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    // Close modal
    function closeModal() {
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    if (openButton) {
        openButton.addEventListener('click', openModal);
    }

    if (closeButton) {
        closeButton.addEventListener('click', closeModal);
    }

    if (cancelButton) {
        cancelButton.addEventListener('click', closeModal);
    }

    // Close when clicking outside the modal
    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
    }

    // Close with Escape key
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    // Add social media row
    let socialIndex = socialContainer
        ? socialContainer.querySelectorAll('.social-edit-row').length
        : 0;

    if (addSocialButton && socialContainer) {
        addSocialButton.addEventListener('click', function () {

            const row = document.createElement('div');
            row.className = 'social-edit-row';

            row.innerHTML = `
                <select name="social_links[${socialIndex}][platform]" required>
                    <option value="instagram">Instagram</option>
                    <option value="tiktok">TikTok</option>
                    <option value="facebook">Facebook</option>
                    <option value="youtube">YouTube</option>
                    <option value="twitter">Twitter</option>
                    <option value="linkedin">LinkedIn</option>
                    <option value="whatsapp">WhatsApp</option>
                </select>

                <input
                    type="text"
                    name="social_links[${socialIndex}][social_media_name]"
                    placeholder="Username"
                >

                <input
                    type="url"
                    name="social_links[${socialIndex}][account_url]"
                    placeholder="https://..."
                    required
                >

                <button
                    type="button"
                    class="remove-social-button"
                    aria-label="Remove social media link"
                >
                    &times;
                </button>
            `;

            socialContainer.appendChild(row);
            socialIndex++;
        });
    }

    // Remove social media row
    if (socialContainer) {
        socialContainer.addEventListener('click', function (event) {

            if (event.target.closest('.remove-social-button')) {
                event.target.closest('.social-edit-row').remove();
            }

        });
    }

});
