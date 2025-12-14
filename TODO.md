# TODO List for Student Account Creation and Forgot Password Enhancements

## Database Schema Update
- [ ] Update sql/schema.sql to replace 'full_name' with 'first_name', 'middle_name', 'last_name'

## Admin Create Student
- [ ] Modify admin/create_student.php to construct 'full_name' from name fields, make middle_name optional, ensure all fields except middle_name are required

## Email Helper Updates
- [ ] Update classes/EmailHelper.php to change sender to angelobadi124@gmail.com, system name, and add new passwordResetFormEmail method with embedded HTML form

## New Password Reset Endpoint
- [ ] Create new student/password_reset_form.php endpoint to handle POST from email form

## Forgot Password Updates
- [ ] Update forgot_password.php to use the new email method instead of linking to password_reset.php

## Followup Steps
- [ ] Run schema update script
- [ ] Test student creation and login
- [ ] Test forgot password email and form submission
