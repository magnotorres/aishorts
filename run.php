<?php

/**
 * run.php
 *
 * Main CLI orchestrator for the Video Automation project.
 *
 * Usage: php run.php <personality-file.php>
 * Example: php run.php financas-ai.php
 */

// -----------------------------------------------------------------------------
// 1. INITIALIZATION & VALIDATION
// -----------------------------------------------------------------------------

// Ensure we are running from the command line
if (php_sapi_name() !== 'cli') {
    die("This script can only be executed from the command line (CLI).");
}

// Check for the personality file argument
if (!isset($argv[1])) {
    die("Usage: php run.php <personality-file.php>\nExample: php run.php financas-ai.php\n");
}

$personality_file = $argv[1];
if (!file_exists($personality_file)) {
    die("ERROR: Personality file not found at '{$personality_file}'.\n");
}

// -----------------------------------------------------------------------------
// 2. LOAD CONFIGURATIONS
// -----------------------------------------------------------------------------

// Load core functions (which also loads config.php)
require_once __DIR__ . '/functions.php';

// Load the specified AI personality
require_once $personality_file;

// Check if personality file defined the required variables
if (!isset($subject_name) || !isset($subject_social_accounts) || !isset($subject_base_prompts)) {
    die("ERROR: The personality file '{$personality_file}' is missing one or more required variables: \$subject_name, \$subject_social_accounts, \$subject_base_prompts.\n");
}

// -----------------------------------------------------------------------------
// 3. SCRIPT EXECUTION START
// -----------------------------------------------------------------------------

log_message("==========================================================");
log_message("Starting run for subject: '{$subject_name}'");
log_message("==========================================================");


// -----------------------------------------------------------------------------
// 4. SETUP & SYNC
// -----------------------------------------------------------------------------
log_message("STEP 1: Syncing accounts and checking environment...");

try {
    // Sync social accounts from the personality file to the database
    foreach ($subject_social_accounts as $account_details) {
        sync_social_account($subject_name, $account_details);
    }

    // Ensure there are categories to work with
    create_initial_categories($subject_name);

} catch (Exception $e) {
    log_message("A critical error occurred during setup: " . $e->getMessage(), "FATAL");
    die("A critical error occurred during setup. Check the logs.\n");
}
log_message("Setup complete.");


// -----------------------------------------------------------------------------
// 5. ANALYSIS & STRATEGY
// -----------------------------------------------------------------------------
log_message("STEP 2: Analyzing metrics and selecting a topic...");

try {
    // Update all metrics from social platforms (this also handles comment interaction)
    update_all_metrics($subject_name);

    // Recalculate category scores based on the new metrics
    recalculate_category_priority_scores();

    // Select the best category for the new video
    $category = get_most_engaging_category();

    if (!$category) {
        log_message("Could not select a category. There may be no active categories available. Exiting.", "ERROR");
        die();
    }
} catch (Exception $e) {
    log_message("A critical error occurred during analysis: " . $e->getMessage(), "FATAL");
    die("A critical error occurred during analysis. Check the logs.\n");
}
log_message("Analysis complete. Selected category: '{$category['name']}'.");


// -----------------------------------------------------------------------------
// 6. CONTENT GENERATION
// -----------------------------------------------------------------------------
log_message("STEP 3: Generating new video content...");

try {
    // Pick a random base prompt
    $base_prompt = $subject_base_prompts[array_rand($subject_base_prompts)];
    // Create the final, specific prompt
    $final_prompt = str_replace('[DYNAMIC_TOPIC]', $category['name'], $base_prompt);

    // Generate the video
    $video_path = generate_video_from_prompt($final_prompt);
    if (!$video_path || !file_exists($video_path)) {
        throw new Exception("Video generation failed to produce a file.");
    }

    // Generate title and description
    $video_title = generate_text_content("Create a catchy title for a video about {$category['name']}");
    $video_description = generate_text_content("Create an optimized description with hashtags for a video about {$category['name']}");

} catch (Exception $e) {
    log_message("A critical error occurred during content generation: " . $e->getMessage(), "FATAL");
    die("A critical error occurred during content generation. Check the logs.\n");
}
log_message("Content generation complete. Video at: {$video_path}");


// -----------------------------------------------------------------------------
// 7. PUBLICATION
// -----------------------------------------------------------------------------
log_message("STEP 4: Publishing video to all platforms...");

try {
    $accounts_to_publish = get_social_accounts_for_subject($subject_name);
    if (empty($accounts_to_publish)) {
        throw new Exception("No social accounts found in the database for subject '{$subject_name}'.");
    }

    foreach ($accounts_to_publish as $account) {
        log_message("Publishing to {$account['platform']} account: {$account['account_name']}");
        $post_url = publish_to_platform($account, $video_path, $video_title, $video_description);

        if ($post_url) {
            // Record each individual publication
            record_publication($category['id'], $account['id'], $final_prompt, $video_path, $post_url);
            log_message("Publication successful for {$account['platform']}. URL: {$post_url}");
        } else {
            log_message("Publication failed for {$account['platform']}.", "WARNING");
        }
    }
} catch (Exception $e) {
    log_message("A critical error occurred during publication: " . $e->getMessage(), "FATAL");
    die("A critical error occurred during publication. Check the logs.\n");
}
log_message("Publication process complete.");


// -----------------------------------------------------------------------------
// 8. FINALIZATION
// -----------------------------------------------------------------------------
log_message("STEP 5: Finalizing run...");

try {
    // Mark the category as used
    update_category_last_used($category['id']);

    // Optionally, delete the local video file if no longer needed
    // unlink($video_path);
    // log_message("Cleaned up local video file: {$video_path}");
} catch (Exception $e) {
    log_message("An error occurred during finalization: " . $e->getMessage(), "ERROR");
}

log_message("==========================================================");
log_message("Run for subject '{$subject_name}' finished successfully.");
log_message("==========================================================");

exit(0);

?>
