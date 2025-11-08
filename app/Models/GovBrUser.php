<?php

namespace App\Models;

/**
 * Modelo de dados para representar um usuário autenticado via Gov.br.
 * Contém informações básicas do cidadão obtidas do endpoint /userinfo.
 */
class GovBrUser
{
    /**
     * CPF do usuário autenticado.
     */
    public string $sub;

    /**
     * Nome completo do usuário.
     */
    public string $name;

    /**
     * URL do perfil do usuário no Gov.br.
     */
    public ?string $profile;

    /**
     * URL da foto do usuário (protegida, requer access_token).
     */
    public ?string $picture;

    /**
     * Endereço de e-mail do usuário.
     */
    public ?string $email;

    /**
     * Indica se o e-mail foi verificado.
     */
    public bool $email_verified;

    /**
     * Número de telefone do usuário.
     */
    public ?string $phone_number;

    /**
     * Indica se o telefone foi verificado.
     */
    public bool $phone_number_verified;

    public function __construct(array $data = [])
    {
        $this->sub = $data['sub'] ?? $data['cpf'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->profile = $data['profile'] ?? null;
        $this->picture = $data['picture'] ?? $data['avatar_url'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->email_verified = $data['email_verified'] ?? false;
        $this->phone_number = $data['phone_number'] ?? null;
        $this->phone_number_verified = $data['phone_number_verified'] ?? false;
    }
}
