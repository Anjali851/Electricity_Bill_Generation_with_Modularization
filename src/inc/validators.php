<?php
// src/inc/validators.php
// Validation and sanitization helpers

function to_title_case($str) {
    $s = trim($str);
    return mb_convert_case($s, MB_CASE_TITLE, "UTF-8");
}

function validate_name($name) {
    $name = trim($name);
    if ($name === '') return "Name is required.";
    if (!preg_match("/^[\p{L}\s'.-]+$/u", $name)) return "Name must contain letters and spaces only.";
    return true;
}

function validate_contact($contact) {
    $contact = preg_replace('/\D+/', '', $contact);
    if (!preg_match("/^[0-9]{10}$/", $contact)) return "Contact must be 10 digits.";
    return true;
}

function validate_service_no($sno, $required = true) {
    $sno = trim($sno);
    if ($required && $sno === '') return "Service Number is required.";
    if ($sno !== '' && !ctype_digit($sno)) return "Service Number must contain digits only.";
    return true;
}

function esc($val) {
    return htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>