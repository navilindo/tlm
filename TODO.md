# Email Verification Implementation

## Plan
1. Create `lms/auth/verify-email.php` - Page to handle email verification token from email link
2. Update `lms/includes/functions.php` - Enhance `send_notification_email()` to build proper verification email
3. Update `lms/includes/auth.php` - Modify `register_user()` to send verification email after registration
4. Create `lms/auth/resend-verification.php` - Page to resend verification email
5. Update `lms/auth/login.php` - Add resend verification link when login fails due to unverified email

## Status
- [ ] Step 1: Create verify-email.php
- [ ] Step 2: Update functions.php send_notification_email()
- [ ] Step 3: Update auth.php register_user()
- [ ] Step 4: Create resend-verification.php
- [ ] Step 5: Update login.php with resend link

