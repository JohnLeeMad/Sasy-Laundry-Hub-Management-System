<?php
session_start();

$allowed = false;
if ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1') {
    $allowed = true;
}

$secret_key = 'laundry_backup_2025';
if (isset($_GET['key']) && $_GET['key'] === $secret_key) {
    $allowed = true;
}

if (!$allowed) {
    die('Access denied. This page is only accessible from localhost or with a valid key.');
}

$db_config = [
    'host' => 'localhost',
    'username' => 'u527675493_laundry',
    'password' => 'Kraynes@021',
    'database' => 'u527675493_laundry'
];

$message = '';
$message_type = '';

try {
    $test_conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    if ($test_conn->connect_error) {
        throw new Exception("Database connection failed: " . $test_conn->connect_error);
    }
    $test_conn->close();
} catch (Exception $e) {
    $message = "Database connection error: " . $e->getMessage();
    $message_type = 'error';
}

if (isset($_POST['backup']) && empty($message)) {
    $message = backupDatabase($db_config);
    $message_type = strpos($message, 'successfully') !== false ? 'success' : 'error';
}

if (isset($_POST['restore']) && isset($_FILES['sql_file']) && empty($message)) {
    $message = restoreDatabase($db_config, $_FILES['sql_file']);
    $message_type = strpos($message, 'successfully') !== false ? 'success' : 'error';
}

function backupDatabase($config) {
    try {
        if (!is_dir('backup')) {
            if (!mkdir('backup', 0755, true)) {
                throw new Exception("Could not create backup directory");
            }
        }
        
        $backup_file = 'backup/laundry_backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        $conn = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8");
        
        $handle = fopen($backup_file, 'w+');
        if (!$handle) {
            throw new Exception("Could not create backup file");
        }
        
        fwrite($handle, "-- Laundry System Database Backup\n");
        fwrite($handle, "-- Backup Date: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Database: {$config['database']}\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");
        
        $tables = [];
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        
        foreach ($tables as $table) {
            fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
            
            $create_result = $conn->query("SHOW CREATE TABLE `$table`");
            if ($create_result) {
                $create_row = $create_result->fetch_row();
                fwrite($handle, $create_row[1] . ";\n\n");
            }
            
            $data_result = $conn->query("SELECT * FROM `$table`");
            if ($data_result->num_rows > 0) {
                fwrite($handle, "-- Dumping data for table `$table`\n");
                
                while ($row = $data_result->fetch_assoc()) {
                    $values = array_map(function($value) use ($conn) {
                        if ($value === null) return 'NULL';
                        return "'" . $conn->real_escape_string($value) . "'";
                    }, array_values($row));
                    
                    $columns = array_keys($row);
                    $column_list = '`' . implode('`, `', $columns) . '`';
                    $value_list = implode(', ', $values);
                    
                    fwrite($handle, "INSERT INTO `$table` ($column_list) VALUES ($value_list);\n");
                }
                fwrite($handle, "\n");
            }
        }
        
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
        $conn->close();
        
        if (file_exists($backup_file)) {
            $file_size = round(filesize($backup_file) / 1024, 2);
            return "Backup created successfully! File: " . basename($backup_file) . 
                   " (" . $file_size . " KB) - " . 
                   "<a href='$backup_file' download class='btn btn-sm btn-success mt-2'>Download Backup</a>";
        } else {
            throw new Exception("Backup file was not created");
        }
        
    } catch (Exception $e) {
        return "Backup failed: " . $e->getMessage();
    }
}

function restoreDatabase($config, $sql_file) {
    try {
        if ($sql_file['error'] !== UPLOAD_ERR_OK) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
            ];
            return "Upload error: " . ($upload_errors[$sql_file['error']] ?? 'Unknown error');
        }
        
        $file_type = strtolower(pathinfo($sql_file['name'], PATHINFO_EXTENSION));
        if ($file_type !== 'sql') {
            return "Please upload a valid SQL file (.sql extension required).";
        }
        
        if ($sql_file['size'] > 50000000) {
            return "File size too large. Maximum allowed size is 50MB.";
        }
        
        $conn = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }
        
        $sql = file_get_contents($sql_file['tmp_name']);
        if ($sql === false) {
            throw new Exception("Could not read the SQL file");
        }
        
        $queries = parseSql($sql);
        $success_count = 0;
        $error_count = 0;
        $errors = [];
        
        foreach ($queries as $index => $query) {
            $query = trim($query);
            if (!empty($query)) {
                if ($conn->query($query) === TRUE) {
                    $success_count++;
                } else {
                    $error_count++;
                    $errors[] = "Query " . ($index + 1) . " failed: " . $conn->error . " (First 100 chars: " . substr($query, 0, 100) . "...)";
                }
            }
        }
        
        $conn->close();
        
        $result_message = "Restore completed. $success_count queries executed successfully.";
        if ($error_count > 0) {
            $result_message .= " $error_count queries failed.";
            if ($error_count <= 5) {
                $result_message .= "<br>Errors:<br>" . implode("<br>", array_slice($errors, 0, 5));
            }
        }
        
        return $result_message;
        
    } catch (Exception $e) {
        return "Restore failed: " . $e->getMessage();
    }
}

function parseSql($sql) {
    $queries = [];
    $current_query = '';
    $in_string = false;
    $string_char = '';
    $escaped = false;
    
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    $length = strlen($sql);
    
    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        
        if ($escaped) {
            $current_query .= $char;
            $escaped = false;
            continue;
        }
        
        if ($char === '\\') {
            $escaped = true;
            $current_query .= $char;
            continue;
        }
        
        if (($char === "'" || $char === '"') && !$in_string) {
            $in_string = true;
            $string_char = $char;
            $current_query .= $char;
        } elseif ($char === $string_char && $in_string) {
            $in_string = false;
            $string_char = '';
            $current_query .= $char;
        } elseif ($char === ';' && !$in_string) {
            $queries[] = trim($current_query);
            $current_query = '';
        } else {
            $current_query .= $char;
        }
    }
    
    if (!empty(trim($current_query))) {
        $queries[] = trim($current_query);
    }
    
    return array_filter($queries, function($query) {
        return !empty(trim($query));
    });
}

function restoreDatabaseAlternative($config, $sql_file) {
    try {
        if ($sql_file['error'] !== UPLOAD_ERR_OK) {
            return "Upload error: " . $sql_file['error'];
        }
        
        $command = "mysql --user={$config['username']} --password={$config['password']} " .
                  "--host={$config['host']} {$config['database']} < \"{$sql_file['tmp_name']}\" 2>&1";
        
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            return "Database restored successfully using MySQL command line tool.";
        } else {
            return "Restore failed. MySQL error: " . implode("\n", $output);
        }
        
    } catch (Exception $e) {
        return "Restore failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup & Restore - Laundry System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #644499;
            --accent-color: #c7345c;
            --light-bg: #f8f9fa;
            --text-dark: #212529;
            --text-light: #f8f9fa;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Montserrat', sans-serif;
            padding: 20px 0;
        }

        .backup-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            position: relative;
            transition: all 0.3s ease;
            border: none;
        }

        .backup-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        .backup-header {
            background-color: white;
            border-bottom: none;
            padding: 2rem 1rem 1rem;
            text-align: center;
        }

        .backup-header h2 {
            color: var(--primary-color);
            font-weight: 600;
        }

        .backup-body {
            padding: 2rem;
        }

        .btn-backup {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-restore {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-backup:hover, .btn-restore:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(177, 0, 124, 0.4);
        }

        .btn-backup:hover {
            background-color: #281c52;
            border-color: #281c52;
        }

        .btn-restore:hover {
            background-color: #99006a;
            border-color: #99006a;
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .status-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-color);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e1e1e1;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(177, 0, 124, 0.25);
        }

        .form-label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .restore-method {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .form-check-input:checked {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-secondary {
            color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .btn-outline-secondary:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
        }

        @media (max-width: 576px) {
            .backup-card {
                margin: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="backup-card">
                    <div class="backup-header">
                        <h2><i class="fas fa-database me-2"></i>Database Backup & Restore</h2>
                        <p class="mb-0 text-muted">Laundry Management System - Emergency Access</p>
                    </div>
                    
                    <div class="backup-body">
                        <div class="status-box">
                            <h5><i class="fas fa-info-circle me-2"></i>Database Status</h5>
                            <?php if (empty($message) || $message_type === 'success'): ?>
                                <span class="badge bg-success">Connected to database: <?php echo $db_config['database']; ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger">Connection Error</span>
                                <p class="text-muted small mt-2">Check your database configuration in the script</p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                                <h5><i class="fas fa-<?php echo $message_type === 'success' ? 'check' : 'exclamation-triangle'; ?> me-2"></i>
                                    <?php echo $message_type === 'success' ? 'Success' : 'Error'; ?>
                                </h5>
                                <?php echo strpos($message, '<a') !== false ? $message : nl2br(htmlspecialchars($message)); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="text-center p-4 border rounded h-100 d-flex flex-column">
                                    <i class="fas fa-download feature-icon"></i>
                                    <h4>Backup Database</h4>
                                    <p class="text-muted flex-grow-1">Create a complete backup of your laundry system database</p>
                                    <form method="POST" class="mt-auto">
                                        <button type="submit" name="backup" class="btn btn-backup">
                                            <i class="fas fa-download me-2"></i>Backup Now
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="text-center p-4 border rounded h-100 d-flex flex-column">
                                    <i class="fas fa-upload feature-icon"></i>
                                    <h4>Restore Database</h4>
                                    <p class="text-muted flex-grow-1">Restore database from a previously created backup file</p>
                                    <form method="POST" enctype="multipart/form-data" class="mt-auto">
                                        <div class="mb-3">
                                            <label for="sql_file" class="form-label small">Select SQL Backup File</label>
                                            <input type="file" name="sql_file" class="form-control" accept=".sql" required>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="use_mysql_tool" id="use_mysql_tool">
                                            <label class="form-check-label restore-method" for="use_mysql_tool">
                                                Use MySQL command line tool (more reliable for complex files)
                                            </label>
                                        </div>
                                        <button type="submit" name="restore" class="btn btn-restore">
                                            <i class="fas fa-upload me-2"></i>Restore Database
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-exclamation-triangle me-2"></i>Important Notes:</h5>
                                <ul class="mb-0">
                                    <li>Backup files are saved in the <code>backup/</code> directory</li>
                                    <li><strong>Restoring will replace ALL current data with backup data</strong></li>
                                    <li>Always create a backup before performing a restore</li>
                                    <li>If PHP restore fails, try the MySQL command line option</li>
                                    <li>This page is accessible without main system login for emergency recovery</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <a href="auth/unified-login.php" class="btn btn-outline-primary me-2">
                                <i class="fas fa-arrow-left me-2"></i>Return to Main System
                            </a>
                            <a href="?key=<?php echo $secret_key; ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Refresh Page
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 10000);
            });
        });
    </script>
</body>
</html>