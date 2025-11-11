<?php

namespace App\Services;

use App\Models\GovBrUser;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Implementação pura (sem bibliotecas externas) do serviço de autenticação Gov.br.
 * Utiliza OAuth 2.0 com OpenID Connect e PKCE para autenticação segura.
 * Ativada quando a propriedade govbr.auth.type é definida como "pure".
 */
abstract class GovBrAbstractService
{
    protected string $urlProvider;
    protected string $urlService;
    protected string $redirectUri;
    protected string $scopes;
    protected string $clientId;
    protected string $clientSecret;
    protected string $logoutUri;

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

    public function getUserPhoto(Request $request): string
    {
        $session = $request->session();
        $token = $session->get('token');
        if (!$token) {
            throw new RuntimeException('Token not found');
        }
        $response = Http::withToken($token)
            ->get($this->urlProvider . '/userinfo/picture');
        $rawBody = $response->body();
        return base64_encode($rawBody);
    }
}
