
document.addEventListener('DOMContentLoaded', function () {

    /* =========================================
       ASSIGN ROLE - CLUB FIELD
    ========================================= */

    const roleSelect = document.getElementById('roleSelect');
    const clubField = document.getElementById('clubField');
    const clubSelect = document.getElementById('clubSelect');

    if (roleSelect && clubField && clubSelect) {

        function updateClubField() {

            const isCommitteeAdmin =
                roleSelect.value === 'committee_admin';

            clubField.hidden = !isCommitteeAdmin;
            clubSelect.required = isCommitteeAdmin;

            if (!isCommitteeAdmin) {
                clubSelect.value = '';
            }
        }

        roleSelect.addEventListener('change', updateClubField);
        updateClubField();
    }


    /* =========================================
       FEEDBACK SEARCH
       SERVER-SIDE SEARCH WITH SHORT DELAY
    ========================================= */

    const feedbackSearchForm =
        document.querySelector('.feedback-search');

    const feedbackSearchInput =
        feedbackSearchForm?.querySelector('input[name="search"]');

    if (feedbackSearchForm && feedbackSearchInput) {

        let searchTimer;

        feedbackSearchInput.addEventListener('input', function () {

            clearTimeout(searchTimer);

            searchTimer = setTimeout(function () {
                feedbackSearchForm.requestSubmit();
            }, 450);
        });
    }


    /* =========================================
       FEEDBACK CONFIRMATION MODAL
    ========================================= */

    const feedbackConfirmModal =
        document.getElementById('feedbackConfirmModal');

    const feedbackConfirmTitle =
        document.getElementById('feedbackConfirmTitle');

    const feedbackConfirmMessage =
        document.getElementById('feedbackConfirmMessage');

    const feedbackConfirmIcon =
        document.getElementById('feedbackConfirmIcon');

    const feedbackConfirmSubmit =
        document.getElementById('feedbackConfirmSubmit');

    const feedbackActionForms =
        document.querySelectorAll(
            '.feedback-action-form[data-confirm-action]'
        );

    let pendingFeedbackForm = null;
    let previousFeedbackTrigger = null;


    /* -------------------------------------
       OPEN FEEDBACK CONFIRMATION
    ------------------------------------- */

    function openFeedbackConfirmation(form) {

        if (
            !feedbackConfirmModal ||
            !feedbackConfirmTitle ||
            !feedbackConfirmMessage ||
            !feedbackConfirmIcon ||
            !feedbackConfirmSubmit
        ) {
            return;
        }

        const action = form.dataset.confirmAction;

        pendingFeedbackForm = form;
        previousFeedbackTrigger =
            form.querySelector('button[type="submit"]');

        if (action === 'review') {

            feedbackConfirmTitle.textContent =
                'Mark as Reviewed?';

            feedbackConfirmMessage.textContent =
                'This feedback will be moved from Pending to Reviewed. You can still find it in the Reviewed section.';

            feedbackConfirmIcon.innerHTML =
                '<span class="material-symbols-outlined">check_circle</span>';

            feedbackConfirmSubmit.textContent =
                'Mark as Reviewed';

            feedbackConfirmSubmit.classList.remove(
                'confirm-archive'
            );

            feedbackConfirmSubmit.classList.add(
                'confirm-review'
            );

        } else if (action === 'archive') {

            feedbackConfirmTitle.textContent =
                'Archive Feedback?';

            feedbackConfirmMessage.textContent =
                'This feedback will be moved to Archived. It will no longer appear in the active feedback sections, but it will not be deleted.';

            feedbackConfirmIcon.innerHTML =
                '<span class="material-symbols-outlined">archive</span>';

            feedbackConfirmSubmit.textContent =
                'Archive Feedback';

            feedbackConfirmSubmit.classList.remove(
                'confirm-review'
            );

            feedbackConfirmSubmit.classList.add(
                'confirm-archive'
            );

        } else {

            return;
        }

        feedbackConfirmModal.hidden = false;
        document.body.classList.add('modal-open');

        feedbackConfirmSubmit.focus();
    }


    /* -------------------------------------
       CLOSE FEEDBACK CONFIRMATION
    ------------------------------------- */

    function closeFeedbackConfirmation() {

        if (!feedbackConfirmModal) return;

        feedbackConfirmModal.hidden = true;

        pendingFeedbackForm = null;

        const anotherManagementModalOpen =
            Array.from(modals).some(function (modal) {
                return !modal.hidden;
            });

        if (!anotherManagementModalOpen) {
            document.body.classList.remove('modal-open');
        }

        if (
            previousFeedbackTrigger &&
            document.contains(previousFeedbackTrigger)
        ) {
            previousFeedbackTrigger.focus();
        }

        previousFeedbackTrigger = null;
    }


    /* -------------------------------------
       INTERCEPT FEEDBACK FORM SUBMISSION
    ------------------------------------- */

    feedbackActionForms.forEach(function (form) {

        form.addEventListener('submit', function (event) {

            event.preventDefault();

            openFeedbackConfirmation(form);
        });
    });


    /* -------------------------------------
       CONFIRM FEEDBACK ACTION
    ------------------------------------- */

    if (feedbackConfirmSubmit) {

        feedbackConfirmSubmit.addEventListener('click', function () {

            if (!pendingFeedbackForm) return;

            const formToSubmit = pendingFeedbackForm;

            closeFeedbackConfirmation();

            formToSubmit.submit();
        });
    }


    /* -------------------------------------
       CANCEL FEEDBACK CONFIRMATION
    ------------------------------------- */

    if (feedbackConfirmModal) {

        feedbackConfirmModal
            .querySelectorAll('[data-confirm-cancel]')
            .forEach(function (element) {

                element.addEventListener('click', function () {
                    closeFeedbackConfirmation();
                });
            });
    }


    /* =========================================
       MODAL ELEMENTS
    ========================================= */

    const modals =
        document.querySelectorAll('.management-modal');

    const openModalButtons =
        document.querySelectorAll('[data-open-modal]');

    const closeModalButtons =
        document.querySelectorAll('[data-close-modal]');


    /* =========================================
       RESET ACCOUNT COMBOBOX
    ========================================= */

    function resetAccountCombobox(searchInputId, hiddenInputId) {

        const searchInput =
            document.getElementById(searchInputId);

        const hiddenInput =
            document.getElementById(hiddenInputId);

        if (!searchInput || !hiddenInput) return;

        const wrapper = searchInput.closest('.account-combobox');

        const optionsContainer =
            wrapper?.querySelector('.account-combobox-options');

        const options =
            optionsContainer?.querySelectorAll('.account-combobox-option');

        const emptyMessage =
            optionsContainer?.querySelector('.account-combobox-empty');

        searchInput.value = '';
        hiddenInput.value = '';

        searchInput.setCustomValidity(
            'Please select an account from the list.'
        );

        if (options) {
            options.forEach(function (option) {
                option.hidden = false;
                option.setAttribute('aria-selected', 'false');
                option.classList.remove('active-option');
            });
        }

        if (emptyMessage) {
            emptyMessage.hidden = true;
        }

        if (optionsContainer) {
            optionsContainer.hidden = true;
        }

        searchInput.setAttribute('aria-expanded', 'false');
    }


    /* =========================================
       RESET MODAL
    ========================================= */

    function resetModal(modal) {

        if (!modal) return;

        const actionPanel =
            modal.querySelector('[data-action-panel]');

        const formPanels =
            modal.querySelectorAll('[data-form-panel]');

        if (actionPanel) {
            actionPanel.hidden = false;
        }

        formPanels.forEach(function (panel) {
            panel.hidden = true;
        });


        // Reset notification audience.

        const audience =
            modal.querySelector('#notificationAudience');

        if (audience) {
            audience.value = 'all';
            audience.dispatchEvent(new Event('change'));
        }


        // Reset account comboboxes.

        resetAccountCombobox(
            'editUserSearch',
            'editUserSelect'
        );

        resetAccountCombobox(
            'deleteUserSearch',
            'deleteUserId'
        );


        // Clear edit account fields.

        const editFields = [
            'editName',
            'editEmail',
            'editFaculty',
            'editPassword'
        ];

        editFields.forEach(function (fieldId) {

            const field = document.getElementById(fieldId);

            if (field) {
                field.value = '';
            }
        });

        const editUserType =
            document.getElementById('editUserType');

        if (editUserType) {
            editUserType.value = 'Student';
        }


        // Reset category selection.

        const editCategorySelect =
            modal.querySelector('#editCategorySelect');

        if (editCategorySelect) {
            editCategorySelect.value = '';
            editCategorySelect.dispatchEvent(new Event('change'));
        }


        // Reset category delete selection.

        const deleteCategorySelect =
            modal.querySelector('#deleteCategoryId');

        if (deleteCategorySelect) {
            deleteCategorySelect.value = '';
        }
    }


    /* =========================================
       OPEN MODAL
    ========================================= */

    function openModal(modal) {

        if (!modal) return;

        resetModal(modal);

        modal.hidden = false;
        document.body.classList.add('modal-open');

        const firstButton = modal.querySelector(
            '.modal-close-btn, .management-option, input, select'
        );

        if (firstButton) {
            setTimeout(function () {
                firstButton.focus();
            }, 50);
        }
    }

    openModalButtons.forEach(function (button) {

        button.addEventListener('click', function () {

            const modalId = button.dataset.openModal;
            const modal = document.getElementById(modalId);

            openModal(modal);
        });
    });


    /* =========================================
       CLOSE MODAL
    ========================================= */

    function closeModal(modal) {

        if (!modal) return;

        modal.hidden = true;
        resetModal(modal);

        const anotherModalOpen =
            Array.from(modals).some(function (item) {
                return !item.hidden;
            });

        if (
            !anotherModalOpen &&
            (!feedbackConfirmModal || feedbackConfirmModal.hidden)
        ) {
            document.body.classList.remove('modal-open');
        }
    }

    closeModalButtons.forEach(function (button) {

        button.addEventListener('click', function () {
            closeModal(button.closest('.management-modal'));
        });
    });


    /* =========================================
       CLOSE WHEN CLICKING OUTSIDE
    ========================================= */

    modals.forEach(function (modal) {

        modal.addEventListener('click', function (event) {

            if (event.target === modal) {
                closeModal(modal);
            }
        });
    });


    /* =========================================
       CLOSE WITH ESCAPE
    ========================================= */

    document.addEventListener('keydown', function (event) {

        if (event.key !== 'Escape') return;

        if (
            feedbackConfirmModal &&
            !feedbackConfirmModal.hidden
        ) {
            closeFeedbackConfirmation();
            return;
        }

        modals.forEach(function (modal) {

            if (!modal.hidden) {
                closeModal(modal);
            }
        });
    });


    /* =========================================
       MANAGEMENT ACTION SWITCHING
    ========================================= */

    document.querySelectorAll('[data-show-form]').forEach(
        function (button) {

            button.addEventListener('click', function () {

                const formId = button.dataset.showForm;
                const modal = button.closest('.management-modal');

                if (!modal) return;

                const targetPanel =
                    modal.querySelector('#' + CSS.escape(formId));

                const actionPanel =
                    modal.querySelector('[data-action-panel]');

                const formPanels =
                    modal.querySelectorAll('[data-form-panel]');

                if (!targetPanel) return;

                if (actionPanel) {
                    actionPanel.hidden = true;
                }

                formPanels.forEach(function (panel) {
                    panel.hidden = panel !== targetPanel;
                });

                const firstField =
                    targetPanel.querySelector(
                        '.account-combobox-input, input, select, textarea'
                    );

                if (firstField) {
                    firstField.focus();
                }
            });
        }
    );


    /* =========================================
       BACK TO ACTION CHOICES
    ========================================= */

    document.querySelectorAll('[data-back-to-actions]').forEach(
        function (button) {

            button.addEventListener('click', function () {

                const modal = button.closest('.management-modal');

                if (!modal) return;

                resetModal(modal);
            });
        }
    );


    /* =========================================
       SEARCHABLE ACCOUNT COMBOBOX
    ========================================= */

    function setupAccountCombobox(config) {

        const searchInput =
            document.getElementById(config.searchInputId);

        const hiddenInput =
            document.getElementById(config.hiddenInputId);

        if (!searchInput || !hiddenInput) return;

        const wrapper =
            searchInput.closest('.account-combobox');

        if (!wrapper) return;

        const optionsContainer =
            wrapper.querySelector('.account-combobox-options');

        if (!optionsContainer) return;

        const options = Array.from(
            optionsContainer.querySelectorAll(
                '.account-combobox-option'
            )
        );

        const emptyMessage =
            optionsContainer.querySelector('.account-combobox-empty');

        let activeIndex = -1;


        /* -------------------------------------
           FILTER OPTIONS
        ------------------------------------- */

        function filterOptions() {

            const searchValue =
                searchInput.value.trim().toLowerCase();

            let visibleCount = 0;

            options.forEach(function (option) {

                const name =
                    option.dataset.name?.toLowerCase() || '';

                const userId =
                    option.dataset.value?.toLowerCase() || '';

                const matches =
                    name.includes(searchValue) ||
                    userId.includes(searchValue);

                option.hidden = !matches;

                option.setAttribute(
                    'aria-selected',
                    'false'
                );

                if (matches) {
                    visibleCount++;
                }
            });

            if (emptyMessage) {
                emptyMessage.hidden = visibleCount !== 0;
            }

            activeIndex = -1;

            return visibleCount;
        }


        /* -------------------------------------
           SHOW DROPDOWN
        ------------------------------------- */

        function openDropdown() {

            filterOptions();

            optionsContainer.hidden = false;

            searchInput.setAttribute(
                'aria-expanded',
                'true'
            );
        }


        /* -------------------------------------
           HIDE DROPDOWN
        ------------------------------------- */

        function closeDropdown() {

            optionsContainer.hidden = true;

            searchInput.setAttribute(
                'aria-expanded',
                'false'
            );

            activeIndex = -1;

            options.forEach(function (option) {
                option.classList.remove('active-option');
            });
        }


        /* -------------------------------------
           GET VISIBLE OPTIONS
        ------------------------------------- */

        function getVisibleOptions() {

            return options.filter(function (option) {
                return !option.hidden;
            });
        }


        /* -------------------------------------
           SELECT ACCOUNT
        ------------------------------------- */

        function selectAccount(option) {

            if (!option) return;

            const userId = option.dataset.value || '';
            const name = option.dataset.name || '';

            hiddenInput.value = userId;

            searchInput.value =
                name + ' (' + userId + ')';

            searchInput.setCustomValidity('');

            options.forEach(function (item) {

                item.setAttribute(
                    'aria-selected',
                    item === option ? 'true' : 'false'
                );

                item.classList.remove('active-option');
            });

            closeDropdown();

            hiddenInput.dispatchEvent(
                new Event('change', { bubbles: true })
            );
        }


        /* -------------------------------------
           HIGHLIGHT ACTIVE OPTION
        ------------------------------------- */

        function highlightOption(index) {

            const visibleOptions = getVisibleOptions();

            if (visibleOptions.length === 0) return;

            if (index < 0) {
                index = visibleOptions.length - 1;
            }

            if (index >= visibleOptions.length) {
                index = 0;
            }

            activeIndex = index;

            options.forEach(function (option) {
                option.classList.remove('active-option');
            });

            const activeOption =
                visibleOptions[activeIndex];

            activeOption.classList.add('active-option');

            activeOption.scrollIntoView({
                block: 'nearest'
            });
        }


        /* -------------------------------------
           INPUT EVENT
        ------------------------------------- */

        searchInput.addEventListener('input', function () {

            hiddenInput.value = '';

            searchInput.setCustomValidity(
                'Please select an account from the list.'
            );

            hiddenInput.dispatchEvent(
                new Event('change', { bubbles: true })
            );

            openDropdown();
        });


        /* -------------------------------------
           FOCUS EVENT
        ------------------------------------- */

        searchInput.addEventListener('focus', function () {
            openDropdown();
        });


        /* -------------------------------------
           CLICK EVENT
        ------------------------------------- */

        searchInput.addEventListener('click', function () {
            openDropdown();
        });


        /* -------------------------------------
           OPTION CLICK
        ------------------------------------- */

        options.forEach(function (option) {

            option.addEventListener('click', function () {
                selectAccount(option);
            });
        });


        /* -------------------------------------
           KEYBOARD NAVIGATION
        ------------------------------------- */

        searchInput.addEventListener('keydown', function (event) {

            const visibleOptions = getVisibleOptions();

            if (event.key === 'ArrowDown') {

                event.preventDefault();

                if (optionsContainer.hidden) {
                    openDropdown();
                }

                highlightOption(activeIndex + 1);
            }

            else if (event.key === 'ArrowUp') {

                event.preventDefault();

                highlightOption(activeIndex - 1);
            }

            else if (event.key === 'Enter') {

                if (!optionsContainer.hidden && visibleOptions.length > 0) {

                    event.preventDefault();

                    const selectedOption =
                        activeIndex >= 0
                            ? visibleOptions[activeIndex]
                            : visibleOptions[0];

                    selectAccount(selectedOption);
                }
            }

            else if (event.key === 'Escape') {
                closeDropdown();
            }
        });


        /* -------------------------------------
           CLOSE WHEN CLICKING OUTSIDE
        ------------------------------------- */

        document.addEventListener('click', function (event) {

            if (!wrapper.contains(event.target)) {
                closeDropdown();
            }
        });


        /* -------------------------------------
           FORM VALIDATION
        ------------------------------------- */

        const form = searchInput.closest('form');

        if (form) {

            form.addEventListener('submit', function (event) {

                if (!hiddenInput.value) {

                    event.preventDefault();

                    searchInput.setCustomValidity(
                        'Please select an account from the list.'
                    );

                    searchInput.reportValidity();
                    searchInput.focus();

                    openDropdown();

                    return;
                }

                searchInput.setCustomValidity('');
            });
        }


        // Initial state.

        searchInput.setCustomValidity(
            'Please select an account from the list.'
        );
    }


    /* =========================================
       INITIALISE ACCOUNT COMBOBOXES
    ========================================= */

    // Edit Account

    setupAccountCombobox({
        searchInputId: 'editUserSearch',
        hiddenInputId: 'editUserSelect'
    });


    // Delete Account

    setupAccountCombobox({
        searchInputId: 'deleteUserSearch',
        hiddenInputId: 'deleteUserId'
    });


    // Assign Role

    setupAccountCombobox({
        searchInputId: 'targetUserSearch',
        hiddenInputId: 'targetUserId'
    });


    /* =========================================
       EDIT ACCOUNT AUTO-FILL
    ========================================= */

    const editUserSelect =
        document.getElementById('editUserSelect');

    if (editUserSelect) {

        function populateAccountFields() {

            const selectedOption = document.querySelector(
                '#editUserOptions .account-combobox-option[data-value="' +
                CSS.escape(editUserSelect.value) +
                '"]'
            );

            const nameField =
                document.getElementById('editName');

            const emailField =
                document.getElementById('editEmail');

            const facultyField =
                document.getElementById('editFaculty');

            const userTypeField =
                document.getElementById('editUserType');

            const passwordField =
                document.getElementById('editPassword');


            if (!selectedOption || !editUserSelect.value) {

                if (nameField) nameField.value = '';
                if (emailField) emailField.value = '';
                if (facultyField) facultyField.value = '';
                if (userTypeField) userTypeField.value = 'Student';
                if (passwordField) passwordField.value = '';

                return;
            }


            if (nameField) {
                nameField.value =
                    selectedOption.dataset.name || '';
            }

            if (emailField) {
                emailField.value =
                    selectedOption.dataset.email || '';
            }

            if (facultyField) {
                facultyField.value =
                    selectedOption.dataset.faculty || '';
            }

            if (userTypeField) {
                userTypeField.value =
                    selectedOption.dataset.userType || 'Student';
            }

            if (passwordField) {
                passwordField.value = '';
            }
        }

        editUserSelect.addEventListener(
            'change',
            populateAccountFields
        );

        populateAccountFields();
    }


    /* =========================================
       EDIT CATEGORY AUTO-FILL
    ========================================= */

    const editCategorySelect =
        document.getElementById('editCategorySelect');

    if (editCategorySelect) {

        function populateCategoryField() {

            const selectedOption =
                editCategorySelect.options[
                    editCategorySelect.selectedIndex
                ];

            const categoryNameField =
                document.getElementById('editCategoryName');

            if (!selectedOption || !selectedOption.value) {

                if (categoryNameField) {
                    categoryNameField.value = '';
                }

                return;
            }

            if (categoryNameField) {
                categoryNameField.value =
                    selectedOption.dataset.name || '';
            }
        }

        editCategorySelect.addEventListener(
            'change',
            populateCategoryField
        );

        populateCategoryField();
    }


    /* =========================================
       NOTIFICATION AUDIENCE
    ========================================= */

    const notificationAudience =
        document.getElementById('notificationAudience');

    const notificationUserField =
        document.getElementById('notificationUserField');

    const notificationUser =
        document.getElementById('notificationUser');

    if (
        notificationAudience &&
        notificationUserField &&
        notificationUser
    ) {

        function updateNotificationAudience() {

            const isIndividual =
                notificationAudience.value === 'individual';

            notificationUserField.hidden = !isIndividual;
            notificationUser.required = isIndividual;

            if (!isIndividual) {
                notificationUser.value = '';
            }
        }

        notificationAudience.addEventListener(
            'change',
            updateNotificationAudience
        );

        updateNotificationAudience();
    }


    /* =========================================
       DELETE ACCOUNT CONFIRMATION
    ========================================= */

    const deleteAccountForm =
        document.getElementById('accountDeleteFormSubmit');

    if (deleteAccountForm) {

        deleteAccountForm.addEventListener('submit', function (event) {

            const confirmed = window.confirm(
                'Are you sure you want to delete this account? This action cannot be undone.'
            );

            if (!confirmed) {
                event.preventDefault();
            }
        });
    }


    /* =========================================
       DELETE CATEGORY CONFIRMATION
    ========================================= */

    const deleteCategoryForm =
        document.getElementById('categoryDeleteFormSubmit');

    if (deleteCategoryForm) {

        deleteCategoryForm.addEventListener('submit', function (event) {

            const confirmed = window.confirm(
                'Are you sure you want to delete this category?'
            );

            if (!confirmed) {
                event.preventDefault();
            }
        });
    }


    /* =========================================
       NOTIFICATION FORM CONFIRMATION
    ========================================= */

    const notificationForm =
        document.getElementById('notificationForm');

    if (notificationForm) {

        notificationForm.addEventListener('submit', function (event) {

            const audience =
                document.getElementById('notificationAudience');

            const message = audience?.value === 'individual'
                ? 'Send this notification to the selected user?'
                : 'Send this notification to all users?';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    }

});
