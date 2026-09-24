<?php

declare(strict_types=1);

namespace App\Web\Account\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\PasswordUpdateResponse as PasswordUpdateResponseContract;
use Symfony\Component\HttpFoundation\Response;

class PasswordUpdateResponse implements PasswordUpdateResponseContract
{
    public function toResponse($request): JsonResponse|Response
    {
        if ($request->wantsJson()) {
            return response()->json();
        }

        toast(title: __('Password updated'), description: __('Your password has been changed successfully.'));

        return back();
    }
}
