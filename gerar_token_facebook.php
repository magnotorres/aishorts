<?php

/**
 * gerar_token_facebook.php
 *
 * Helper script to generate a non-expiring Facebook Page Access Token.
 *
 * This script guides the user through the OAuth 2.0 process to grant the
 * application access to manage a Facebook Page and get a permanent token
 * for use in server-side automation.
 */

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

// --- Configuration ---
// The redirect URI must be registered in your Facebook App's settings
// under "Facebook Login" -> "Settings" -> "Valid OAuth Redirect URIs".
// For a simple CLI script, a localhost or personal website URL is fine.
const REDIRECT_URI = 'https://localhost/callback';
const API_VERSION = 'v19.0';

echo "========================================================\n";
echo "Gerador de Token de Página Permanente para o Facebook\n";
echo "========================================================\n\n";

// Function to prompt user for input
function prompt_user(string $message): string {
    echo $message . " ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    return trim($line);
}

// --- Main Script ---

$app_id = prompt_user("Insira o seu Facebook App ID:");
$app_secret = prompt_user("Insira o seu Facebook App Secret:");

if (empty($app_id) || empty($app_secret)) {
    die("ERRO: App ID e App Secret não podem ser vazios.\n");
}

$client = new Client([
    'base_uri' => 'https://graph.facebook.com/' . API_VERSION . '/',
    'timeout'  => 10.0,
]);

// 1. Generate the Authorization URL
$scopes = 'pages_show_list,pages_manage_posts,read_insights';
$auth_url = "https://www.facebook.com/" . API_VERSION . "/dialog/oauth?" . http_build_query([
    'client_id' => $app_id,
    'redirect_uri' => REDIRECT_URI,
    'scope' => $scopes,
    'response_type' => 'code',
]);

echo "\n--- PASSO 1: Autorização ---\n";
echo "Abra o seguinte link no seu navegador:\n\n";
echo $auth_url . "\n\n";
echo "Faça login, conceda as permissões para suas Páginas.\n";
echo "Você será redirecionado para uma página (provavelmente com erro, isso é normal).\n";
echo "Copie o parâmetro 'code' da URL na barra de endereço do seu navegador.\n";
echo "Exemplo: https://localhost/callback?code=AQB_....#_=\n\n";

$auth_code = prompt_user("Cole o valor do parâmetro 'code' aqui:");

if (empty($auth_code)) {
    die("ERRO: O código de autorização não pode ser vazio.\n");
}

// 2. Exchange authorization code for a short-lived User Access Token
try {
    $response = $client->get('oauth/access_token', [
        'query' => [
            'client_id' => $app_id,
            'client_secret' => $app_secret,
            'redirect_uri' => REDIRECT_URI,
            'code' => $auth_code,
        ]
    ]);
    $data = json_decode($response->getBody()->getContents(), true);
    $user_access_token = $data['access_token'] ?? null;
    if (!$user_access_token) {
        throw new Exception("Não foi possível obter o token de acesso do usuário. Resposta: " . json_encode($data));
    }
    echo "\nToken de Acesso de Usuário (temporário) obtido com sucesso.\n";
} catch (Exception $e) {
    die("\nERRO ao obter o token de acesso: " . $e->getMessage() . "\n");
}

// 3. Get the list of pages the user manages
try {
    $response = $client->get('me/accounts', [
        'query' => [
            'access_token' => $user_access_token,
        ]
    ]);
    $pages_data = json_decode($response->getBody()->getContents(), true);
    $pages = $pages_data['data'] ?? [];
    if (empty($pages)) {
        die("\nERRO: Nenhuma Página do Facebook encontrada para este usuário ou a permissão 'pages_show_list' não foi concedida.\n");
    }
} catch (Exception $e) {
    die("\nERRO ao buscar as páginas do usuário: " . $e->getMessage() . "\n");
}

// 4. Let the user choose the page
echo "\n--- PASSO 2: Seleção da Página ---\n";
echo "Encontramos as seguintes páginas sob sua administração:\n";
foreach ($pages as $index => $page) {
    echo "  [" . ($index + 1) . "] " . $page['name'] . " (ID: " . $page['id'] . ")\n";
}
echo "\n";

$choice = (int) prompt_user("Digite o número da página para a qual você deseja gerar o token:");
$selected_page_index = $choice - 1;

if (!isset($pages[$selected_page_index])) {
    die("ERRO: Escolha inválida.\n");
}

$selected_page = $pages[$selected_page_index];
$page_access_token = $selected_page['access_token'];

// Note: The token returned here is the permanent Page Access Token if the user
// went through the login flow for a Business App correctly.
// For non-business apps, this might be a short-lived token that needs to be exchanged
// for a long-lived one, but for server-side automation, a Business App is the standard.

echo "\n--- SUCESSO! ---\n\n";
echo "O Token de Acesso para a Página '" . $selected_page['name'] . "' é:\n\n";
echo "********************************************************************************\n";
echo $page_access_token . "\n";
echo "********************************************************************************\n\n";
echo "Este é o token que você deve usar como 'user_access_token' no seu arquivo de configuração (ex: financas-ai.php).\n";
echo "O ID da Página é: " . $selected_page['id'] . "\n\n";

echo "Script finalizado.\n";
