<?php
// test-session.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Session System</h2>";

// Test 1: Cek session sebelum config
echo "<h3>1. Session sebelum include config.php:</h3>";
echo "session_status(): " . session_status() . "<br>";
echo "session_id(): " . (session_id() ?: 'NO SESSION') . "<br>";
echo "session_name(): " . session_name() . "<br>";

// Test 2: Include config
require_once 'includes/config.php';

echo "<h3>2. Session setelah include config.php:</h3>";
echo "session_status(): " . session_status() . "<br>";
echo "session_id(): " . session_id() . "<br>";
echo "session_name(): " . session_name() . "<br>";

echo "<h3>3. Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>4. Cookies:</h3>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

echo "<h3>5. Test isLoggedIn():</h3>";
require_once 'includes/functions.php';
echo "isLoggedIn(): " . (isLoggedIn() ? 'TRUE' : 'FALSE') . "<br>";
echo "isPremium(): " . (isPremium() ? 'TRUE' : 'FALSE') . "<br>";

// Test manual login
echo "<h3>6. Test Manual Login:</h3>";
echo "<form method='POST' action=''>";
echo "User ID: <input type='text' name='user_id' value='5'><br>";
echo "Username: <input type='text' name='username' value='satz2'><br>";
echo "Membership: 
    <select name='membership'>
        <option value='free'>Free</option>
        <option value='premium' selected>Premium</option>
    </select><br>";
echo "<button type='submit' name='login_test'>Set Session</button>";
echo "</form>";

if (isset($_POST['login_test'])) {
    $_SESSION['user_id'] = $_POST['user_id'];
    $_SESSION['username'] = $_POST['username'];
    $_SESSION['membership_status'] = $_POST['membership'];
    $_SESSION['email'] = 'test@example.com';
    $_SESSION['full_name'] = 'Test User';
    $_SESSION['role'] = 'user';
    
    echo "<p style='color:green;'>✓ Session set!</p>";
    echo "<p>Reload halaman untuk test.</p>";
}

echo "<h3>7. Test Links:</h3>";
echo "<a href='test-session.php'>Test Session</a><br>";
echo "<a href='download.php?id=1'>Test Download ID 1</a><br>";
echo "<a href='catalog.php'>Lihat Catalog</a>";
?>