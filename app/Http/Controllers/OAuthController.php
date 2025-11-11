<?php

namespace App\Http\Controllers;

use App\Services\Contracts\IGovBrAuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;

/**
 * Controlador responsável por gerenciar as rotas de autenticação OAuth 2.0 com Gov.br.
 * Utiliza um serviço de autenticação injetado para processar login, callback, logout e recuperação de usuário.
 */
class OAuthController extends Controller
{
    private IGovBrAuthService $authService;

    /**
     * Construtor que injeta o serviço de autenticação.
     *
     * @param IGovBrAuthService $authService Serviço de autenticação Gov.br (pure).
     */
    public function __construct(IGovBrAuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Redireciona para a página de informações do usuário.
     *
     * @return RedirectResponse
     */
    public function index(): RedirectResponse
    {
        return redirect('/user');
    }

    /**
     * Retorna as informações do usuário logado ou uma mensagem de erro se não logado.
     *
     * @param Request $requestcl
     * @return JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        $user = $this->authService->getUser($request);
        if ($user === null) {
            return response()->json([
                'error' => 'Usuário não logado',
                'code' => 401,
            ], 401);
        }
        return response()->json($user);
    }

    public function userPhoto(Request $request): Response
    {
        $photoBase64 = $this->authService->getUserPhoto($request);
        // exibe em tela formato web
        return response()->make(base64_decode($photoBase64), 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="user_photo.jpg"',
        ]);
    }

    /**
     * Inicia o processo de login, redirecionando para a URL de autorização do Gov.br.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function login(Request $request): RedirectResponse
    {
        $loginUrl = $this->authService->getLoginUrl($request);
        return redirect($loginUrl);
    }

    /**
     * Processa o callback do Gov.br após a autenticação.
     * Pode retornar um erro ou redirecionar para a página inicial.
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse|Redirector
     */
    public function callback(Request $request): JsonResponse|RedirectResponse|Redirector
    {
        $this->authService->handleCallback($request);
        return redirect('/user');
    }

    /**
     * Realiza o logout da sessão do usuário e redireciona para a URL de logout do Gov.br.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        $logoutUrl = $this->authService->logout($request);
        return redirect($logoutUrl);
    }

    /**
     * Realiza o logout do usuário e redireciona para a página inicial.
     *
     * @return RedirectResponse
     */
    public function logoutGovBrCallback(): RedirectResponse
    {
        return redirect('/');
    }
}
