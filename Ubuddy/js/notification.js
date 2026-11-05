
document.addEventListener('DOMContentLoaded', () => {
    const notificationCards = document.querySelectorAll('.info-notification');

    async function markAsRead(card) {
        if (card.dataset.isRead === 'true' || card.dataset.loading === 'true') {
            return;
        }

        card.dataset.loading = 'true';

        const formData = new FormData();
        formData.append('notif_id', card.dataset.notifId);

        try {
            const response = await fetch('mark_notification_read.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Unable to mark notification as read.');
            }

            card.dataset.isRead = 'true';
            card.classList.add('is-read');
            card.setAttribute('aria-pressed', 'true');

            const unreadBadge = card.querySelector('.badge');

            if (unreadBadge) {
                unreadBadge.remove();
            }

            // Remove the bell dot when all notifications are read.
            const remainingUnread = document.querySelectorAll(
                '.info-notification[data-is-read="false"]'
            );

            if (remainingUnread.length === 0) {
                const bellDot = document.querySelector('.tab-icon-bell .notification-dot');

                if (bellDot) {
                    bellDot.remove();
                }
            }

        } catch (error) {
            console.error(error);
            alert('Unable to mark this notification as read. Please try again.');
        } finally {
            delete card.dataset.loading;
        }
    }

    notificationCards.forEach(card => {
        card.addEventListener('click', event => {
            // Let the arrow link navigate normally.
            if (event.target.closest('.notification-arrow')) {
                return;
            }

            markAsRead(card);
        });

        // Allow keyboard users to activate the notification card.
        card.addEventListener('keydown', event => {
            if (event.target.closest('.notification-arrow')) {
                return;
            }

            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                markAsRead(card);
            }
        });
    });
});
