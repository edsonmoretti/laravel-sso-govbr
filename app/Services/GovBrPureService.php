<?php

namespace App\Services;

use App\Models\GovBrUser;
use App\Services\Contracts\IGovBrAuthService;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Random\RandomException;
use RuntimeException;

/**
 * Implementação pura (sem bibliotecas externas) do serviço de autenticação Gov.br.
 * Utiliza OAuth 2.0 com OpenID Connect e PKCE para autenticação segura.
 * Ativada quando a propriedade govbr.auth.type é definida como "pure".
 */
class GovBrPureService implements IGovBrAuthService
{
    private string $urlProvider;
    private string $urlService;
    private string $redirectUri;
    private string $scopes;
    private string $clientId;
    private string $clientSecret;
    private string $logoutUri;

    public function __construct()
    {
        $this->urlProvider = config('sso.govbr.url_provider');
        $this->urlService = config('sso.govbr.url_service');
        $this->redirectUri = config('sso.govbr.redirect_uri');
        $this->scopes = config('sso.govbr.scopes');
        $this->clientId = config('sso.govbr.client_id');
        $this->clientSecret = config('sso.govbr.client_secret');
        $this->logoutUri = config('sso.govbr.logout_uri');
    }

    public function getLoginUrl(Request $request): string
    {
        $session = $request->session();

        // Gera parâmetros de segurança: state, nonce e PKCE
        $state = Str::uuid()->toString();
        $nonce = Str::uuid()->toString();
        $codeVerifier = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);

        // Armazena na sessão para validação posterior
        $session->put('oauth_state', $state);
        $session->put('oauth_nonce', $nonce);
        $session->put('code_verifier', $codeVerifier);

        // Constrói a URL de autorização com todos os parâmetros necessários
        $authorizeUrl = $this->urlProvider . '/authorize?' . http_build_query([
                'response_type' => 'code',
                'client_id' => $this->clientId,
                'scope' => $this->scopes,
                'redirect_uri' => $this->redirectUri,
                'nonce' => $nonce,
                'state' => $state,
                'code_challenge' => $codeChallenge,
                'code_challenge_method' => 'S256',
            ]);

        Log::info('Redirecting to authorize: ' . $authorizeUrl);
        return $authorizeUrl;
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function handleCallback(Request $request): JsonResponse|Redirector|RedirectResponse
    {
        $session = $request->session();

        $code = $request->query('code');
        $state = $request->query('state');
        $error = $request->query('error');
        $errorDescription = $request->query('error_description');

        // Verifica se houve erro no callback
        if ($error) {
            $errorResponse = [
                'error' => $error,
                'error_description' => $errorDescription,
                'state' => $state,
            ];
            Log::error('OAuth error: ', $errorResponse);
            return response()->json($errorResponse, 400);
        }

        // Valida o state para prevenir ataques CSRF
        $sessionState = $session->get('oauth_state');
        if ($state !== $sessionState) {
            throw new RuntimeException('Invalid state');
        }

        $codeVerifier = $session->get('code_verifier');

        // Troca o código de autorização por tokens de acesso
        $tokenUrl = $this->urlProvider . '/token';

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post($tokenUrl, [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'code_verifier' => $codeVerifier,
        ]);

        Log::info('Exchanging code for token: ', $response->json());

        if (!$response->successful()) {
            throw new Exception('Failed to get token: ' . $response->body());
        }

        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'];

        // Obtém informações do usuário usando o access_token
        $userInfoUrl = $this->urlProvider . '/userinfo';

        $userResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get($userInfoUrl);

        if (!$userResponse->successful()) {
            throw new Exception('Failed to get user info: ' . $userResponse->body());
        }

        // Converte a resposta JSON para GovBrUser e armazena na sessão
        $userInfo = new GovBrUser($userResponse->json());
        $session->put('user', json_encode($userInfo));
        return redirect('/');
    }

    public function logout(Request $request): string
    {
        $session = $request->session();

        // Invalida a sessão do usuário
        $session->flush();

        return $this->urlProvider . '/logout?' . http_build_query([
                'post_logout_redirect_uri' => $this->logoutUri,
            ]);
    }

    /**
     * Recupera os dados do usuário da sessão.
     */
    public function getUser(Request $request): ?GovBrUser
    {
        $session = $request->session();

        $userJson = $session->get('user');
        if (!$userJson) {
            return null;
        }

        try {
            $userData = json_decode($userJson, true);
            return new GovBrUser($userData);
        } catch (Exception $e) {
            Log::error('Error deserializing user from session', ['exception' => $e]);
            return null;
        }
    }

    /**
     * @throws RandomException
     */
    private function generateCodeVerifier(): string
    {
        return $this->base64UrlEncode(random_bytes(32));
    }

    private function generateCodeChallenge(string $verifier): string
    {
        return $this->base64UrlEncode(hash('sha256', $verifier, true));
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
