<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;

interface IGovBrAuthService
{
    const AUTH_TYPE_PURE = 'pure';
    const AUTH_TYPE_LIB = 'lib';

    public function getLoginUrl(Request $request);

    public function handleCallback(Request $request);

    public function logout(Request $request);

    public function getUser(Request $request);
}
