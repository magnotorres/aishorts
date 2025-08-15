<?php

/**
 * gerar_token_youtube.php
 *
 * Helper script to generate a YouTube API Refresh Token using OAuth 2.0.
 *
 * This script guides the user through the authorization process to grant
 * the application access to their YouTube account for uploading videos.
 * The output is a Refresh Token that can be used in the main application's
 * configuration file (`financas-ai.php` or similar).
 */

// Include Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

echo "=====================================================\n";
echo "Gerador de Refresh Token para a API do YouTube\n";
echo "=====================================================\n\n";

// Function to prompt user for input
function prompt_user(string $message): string {
    echo $message . " ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    return trim($line);
}

// 1. Get the path to the client secret file
$client_secret_path = prompt_user("Por favor, insira o caminho para o seu arquivo client_secret.json:");

if (!file_exists($client_secret_path)) {
    die("\nERRO: Arquivo não encontrado em '{$client_secret_path}'. Por favor, verifique o caminho e tente novamente.\n");
}

$client = new Google_Client();

try {
    // Load client secrets from the JSON file
    $client->setAuthConfig($client_secret_path);
} catch (Google\Exception $e) {
    die("\nERRO: Formato inválido do arquivo JSON de credenciais. Mensagem: " . $e->getMessage() . "\n");
}

// 2. Configure the Google Client
// The 'offline' access type is crucial for getting a refresh token
$client->setAccessType('offline');
// The 'force' approval prompt ensures you get a refresh token every time
$client->setApprovalPrompt('force');
// Set the scopes required for the YouTube Data API, specifically for uploads
$client->setScopes([
    'https://www.googleapis.com/auth/youtube.upload',
    'https://www.googleapis.com/auth/youtube.readonly' // Good to have for checking status later
]);
// This special redirect URI is for 'out-of-band' (copy/paste) authorization flows
$client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');

// 3. Generate and display the authorization URL
$auth_url = $client->createAuthUrl();

echo "\n--- PASSO 1: Autorização ---\n";
echo "Abra o seguinte link no seu navegador:\n\n";
echo $auth_url . "\n\n";
echo "Faça login com a sua conta Google (a dona do canal do YouTube),\n";
echo "conceda as permissões solicitadas e copie o código de autorização que será exibido.\n\n";

// 4. Get the authorization code from the user
$auth_code = prompt_user("Cole o código de autorização aqui e pressione Enter:");

if (empty($auth_code)) {
    die("\nERRO: Código de autorização não pode ser vazio.\n");
}

// 5. Exchange authorization code for an access token and refresh token
try {
    $access_token_data = $client->fetchAccessTokenWithAuthCode($auth_code);
    $client->setAccessToken($access_token_data);
} catch (Exception $e) {
    die("\nERRO: Falha ao trocar o código de autorização pelo token de acesso. Mensagem: " . $e->getMessage() . "\n");
}

// 6. Extract and display the Refresh Token
$refresh_token = $client->getRefreshToken();

if ($refresh_token) {
    echo "\n--- SUCESSO! ---\n\n";
    echo "Seu Refresh Token é:\n\n";
    echo "********************************************************************************\n";
    echo $refresh_token . "\n";
    echo "********************************************************************************\n\n";
    echo "Copie este token e cole-o no valor de 'oauth_token' no seu arquivo de configuração (ex: financas-ai.php).\n";
    echo "Guarde este token de forma segura. Ele não será exibido novamente.\n\n";
} else {
    echo "\n--- ATENÇÃO ---\n\n";
    echo "Não foi possível obter um novo Refresh Token.\n";
    echo "Isso geralmente acontece se você já autorizou este aplicativo antes.\n";
    echo "Para forçar a geração de um novo token, acesse o link abaixo, remova o acesso do seu aplicativo e execute este script novamente:\n";
    echo "https://myaccount.google.com/permissions\n\n";

    // Even if we didn't get a new refresh token, we got an access token
    if (isset($access_token_data['access_token'])) {
        echo "Token de acesso (temporário) obtido: " . $access_token_data['access_token'] . "\n";
    }
}

echo "Script finalizado.\n";
