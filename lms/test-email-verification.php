<?php
/**
 * Email Verification Test Script
 * Tests the full registration and verification flow
 */

require_once 'config.php';

$test_email = 'altmedialab@gmail.com';
$test_password = 'Alt@2026';
$test_first_name = 'Test';
$test_last_name = 'User';
$test_role = ROLE_STUDENT;

echo "=== Email Verification Functionality Test ===\n\n";

try {
    $db = getDB();
    
    // Clean up any existing test user
    $existing = $db->fetch("SELECT id FROM users WHERE email = ?", [$test_email]);
    if ($existing) {
        echo "[INFO] Cleaning up existing test user...\n";
        $db->execute("DELETE FROM users WHERE email = ?", [$test_email]);
        echo "[OK] Existing test user removed.\n\n";
    }
    
    // Step 1: Register user
    echo "[STEP 1] Registering user with email: {$test_email}\n";
    $user_id = register_user($test_email, $test_password, $test_first_name, $test_last_name, $test_role);
    echo "[OK] User registered successfully. User ID: {$user_id}\n\n";
    
    // Step 2: Verify database state
    echo "[STEP 2] Checking database state...\n";
    $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$user_id]);
    
    if (!$user) {
        throw new Exception("User not found in database after registration");
    }
    
    echo "  - Email: {$user['email']}\n";
    echo "  - is_verified: " . ($user['is_verified'] ? 'true' : 'false') . "\n";
    echo "  - verification_token: " . ($user['verification_token'] ? substr($user['verification_token'], 0, 16) . '...' : 'NULL') . "\n";
    echo "  - status: {$user['status']}\n\n";
    
    if ($user['is_verified']) {
        throw new Exception("User should NOT be verified immediately after registration");
    }
    
    if (empty($user['verification_token'])) {
        throw new Exception("Verification token should be set after registration");
    }
    
    // Step 3: Check email queue
    echo "[STEP 3] Checking email queue...\n";
    $emails = $db->fetchAll("SELECT * FROM email_queue WHERE recipient_email = ? ORDER BY id DESC LIMIT 1", [$test_email]);
    
    if (empty($emails)) {
        echo "  [WARNING] No email found in queue. Email sending may have failed or been skipped.\n";
    } else {
        $email = $emails[0];
        echo "  - Subject: {$email['subject']}\n";
        echo "  - Type: {$email['type']}\n";
        echo "  - Status: {$email['status']}\n";
        echo "  - Created: {$email['created_at']}\n\n";
        
        // Extract verification link from message
        if (preg_match('/(http[^\s"]+verify-email\.php\?token=[a-f0-9]+)/', $email['message'], $matches)) {
            echo "  [EXTRACTED] Verification Link: {$matches[1]}\n\n";
            $verification_link = $matches[1];
        }
    }
    
    // Step 4: Verify email with token
    echo "[STEP 4] Verifying email with token...\n";
    $token = $user['verification_token'];
    $result = verify_user_email($token);
    
    if ($result) {
        echo "[OK] Email verified successfully!\n";
    } else {
        throw new Exception("Email verification failed - token may be invalid");
    }
    
    // Step 5: Check final state
    echo "\n[STEP 5] Checking final database state...\n";
    $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$user_id]);
    echo "  - is_verified: " . ($user['is_verified'] ? 'true' : 'false') . "\n";
    echo "  - verification_token: " . ($user['verification_token'] ? $user['verification_token'] : 'NULL (cleared)') . "\n\n";
    
    if (!$user['is_verified']) {
        throw new Exception("User should be verified after calling verify_user_email()");
    }
    
    if (!empty($user['verification_token'])) {
        throw new Exception("Verification token should be cleared after verification");
    }
    
    // Step 6: Test login after verification
    echo "[STEP 6] Testing login after verification...\n";
    try {
        $logged_in_user = login_user($test_email, $test_password);
        echo "[OK] Login successful after verification!\n";
        echo "  - Logged in as: {$logged_in_user['first_name']} {$logged_in_user['last_name']}\n";
        echo "  - Role: {$logged_in_user['role']}\n\n";
    } catch (Exception $e) {
        echo "[ERROR] Login failed: " . $e->getMessage() . "\n\n";
    }
    
    echo "=== ALL TESTS PASSED ===\n";
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    echo "=== TEST FAILED ===\n";
    exit(1);
}

