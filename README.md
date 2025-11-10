# Laravel SSO Gov.br

## Resumo do SSO Gov.br

O SSO (Single Sign-On) Gov.br é um sistema de autenticação único desenvolvido pelo governo brasileiro. Ele permite que cidadãos acessem diversos serviços governamentais usando uma única conta, baseada no CPF e senha do Gov.br. Utiliza os protocolos OAuth 2.0 e OpenID Connect para garantir segurança e interoperabilidade.

## Sobre o Projeto

Este projeto é uma implementação em Laravel para integrar aplicações com o SSO Gov.br. Ele oferece duas formas de implementação:

- **GovBrPureService**: Implementação pura usando apenas as bibliotecas nativas do Laravel (Http, etc.), sem dependências externas para o Gov.br.
- **GovBrLibService**: Implementação usando a biblioteca `socialiteproviders/govbr` com Laravel Socialite.

O projeto usa PKCE (Proof Key for Code Exchange) para maior segurança no fluxo OAuth.

## Instalação

1. Clone o repositório:
   ```
   git clone <url-do-repositorio>
   cd laravel-sso-govbr
   ```

2. Instale as dependências:
   ```
   composer install
   npm install
   ```

3. Configure o ambiente:
   - Copie `.env.example` para `.env`
   - Gere a chave da aplicação: `php artisan key:generate`

4. Configure o banco de dados e execute as migrações:
   ```
   php artisan migrate
   ```

## Configuração

Edite o arquivo `config/sso.php` com suas credenciais do Gov.br:

```php
'govbr' => [
    'url_provider' => 'https://sso.staging.acesso.gov.br',
    'url_service' => 'https://api.staging.acesso.gov.br',
    'client_id' => 'your-client-id',
    'client_secret' => 'your-client-secret',
    'redirect_uri' => 'http://localhost:8000/callback',
    'logout_uri' => 'http://localhost:8000/',
    'scopes' => 'openid email profile phone',
],
```

Para alternar entre implementações, edite `app/Providers/AppServiceProvider.php`:

- Para pura: `$this->app->bind(IGovBrAuthService::class, GovBrPureService::class);`
- Para com biblioteca: `$this->app->bind(IGovBrAuthService::class, GovBrLibService::class);`

## Uso

### Endpoints

- `GET /login`: Inicia o login, redireciona para Gov.br
- `GET /callback`: Processa o callback após autenticação
- `GET /user`: Retorna dados do usuário logado (JSON)
- `GET /logout`: Faz logout e redireciona para Gov.br logout

### Exemplo de Uso no Código

Injete o serviço `IGovBrAuthService` no seu controlador:

```php
use App\Services\Contracts\IGovBrAuthService;

public function someMethod(IGovBrAuthService $authService, Request $request) {
    $user = $authService->getUser($request);
    // ...
}
```

## Desenvolvimento

Para rodar em modo desenvolvimento:
```
composer run dev
```

Para testes:
```
composer run test
```

## Licença

MIT
