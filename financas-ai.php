<?php

/**
 * financas-ai.php
 *
 * AI Personality & Configuration for the "Finance" Subject.
 *
 * This file defines the specific accounts and content style for this niche.
 * The main `run.php` script loads this file to orchestrate its operations.
 */

// 1. Subject Identifier
// ---------------------
// A unique name for this content vertical. Used for database lookups.
$subject_name = 'finance';


// 2. Social Media Accounts
// ------------------------
// An array of all social media accounts associated with this subject.
// The `credentials` should be a JSON string.
//
// IMPORTANT: SECURITY WARNING
// Never commit real API keys or tokens directly into your code.
// In a production environment, load these from a secure source.
$subject_social_accounts = [
    [
        'platform'      => 'youtube',
        'account_name'  => 'Finance in Focus',
        'credentials'   => json_encode([
            'api_key'     => 'placeholder_youtube_api_key',
            'oauth_token' => 'placeholder_youtube_oauth_token'
        ])
    ],
    [
        'platform'      => 'tiktok',
        'account_name'  => '@focusedfinance',
        'credentials'   => json_encode([
            'access_token' => 'placeholder_tiktok_access_token'
        ])
    ],
    [
        'platform'      => 'instagram',
        'account_name'  => 'finance.focus',
        'credentials'   => json_encode([
            'username'    => 'finance.focus',
            'password'    => 'placeholder_instagram_password'
        ])
    ],
    [
        'platform'      => 'facebook',
        'account_name'  => 'Finance in Focus Page',
        'credentials'   => json_encode([
            'page_id'         => 'placeholder_facebook_page_id',
            'user_access_token' => 'placeholder_facebook_access_token'
        ])
    ],
];


// 3. Base Content Prompts
// -----------------------
// An array of base prompts used for generating video ideas in English.
// The placeholder [DYNAMIC_TOPIC] will be dynamically replaced by the
// name of the chosen category (e.g., "Quick Investment Tips").
$subject_base_prompts = [
    "Create a 30-second vertical 9:16 video about [DYNAMIC_TOPIC] with a quick, actionable tip.",
    "Generate a 45-second vertical 9:16 video explaining the concept of [DYNAMIC_TOPIC] for beginners, using visual analogies.",
    "Produce a 1-minute vertical 9:16 video analyzing the latest news on [DYNAMIC_TOPIC], with simple graphics and text highlights.",
    "Create a 'Myth vs. Fact' vertical 9:16 video about [DYNAMIC_TOPIC] to debunk a common misconception.",
    "Make a short vertical 9:16 video comparing [DYNAMIC_TOPIC] to another popular strategy, showing pros and cons."
];

?>
