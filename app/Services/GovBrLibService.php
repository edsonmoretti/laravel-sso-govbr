<?php

namespace App\Services;

use App\Models\GovBrUser;
use App\Services\Contracts\IGovBrAuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

/**
 * Implementação pura (sem bibliotecas externas) do serviço de autenticação Gov.br.
 * Utiliza OAuth 2.0 com OpenID Connect e PKCE para autenticação segura.
 * Ativada quando a propriedade govbr.auth.type é definida como "pure".
 */
class GovBrLibService extends GovBrAbstractService implements IGovBrAuthService
{
    public function getLoginUrl(Request $request): string
    {
        return Socialite::driver('govbr')->redirect()->getTargetUrl();
    }

    /**
     * @throws Exception
     */
    public function handleCallback(Request $request): JsonResponse|Redirector|RedirectResponse
    {
        try {
            $session = $request->session();
            $userInfo = Socialite::driver('govbr')->user();
            $userInfo = new GovBrUser($userInfo->attributes);
            $session->put('user', json_encode($userInfo));
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
        return redirect('/');
    }

    public function logout(Request $request): string
    {
        $request->session()->invalidate();
        $request->session()->flush();
        $request->session()->regenerate();
        return $this->urlProvider . '/logout?' . http_build_query([
                'post_logout_redirect_uri' => $this->logoutUri,
            ]);
    }

}
