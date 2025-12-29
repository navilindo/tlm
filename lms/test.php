<?php
// Simple test to check for function redeclaration errors
echo "Testing basic LMS structure...\n";

try {
    require_once 'config.php';
    echo "✓ Config loaded successfully\n";
    
    if (function_exists('get_current_user')) {
        echo "✓ get_current_user function exists\n";
    } else {
        echo "✗ get_current_user function not found\n";
    }
    
    if (function_exists('is_logged_in')) {
        echo "✓ is_logged_in function exists\n";
    } else {
        echo "✗ is_logged_in function not found\n";
    }
    
    echo "✓ No fatal errors detected\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
