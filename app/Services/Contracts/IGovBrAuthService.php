<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;

interface IGovBrAuthService
{
    public function getLoginUrl(Request $request);

    public function handleCallback(Request $request);

    public function logout(Request $request);

    public function getUser(Request $request);
}
