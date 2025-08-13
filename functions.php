<?php

/**
 * functions.php
 *
 * Global functions library for the Video Automation project.
 * This file contains all the core logic for database interactions,
 * API communications, social media operations, and business intelligence.
 */

// Ensure config is loaded
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} else {
    die("FATAL ERROR: config.php not found. Please create it based on the template.");
}

// =============================================================================
// 1. DATABASE FUNCTIONS
// =============================================================================

/**
 * Establishes and returns a PDO database connection.
 * Uses a static variable to ensure a single connection per request (Singleton pattern).
 *
 * @return PDO|null The PDO database object or null on failure.
 */
function get_db_connection() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            log_message("DB Connection Error: " . $e->getMessage(), "ERROR");
            die("Database connection failed. Check logs for details.");
        }
    }
    return $pdo;
}

// =============================================================================
// 2. LOGGING & UTILITIES
// =============================================================================

/**
 * Logs a message to a file in the defined logs directory.
 *
 * @param string $message The message to log.
 * @param string $level The log level (e.g., INFO, WARNING, ERROR).
 */
function log_message($message, $level = "INFO") {
    $log_file = DIR_LOGS . '/app_' . date('Y-m-d') . '.log';
    $formatted_message = "[" . date('Y-m-d H:i:s') . "] [" . $level . "] " . $message . PHP_EOL;
    file_put_contents($log_file, $formatted_message, FILE_APPEND);
}

/**
 * Securely gets social account credentials for a specific subject.
 * NOTE: In a real application, decryption logic would be here.
 *
 * @param string $subject The subject (e.g., 'financas').
 * @return array An array of social accounts.
 */
function get_social_accounts_for_subject($subject) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM social_accounts WHERE subject = ?");
    $stmt->execute([$subject]);
    $accounts = $stmt->fetchAll();

    // Placeholder for decryption
    foreach ($accounts as &$account) {
        // In a real app: $account['credentials'] = decrypt($account['credentials']);
        $account['credentials'] = json_decode($account['credentials'], true);
    }
    return $accounts;
}

/**
 * Ensures a social account from a personality file exists in the database.
 * If it exists, it updates the credentials. If not, it creates it.
 *
 * @param string $subject The subject of the account.
 * @param array $account_details The account details from the personality file.
 */
function sync_social_account($subject, $account_details) {
    $pdo = get_db_connection();
    $sql_find = "SELECT id FROM social_accounts WHERE subject = ? AND platform = ? AND account_name = ?";
    $stmt_find = $pdo->prepare($sql_find);
    $stmt_find->execute([$subject, $account_details['platform'], $account_details['account_name']]);
    $existing_id = $stmt_find->fetchColumn();

    if ($existing_id) {
        // Update existing account's credentials
        $sql_update = "UPDATE social_accounts SET credentials = ? WHERE id = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$account_details['credentials'], $existing_id]);
        log_message("Synced (updated) credentials for account: {$account_details['account_name']} on {$account_details['platform']}");
    } else {
        // Insert new account
        $sql_insert = "INSERT INTO social_accounts (subject, platform, account_name, credentials) VALUES (?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([$subject, $account_details['platform'], $account_details['account_name'], $account_details['credentials']]);
        log_message("Synced (created) new account record: {$account_details['account_name']} on {$account_details['platform']}");
    }
}


// =============================================================================
// 3. AI & CONTENT GENERATION
// =============================================================================

/**
 * Simulates calling the Google Veo API to generate a video.
 * All prompts should request a vertical 9:16 aspect ratio.
 *
 * @param string $prompt The text prompt for the video.
 * @return string|false The path to the generated video file or false on failure.
 */
function generate_video_from_prompt($prompt) {
    log_message("Generating video for prompt: '{$prompt}'");

    // ** SIMULATION **
    // In a real implementation, this would be an API call to Google Veo.
    // Example: $videoData = GoogleVeoClient::generate($prompt);

    $video_filename = 'video_' . time() . '_' . uniqid() . '.mp4';
    $video_path = DIR_VIDEOS . '/' . $video_filename;

    // Create a dummy file to simulate the video
    file_put_contents($video_path, "Simulated video content for prompt: " . $prompt);

    log_message("Video generated and saved to: {$video_path}");
    return $video_path;
}

/**
 * Simulates using an LLM to generate text content (e.g., title, description).
 *
 * @param string $base_prompt The prompt for the text generation.
 * @return string The generated text.
 */
function generate_text_content($base_prompt) {
    log_message("Generating text for prompt: '{$base_prompt}'");
    // ** SIMULATION **
    // Replace with a real LLM API call.
    return $base_prompt . " - " . date('Y-m-d H:i');
}


// =============================================================================
// 4. SOCIAL MEDIA & PUBLISHING
// =============================================================================

/**
 * A generic placeholder for publishing content to a social platform.
 *
 * @param array $account The social account details.
 * @param string $video_path Path to the video file.
 * @param string $title The title for the post.
 * @param string $description The description for the post.
 * @return string A simulated URL to the new post.
 */
function publish_to_platform($account, $video_path, $title, $description) {
    $platform = $account['platform'];
    log_message("Attempting to publish to {$platform} for account {$account['account_name']}.");

    // ** SIMULATION **
    // This is where you would integrate the specific SDK for each platform.
    // e.g., for YouTube: use the `google/apiclient` library.
    // e.g., for Facebook: use the `facebook/graph-sdk`.

    // Simulate a successful upload
    sleep(2); // Simulate API call latency
    $post_url = "https://www.{$platform}.com/post/" . uniqid();
    log_message("Successfully published to {$platform}. URL: {$post_url}");
    return $post_url;
}

/**
 * Simulates fetching new metrics for a single published video.
 *
 * @param array $video The video record from the database.
 * @return array An array of new metrics.
 */
function fetch_platform_metrics($video) {
    log_message("Fetching metrics for video ID {$video['id']} from {$video['post_url']}");
    // ** SIMULATION **
    // In reality, you'd use the API of the platform to get real data.
    return [
        'views' => rand(100, 5000),
        'likes' => rand(10, 500),
        'comments' => rand(2, 50),
        'shares' => rand(1, 20)
    ];
}

/**
 * Simulates liking and replying to all comments on a post.
 *
 * @param array $account The social account to use for authentication.
 * @param string $post_url The URL of the post to interact with.
 */
function interact_with_comments($account, $post_url) {
    $platform = $account['platform'];
    log_message("Checking comments for post on {$platform}: {$post_url}");

    // ** SIMULATION **
    // 1. Fetch comments using the platform's API.
    // 2. Iterate through comments.
    // 3. If a comment hasn't been interacted with:
    //    - Call the API to "like" the comment.
    //    - Call an LLM to generate a reply.
    //    - Call the API to post the reply.
    //    - Mark the comment as "handled" in your database (would require another table).

    $num_comments = rand(1, 5);
    log_message("Found {$num_comments} new comments. Liking and replying to all.");
    sleep(1);
    log_message("Comment interaction complete for {$post_url}.");
}


// =============================================================================
// 5. CORE BUSINESS LOGIC & STRATEGY
// =============================================================================

/**
 * Updates metrics for all published videos for a given subject.
 *
 * @param string $subject The subject to update metrics for.
 */
function update_all_metrics($subject) {
    log_message("Starting metrics update for subject: {$subject}");
    $pdo = get_db_connection();

    $sql = "SELECT pv.* FROM published_videos pv
            JOIN social_accounts sa ON pv.social_account_id = sa.id
            WHERE sa.subject = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$subject]);
    $videos = $stmt->fetchAll();

    foreach ($videos as $video) {
        $new_metrics = fetch_platform_metrics($video);

        $insert_sql = "INSERT INTO video_metrics (video_id, views, likes, comments, shares) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->execute([
            $video['id'],
            $new_metrics['views'],
            $new_metrics['likes'],
            $new_metrics['comments'],
            $new_metrics['shares']
        ]);

        // Also, interact with comments for this video post
        $account_stmt = $pdo->prepare("SELECT * FROM social_accounts WHERE id = ?");
        $account_stmt->execute([$video['social_account_id']]);
        $account = $account_stmt->fetch();
        if ($account) {
            interact_with_comments($account, $video['post_url']);
        }
    }
    log_message("Metrics update completed for subject: {$subject}");
}

/**
 * Recalculates the priority_score for all active categories based on performance.
 */
function recalculate_category_priority_scores() {
    log_message("Recalculating category priority scores.");
    $pdo = get_db_connection();

    $sql = "
        UPDATE categories c
        SET priority_score = (
            SELECT
                -- Weighted average of metrics, giving more weight to recent videos.
                -- Default to 1.0 if no metrics exist.
                COALESCE(SUM(vm.views * 0.5 + vm.likes * 0.3 + vm.comments * 0.1 + vm.shares * 0.1) / COUNT(pv.id), 1.0)
            FROM published_videos pv
            JOIN video_metrics vm ON pv.id = vm.video_id
            WHERE pv.category_id = c.id
        )
        WHERE c.is_active = 1;
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    log_message("Priority scores recalculated. {$stmt->rowCount()} categories updated.");
}

/**
 * Selects the best category to use for the next video.
 *
 * @return array|false The selected category record or false if none found.
 */
function get_most_engaging_category() {
    log_message("Selecting most engaging category.");
    $pdo = get_db_connection();

    // Select the active category with the highest score that hasn't been used in the last 3 days.
    // If all have been used recently, it will pick the one with the highest score.
    $sql = "SELECT * FROM categories
            WHERE is_active = 1
            ORDER BY
                (CASE WHEN last_used_at IS NULL OR last_used_at < NOW() - INTERVAL 3 DAY THEN 1 ELSE 0 END) DESC,
                priority_score DESC,
                last_used_at ASC
            LIMIT 1";

    $stmt = $pdo->query($sql);
    $category = $stmt->fetch();

    if ($category) {
        log_message("Selected category: '{$category['name']}' (Score: {$category['priority_score']})");
    } else {
        log_message("No suitable category found.", "WARNING");
    }
    return $category;
}

/**
 * Records a newly published video in the database.
 *
 * @param int $category_id
 * @param int $social_account_id
 * @param string $prompt
 * @param string $video_path
 * @param string $post_url
 * @return int The ID of the new record.
 */
function record_publication($category_id, $social_account_id, $prompt, $video_path, $post_url) {
    $pdo = get_db_connection();
    $sql = "INSERT INTO published_videos (category_id, social_account_id, prompt_used, video_path, post_url)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category_id, $social_account_id, $prompt, $video_path, $post_url]);
    return $pdo->lastInsertId();
}

/**
 * Updates the last_used_at timestamp for a category.
 *
 * @param int $category_id
 */
function update_category_last_used($category_id) {
    $pdo = get_db_connection();
    $sql = "UPDATE categories SET last_used_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category_id]);
}

/**
 * Generates initial categories if the table is empty.
 *
 * @param string $subject The subject to generate categories for.
 */
function create_initial_categories($subject) {
    $pdo = get_db_connection();
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    if ($stmt->fetch()['count'] == 0) {
        log_message("No categories found. Generating initial set for subject '{$subject}'.");
        // ** SIMULATION **
        // Use an LLM to generate a list of 5-10 relevant English categories.
        $simulated_categories = [
            "Quick " . ucfirst($subject) . " Tips",
            ucfirst($subject) . " Market Analysis",
            "Breaking News in " . ucfirst($subject),
            ucfirst($subject) . " for Beginners",
            "Myths and Facts about " . ucfirst($subject)
        ];

        $sql = "INSERT INTO categories (name) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        foreach ($simulated_categories as $cat_name) {
            $stmt->execute([$cat_name]);
        }
        log_message(count($simulated_categories) . " initial categories created.");
    }
}

?>
