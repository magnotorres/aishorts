<?php
// admin/header.php
// This file contains the opening HTML, head section, and navigation bar.

// Get the current script name to highlight the active navigation link.
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <nav class="sidebar">
            <h2>AI Video Bot</h2>
            <ul>
                <li><a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="categories.php" class="<?php echo ($current_page == 'categories.php') ? 'active' : ''; ?>">Categories</a></li>
                <li><a href="videos.php" class="<?php echo ($current_page == 'videos.php') ? 'active' : ''; ?>">Published Videos</a></li>
            </ul>
            <div class="logout-section">
                <a href="logout.php">Logout</a>
            </div>
        </nav>
        <main class="main-content">
            <header class="main-header">
                <h1><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?></h1>
            </header>
            <div class="content-area">
                <!-- Page content starts here -->
