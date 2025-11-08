<?php

namespace App\Http\Controllers;

use App\Services\Contracts\IGovBrAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
    public function index()
    {
        return redirect('/user');
    }

    /**
     * Retorna as informações do usuário logado ou uma mensagem de erro se não logado.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
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

    /**
     * Inicia o processo de login, redirecionando para a URL de autorização do Gov.br.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    public function login(Request $request)
    {
        $loginUrl = $this->authService->getLoginUrl($request);
        return redirect($loginUrl);
    }

    /**
     * Processa o callback do Gov.br após a autenticação.
     * Pode retornar um erro ou redirecionar para a página inicial.
     *
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function callback(Request $request)
    {
        return $this->authService->handleCallback($request);
    }

    /**
     * Realiza o logout da sessão do usuário e redireciona para a URL de logout do Gov.br.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request)
    {
        $logoutUrl = $this->authService->logout($request);
        return redirect($logoutUrl);
    }

    /**
     * Realiza o logout do usuário e redireciona para a página inicial.
     *
     * @return RedirectResponse
     */
    public function logoutGovBrCallback()
    {
        return redirect('/');
    }
}
