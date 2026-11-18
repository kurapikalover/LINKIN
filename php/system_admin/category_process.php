
<?php

require_once __DIR__ . '/process_helper.php';

$action = trim($_POST['action'] ?? '');

try {

    /*
    |--------------------------------------------------------------------------
    | ADD CATEGORY
    |--------------------------------------------------------------------------
    */
    if ($action === 'add_category') {

        $categoryName = trim($_POST['category_name'] ?? '');

        if ($categoryName === '') {
            systemAdminProcessError(
                'Please enter a category name.'
            );
        }

        if (mb_strlen($categoryName) > 100) {
            systemAdminProcessError(
                'Category name cannot exceed 100 characters.'
            );
        }

        $success = addSystemAdminCategory(
            $pdo,
            $categoryName
        );

        if (!$success) {
            systemAdminProcessError(
                'Unable to add the category.'
            );
        }

        $_SESSION['system_admin_success'] =
            'Category added successfully.';


    /*
    |--------------------------------------------------------------------------
    | EDIT CATEGORY
    |--------------------------------------------------------------------------
    */
    } elseif ($action === 'edit_category') {

        $categoryId = filter_input(
            INPUT_POST,
            'edit_category_id',
            FILTER_VALIDATE_INT
        );

        $categoryName = trim(
            $_POST['edit_category_name'] ?? ''
        );

        if (!$categoryId || $categoryId < 1) {
            systemAdminProcessError(
                'Please select a valid category.'
            );
        }

        if ($categoryName === '') {
            systemAdminProcessError(
                'Please enter a category name.'
            );
        }

        if (mb_strlen($categoryName) > 100) {
            systemAdminProcessError(
                'Category name cannot exceed 100 characters.'
            );
        }

        $success = updateSystemAdminCategory(
            $pdo,
            $categoryId,
            $categoryName
        );

        if (!$success) {
            systemAdminProcessError(
                'Unable to update the category. It may no longer exist.'
            );
        }

        $_SESSION['system_admin_success'] =
            'Category updated successfully.';


    /*
    |--------------------------------------------------------------------------
    | DELETE CATEGORY
    |--------------------------------------------------------------------------
    */
    } elseif ($action === 'delete_category') {

        $categoryId = filter_input(
            INPUT_POST,
            'delete_category_id',
            FILTER_VALIDATE_INT
        );

        if (!$categoryId || $categoryId < 1) {
            systemAdminProcessError(
                'Please select a valid category.'
            );
        }

        $success = deleteSystemAdminCategory(
            $pdo,
            $categoryId
        );

        if (!$success) {
            systemAdminProcessError(
                'Unable to delete the category. It may no longer exist.'
            );
        }

        $_SESSION['system_admin_success'] =
            'Category deleted successfully.';


    } else {

        systemAdminProcessError(
            'Invalid category action.'
        );
    }

} catch (PDOException $e) {

    error_log(
        'Category processing error: ' . $e->getMessage()
    );

    // PostgreSQL foreign key violation:
    // A club may still be using this category.
    if ($e->getCode() === '23503') {
        systemAdminProcessError(
            'This category cannot be deleted because it is currently assigned to one or more clubs.'
        );
    }

    // PostgreSQL unique violation
    if ($e->getCode() === '23505') {
        systemAdminProcessError(
            'A category with that name already exists.'
        );
    }

    systemAdminProcessError(
        'Something went wrong while processing the category.'
    );
}

systemAdminProcessRedirect();
