<?php
/**
 * AnimeCube Setup Test & Verification Page
 * This page helps verify that your installation is configured correctly
 */

// Start output buffering to catch any errors
ob_start();

// Configuration
$errors = [];
$warnings = [];
$success = [];

// Test 1: PHP Version
$phpVersion = phpversion();
if (version_compare($phpVersion, '7.4.0', '>=')) {
    $success[] = "PHP Version: $phpVersion ✓";
} else {
    $errors[] = "PHP Version: $phpVersion (Requires 7.4 or higher)";
}

// Test 2: Required PHP Extensions
$requiredExtensions = ['mysqli', 'curl', 'json', 'session'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        $success[] = "PHP Extension '$ext': Loaded ✓";
    } else {
        $errors[] = "PHP Extension '$ext': Not loaded ✗";
    }
}

// Test 3: Database Connection
try {
    include_once "./Database/db.php";

    if ($conn && !$conn->connect_error) {
        $success[] = "Database Connection: Success ✓";

        // Test 4: Check if tables exist
        $tables = ['users', 'anime', 'user_favorites', 'user_watchlist'];
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                $success[] = "Table '$table': Exists ✓";

                // Count records
                $countResult = $conn->query("SELECT COUNT(*) as count FROM `$table`");
                if ($countResult) {
                    $row = $countResult->fetch_assoc();
                    $success[] = "Table '$table' has {$row['count']} record(s)";
                }
            } else {
                $errors[] = "Table '$table': Does not exist ✗";
            }
        }

        // Check for sample anime data
        $animeCount = $conn->query("SELECT COUNT(*) as count FROM `anime`");
        if ($animeCount) {
            $animeRow = $animeCount->fetch_assoc();
            if ($animeRow['count'] > 0) {
                $success[] = "Sample anime data: {$animeRow['count']} anime found ✓";
            } else {
                $warnings[] = "No anime data found. Consider running the sample data insert from schema.sql";
            }
        }

    } else {
        $errors[] = "Database Connection: Failed ✗ - " . ($conn->connect_error ?? "Unknown error");
    }
} catch (Exception $e) {
    $errors[] = "Database Test: Exception - " . $e->getMessage();
}

// Test 5: File Structure
$requiredFiles = [
    'client/apiCall.php',
    'client/Card.php',
    'client/commonFile.php',
    'client/Content.php',
    'client/header.php',
    'client/login.php',
    'client/signup.php',
    'Database/db.php',
    'Database/schema.sql',
    'server/requests.php',
    'server/userActions.php',
    'public/style.css'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        $success[] = "File '$file': Exists ✓";
    } else {
        $errors[] = "File '$file': Missing ✗";
    }
}

// Test 6: Directory Permissions
$directories = ['client', 'server', 'Database', 'public'];
foreach ($directories as $dir) {
    if (is_readable($dir)) {
        $success[] = "Directory '$dir': Readable ✓";
    } else {
        $warnings[] = "Directory '$dir': Not readable or doesn't exist";
    }
}

// Test 7: API Connection Test
$apiTestResult = @file_get_contents('https://api.animechan.io/v1/quotes/random');
if ($apiTestResult !== false) {
    $apiData = json_decode($apiTestResult, true);
    if (isset($apiData['data'])) {
        $success[] = "AnimeChan API: Connection successful ✓";
    } else {
        $warnings[] = "AnimeChan API: Connected but unexpected response format";
    }
} else {
    $warnings[] = "AnimeChan API: Unable to connect (may be rate-limited or offline)";
}

// Test 8: Session Test
session_start();
$_SESSION['test'] = 'working';
if (isset($_SESSION['test']) && $_SESSION['test'] === 'working') {
    $success[] = "PHP Sessions: Working ✓";
    unset($_SESSION['test']);
} else {
    $errors[] = "PHP Sessions: Not working ✗";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnimeCube - Setup Test</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .summary {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .summary-card {
            flex: 1;
            min-width: 200px;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .summary-card.success {
            background: #d4edda;
            border: 2px solid #28a745;
        }

        .summary-card.error {
            background: #f8d7da;
            border: 2px solid #dc3545;
        }

        .summary-card.warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
        }

        .summary-card h3 {
            font-size: 40px;
            margin-bottom: 5px;
        }

        .summary-card p {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .section {
            margin-bottom: 25px;
        }

        .section h2 {
            font-size: 20px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
            color: #333;
        }

        .test-item {
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 6px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .test-item.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .test-item.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .test-item.warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }

        .icon {
            font-size: 18px;
        }

        .actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            text-align: center;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 5px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #5568d3;
        }

        .btn-success {
            background: #28a745;
        }

        .btn-success:hover {
            background: #218838;
        }

        .no-items {
            text-align: center;
            padding: 20px;
            color: #999;
            font-style: italic;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .status-badge.ready {
            background: #28a745;
            color: white;
        }

        .status-badge.issues {
            background: #dc3545;
            color: white;
        }

        .status-badge.warnings {
            background: #ffc107;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎬 AnimeCube Setup Test</h1>
            <p>Verify your installation configuration</p>
            <?php
            if (count($errors) === 0 && count($warnings) === 0) {
                echo '<span class="status-badge ready">✓ Ready to Use</span>';
            } elseif (count($errors) > 0) {
                echo '<span class="status-badge issues">✗ Issues Found</span>';
            } else {
                echo '<span class="status-badge warnings">⚠ Warnings Only</span>';
            }
            ?>
        </div>

        <div class="content">
            <!-- Summary Cards -->
            <div class="summary">
                <div class="summary-card success">
                    <h3><?php echo count($success); ?></h3>
                    <p>Passed</p>
                </div>
                <div class="summary-card error">
                    <h3><?php echo count($errors); ?></h3>
                    <p>Errors</p>
                </div>
                <div class="summary-card warning">
                    <h3><?php echo count($warnings); ?></h3>
                    <p>Warnings</p>
                </div>
            </div>

            <!-- Success Items -->
            <?php if (!empty($success)): ?>
                <div class="section">
                    <h2>✓ Passed Tests</h2>
                    <?php foreach ($success as $item): ?>
                        <div class="test-item success">
                            <span class="icon">✓</span>
                            <span><?php echo htmlspecialchars($item); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Error Items -->
            <?php if (!empty($errors)): ?>
                <div class="section">
                    <h2>✗ Errors (Must Fix)</h2>
                    <?php foreach ($errors as $item): ?>
                        <div class="test-item error">
                            <span class="icon">✗</span>
                            <span><?php echo htmlspecialchars($item); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Warning Items -->
            <?php if (!empty($warnings)): ?>
                <div class="section">
                    <h2>⚠ Warnings (Optional)</h2>
                    <?php foreach ($warnings as $item): ?>
                        <div class="test-item warning">
                            <span class="icon">⚠</span>
                            <span><?php echo htmlspecialchars($item); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="actions">
                <a href="test_setup.php" class="btn">🔄 Refresh Test</a>
                <?php if (count($errors) === 0): ?>
                    <a href="index.php" class="btn btn-success">🚀 Go to AnimeCube</a>
                <?php endif; ?>
            </div>

            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 6px; font-size: 13px; color: #666;">
                <strong>💡 Quick Fixes:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>If database connection fails: Check <code>Database/db.php</code> credentials</li>
                    <li>If tables don't exist: Import <code>Database/schema.sql</code> in phpMyAdmin</li>
                    <li>If files are missing: Verify all project files are uploaded correctly</li>
                    <li>If API fails: Check internet connection or wait (rate limit may apply)</li>
                    <li>If session fails: Check PHP session configuration in php.ini</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
