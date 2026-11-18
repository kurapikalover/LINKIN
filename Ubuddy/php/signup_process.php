<?php
//Handles the sign-up form and sama mcm login.php which includes login_process.php

if (session_status()== PHP_SESSION_NONE){
    session_start();
}

require_once __DIR__ .'/db.php';

//Basically read messages saved by the previous request (a "flash")
// They are remove tarys after reading so kana show only once

$errors = $_SESSION['signup_errors'] ?? [];
$old = $_SESSION['signup_old'] ?? ['name' => '', 'email' => ''];
unset($_SESSION['signup_errors'], $_SESSION['signup_old']);

//CSRF token: stops other websites from submitting this form for the user
if (empty($_SESSION['csrf'])){
    $_SESSION['csrf']= bin2hex(random_bytes(32));
}

//Process the form when it is submitted 
if ($_SERVER['REQUEST_METHOD'] === 'POST'){

    if(!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')){
        http_response_code(400);
        exit('Invalid request');
    }
    $name= trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $errors = [];

    //Validate where js check can be bypassed so repeat it sini
    if ($name == '' || mb_strlen($name) > 100) {
        $errors[] = 'Please enter your full name.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 8){
        $error[] = 'Password must be at least 8 characters.';
    }

    //bcrypt (PASSWORD_DEFAULT) only reads the first 72 bytes
    if (strlen($password) > 72){
        $errors[] = 'Password must be 72 characters or fewer.';
    }

    if ($password !== $confirmPassword){
        $errors[] = 'Passwords do not match.';
    }

    // Is the email already registered? sama LOWER() style as login
    if (!$errors){
        $stmt = $pdo->prepare(
            'SELECT 1 FROM public.users WHERE LOWER(email) = LOWER (:email) LIMIT 1'
        );
        $stmt->execute(['email' => $email]);

        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    //Save the new user 
    if (!$errors){
        try{
            $stmt = $pdo->prepare("
                INSERT INTO public.users (name, eamil, password, user_type)
                VALUES (:name, :email, :password, 'Student')
            ");
            //user_type is hard-coded to 'Student' on purpose
            //NEVER read it from $_POST or anyone could sign up as System Admin.

            $stmt->execute([
                'name' => $name.
                'email' => strtolower($email),
                'password' => password_hash($password, PASSWORD_DEFAULT), //store hash only
            ]);

            //Success: send them to the login page with a message
            unset($_SESSION['csrf']);
            $_SESSION['signup_success']= 'Account created! Please log in.';
            header('Location; login.php')
            exit;
        } catch (PDOException $e){
            //23505= unique violation where two sign upswith the same email at once 
            if ($e->getCode() === '23505'){
                $errors[] = 'An account with this email already exists';
            } else{
                error_log($e->getMessage()); //details go to the server log, not rah user
                $errors[]='Something went wrong. Please try again.';
            }
        }
    }

    //Something failed: save errors and what they typed then redirect
    //redirecting means refreshing the page wont ask to resubmit the form
    //Passwords are never saved or refilled
    $_SESSION['signup_errors']= $errors;
    $_SESSION['signup_old'] = ['name'=> $name, 'email'=> $email];
    header('Location:signup.php');
    exit;
}