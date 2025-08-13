# AI Video Content Automation Bot

This project is a complete PHP application designed to automate the creation and publication of short-form vertical videos (9:16 format) for platforms like YouTube Shorts, TikTok, and Instagram Reels. It uses an AI-driven strategy to analyze engagement metrics and optimize future content. The application consists of a powerful CLI bot and a web-based admin panel.

## Features

- **Automated Content Pipeline:** From topic selection to video generation and publishing, the entire workflow is automated.
- **AI-Powered Strategy:** Analyzes video performance metrics (views, likes, comments) to calculate a `priority_score` for different content categories, ensuring the bot focuses on what the audience engages with most.
- **Multi-Platform Publishing:** Built to support YouTube, Facebook, Instagram, TikTok, and Kwai.
- **CLI Control:** The core engine is run via a command-line interface, making it easy to schedule with cron jobs.
- **"AI Personality" System:** Easily configure new content niches by creating simple `*-ai.php` files that define the subject, social accounts, and content style.
- **Web Admin Panel:** A secure, password-protected dashboard to:
    - View high-level performance metrics.
    - See a list of all published videos with links and stats.
    - Manage content categories: create, edit, delete, and manually adjust their priority scores.

## Requirements

- PHP 8.1+ (with `pdo_mysql` and `json` extensions)
- MySQL 8+
- Composer

## Installation and Setup

1.  **Clone the Repository**
    ```bash
    git clone <repository_url>
    cd ai-video-automation
    ```

2.  **Install Dependencies**
    Use Composer to install the required PHP packages.
    ```bash
    composer install
    ```

3.  **Set Up Environment Variables**
    Copy the example `.env` file and fill in your credentials.
    ```bash
    cp .env.example .env
    ```
    Now, edit the `.env` file with your database credentials, Google AI API key, and desired admin password.

4.  **Set Up the Database**
    Connect to your MySQL server and create a database. Then, import the schema from the `schema.sql` file.
    ```bash
    # Example using mysql command line
    mysql -u your_user -p -e "CREATE DATABASE video_automation_db;"
    mysql -u your_user -p video_automation_db < schema.sql
    ```

5.  **Configure AI Personalities**
    Review the `finance-ai.php` file. You can duplicate and modify it to create new personalities for different content niches (e.g., `cooking-ai.php`). Update the `$subject_social_accounts` array with your actual social media credentials (or a secure way to load them).

## Usage

### Running the CLI Bot

The main script is `run.php`. To execute a run for a specific personality, pass its configuration file as an argument.

```bash
# Run the bot for the finance personality
php run.php finance-ai.php

# Run the bot for a custom cooking personality
php run.php cooking-ai.php
```

You can set up a cron job to run this command automatically on a schedule (e.g., once a day).

### Using the Admin Panel

The project includes a simple `start` script in `composer.json` to launch a local PHP web server for the admin panel.

1.  **Start the Server:**
    ```bash
    composer start
    ```
    This will start a server, typically at `http://localhost:8080`.

2.  **Login:**
    - Navigate to the server address in your browser.
    - The default credentials are set in your `.env` file.
    - **Default:** `admin` / `admin_password`

## Project Structure

- `/project_root`
    - `admin/`: Contains all files for the web-based admin panel.
    - `vendor/`: Composer dependencies.
    - `generated_videos/`: Default directory where generated videos are saved.
    - `logs/`: Application logs are stored here.
    - `config.php`: Global configuration (loads from `.env`).
    - `functions.php`: Core library of all functions for the application.
    - `run.php`: The main CLI orchestrator script.
    - `*.ai.php`: Personality files (e.g., `finance-ai.php`).
    - `schema.sql`: The MySQL database schema.
    - `composer.json`: PHP project dependencies and scripts.
    - `.env.example`: Example environment file.
    - `README.md`: This file.
