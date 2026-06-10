<?php
// Removed declare()
// declare(strict_types=1);
// Replaced "require_once" statements with composer autoloader
require __DIR__ . '/../../vendor/autoload.php';
/*
require_once __DIR__ . '/../src/Appointment/Appointment.php';
require_once __DIR__ . '/../src/Appointment/ApptRepository.php';
require_once __DIR__ . '/../src/Appointment/ApptService.php';
*/

use Cookbook\Appointment\Appointment;
use Cookbook\Appointment\ApptService;
use Cookbook\Appointment\ApptRepository;
use Cookbook\Appointment\ApptViewHelpers as Helper;

$pdo    = require __DIR__ . '/../../config/ch04_appt_get_pdo.php';
$repo   = new ApptRepository($pdo);
$svc    = new ApptService($repo);

$id     = (int) ($_REQUEST['id'] ?? 0);
$isEdit = $id > 0;
$errors = [];
$appt   = new Appointment();

// ── Load existing record for edit ─────────────────────────────────────────
if ($isEdit && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $loaded = $svc->getById($id);
    if (!$loaded) {
        header('Location: ch04_appointment.php');
        exit;
    }
    $appt = $loaded;
}

// ── Handle form submission ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_REQUEST['action'] ?? 'save';
    // Delete
    if ($action === 'delete' && $isEdit) {
        $target = $svc->getById($id);
        if ($target) {
            $repo->delete($target);
        }
        header('Location: ch04_appointment.php?msg=deleted');
        exit;
    }

    // added strip_tags()
    $title   = trim(strip_tags($_POST['title']               ?? ''));
    $loc     = trim(strip_tags($_POST['location']            ?? ''));
    $contact = trim(strip_tags($_POST['contact_info']        ?? ''));
    $startRaw = trim(strip_tags($_POST['start_date_and_time'] ?? ''));
    $endRaw   = trim(strip_tags($_POST['end_date_and_time']   ?? ''));

    // datetime-local → MySQL datetime (YYYY-MM-DDTHH:MM → YYYY-MM-DD HH:MM:00)
    $startDt = $startRaw !== '' ? str_replace('T', ' ', $startRaw) . ':00' : '';
    $endDt   = $endRaw   !== '' ? str_replace('T', ' ', $endRaw)   . ':00' : '';

    // Validate
    if ($title === '') {
        $errors[] = 'Title is required.';
    } elseif (mb_strlen($title) > 16) {
        $errors[] = 'Title cannot exceed 16 characters.';
    }
    if ($startRaw === '') {
        $errors[] = 'Start date and time is required.';
    }
    if ($endRaw === '') {
        $errors[] = 'End date and time is required.';
    }
    if ($startRaw !== '' && $endRaw !== '' && $endRaw <= $startRaw) {
        $errors[] = 'End date/time must be after start date/time.';
    }

    if (empty($errors)) {
        $appt = new Appointment(
            id:                  $id,
            title:               $title,
            location:            $loc,
            contact_info:        $contact,
            start_date_and_time: $startDt,
            end_date_and_time:   $endDt,
        );
        $ok = $isEdit ? $repo->edit($appt) : $repo->add($appt);
        if ($ok) {
            header('Location: ch04_appointment.php?msg=' . ($isEdit ? 'edited' : 'added'));
            exit;
        }
        $errors[] = 'A database error occurred. Please try again.';
    }

    // Repopulate form values on validation failure
    $appt = new Appointment(
        id:                  $id,
        title:               $title,
        location:            $loc,
        contact_info:        $contact,
        start_date_and_time: $startDt,
        end_date_and_time:   $endDt,
    );
}

$pageTitle = $isEdit ? 'Edit Appointment' : 'New Appointment';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pageTitle ?> &mdash; Appointment Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body    { background: #f0f2f5; }
        .navbar { box-shadow: 0 2px 6px rgba(0,0,0,.18); }
        .card   { border: none; }
        .form-label { font-weight: 500; font-size: .9rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container-lg">
        <a class="navbar-brand fw-bold fs-5" href="ch04_appointment.php">
            <i class="bi bi-calendar2-check me-2"></i>Appointment Manager
        </a>
    </div>
</nav>

<div class="container-lg pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">

            <div class="d-flex align-items-center mb-4">
                <a href="ch04_appointment.php" class="btn btn-sm btn-outline-secondary me-3">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
                <h4 class="mb-0 fw-bold">
                    <i class="bi bi-<?= $isEdit ? 'pencil-square' : 'plus-circle' ?> me-2 text-primary"></i>
                    <?= $pageTitle ?>
                </h4>
            </div>

            <!-- Validation errors -->
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-2 ps-3">
                    <?php foreach ($errors as $e): ?>
                    <li><?= Helper::h($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="post" action="ch04_appointment_edit.php" novalidate>
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <div class="mb-3">
                            <label class="form-label" for="title">
                                Title <span class="text-danger">*</span>
                                <small class="text-muted fw-normal">(max 16 chars)</small>
                            </label>
                            <input type="text" class="form-control <?= in_array('Title is required.', $errors) || in_array('Title cannot exceed 16 characters.', $errors) ? 'is-invalid' : '' ?>"
                                   id="title" name="title"
                                   maxlength="16"
                                   value="<?= Helper::h($appt->title) ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="location">Location</label>
                            <input type="text" class="form-control"
                                   id="location" name="location"
                                   maxlength="255"
                                   value="<?= Helper::h($appt->location) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="contact_info">Contact Info</label>
                            <input type="text" class="form-control"
                                   id="contact_info" name="contact_info"
                                   maxlength="255"
                                   value="<?= Helper::h($appt->contact_info) ?>">
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <label class="form-label" for="start_date_and_time">
                                    Start <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" class="form-control <?= in_array('Start date and time is required.', $errors) ? 'is-invalid' : '' ?>"
                                       id="start_date_and_time" name="start_date_and_time"
                                       value="<?= Helper::h(Helper::dtToInput($appt->start_date_and_time)) ?>"
                                       required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="end_date_and_time">
                                    End <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" class="form-control <?= in_array('End date and time is required.', $errors) || in_array('End date/time must be after start date/time.', $errors) ? 'is-invalid' : '' ?>"
                                       id="end_date_and_time" name="end_date_and_time"
                                       value="<?= Helper::h(Helper::dtToInput($appt->end_date_and_time)) ?>"
                                       required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <button type="submit" name="action" value="save"
                                        class="btn btn-primary px-4">
                                    <i class="bi bi-<?= $isEdit ? 'floppy-fill' : 'plus-lg' ?> me-1"></i>
                                    <?= $isEdit ? 'Save Changes' : 'Add Appointment' ?>
                                </button>
                                <a href="ch04_appointment.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                            <?php if ($isEdit): ?>
                            <form method="post" action="ch04_appointment_edit.php?action=delete"
                                  onsubmit="return confirm('Permanently delete this appointment?')">
                                <input type="hidden" name="id"     value="<?= $id ?>">
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-trash-fill me-1"></i>Delete
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
