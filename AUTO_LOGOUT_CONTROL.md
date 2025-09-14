# Auto Logout Control Implementation

This documentation explains the implementation of the supervisor-controlled auto-logout feature for student exams.

## Overview

The auto-logout feature automatically logs students out when they switch tabs or minimize the browser during an exam. This is a security measure to prevent cheating. The new implementation allows exam supervisors to enable or disable this feature for each exam.

## Changes Made

1. **Database Changes**

    - Added `aktifkan_auto_logout` column to the `jadwal_ujian` table
    - Created a migration file: `2023_11_05_add_aktifkan_auto_logout_to_jadwal_ujian_table.php`

2. **Model Changes**

    - Added `aktifkan_auto_logout` to the `$fillable` array in the `JadwalUjian` model
    - Added `aktifkan_auto_logout` to the `$casts` array as a boolean in the `JadwalUjian` model

3. **Controller Changes**

    - Updated `SiswaDashboardController` to include the new setting in the exam settings array
    - Updated `JadwalUjianController` to handle the new field in both `store` and `update` methods

4. **View Changes**
    - Added a checkbox for `aktifkan_auto_logout` in the create form (`create.blade.php`)
    - Added a checkbox for `aktifkan_auto_logout` in the edit form (`edit.blade.php`)
    - Added the status of this feature in the show view (`show.blade.php`)
    - Updated the JavaScript function `setupVisibilityChangeDetection` in `exam.blade.php` to check if auto-logout is enabled

## How It Works

1. When creating or editing an exam, the supervisor can check or uncheck the "Aktifkan Auto Logout" option.
2. This setting is saved in the database and associated with the specific exam.
3. When a student takes the exam, the system reads this setting and decides whether to enable or disable the auto-logout feature.
4. If enabled, switching tabs or minimizing the browser will trigger warnings and potentially log the student out.
5. If disabled, students can switch tabs without being logged out.

## Default Behavior

For backward compatibility, the auto-logout feature is enabled by default for all exams unless explicitly disabled.
