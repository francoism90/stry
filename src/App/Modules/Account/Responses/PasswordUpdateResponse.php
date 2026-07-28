<?php

declare(strict_types=1);

namespace App\Web\Account\Responses;

use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\PasswordUpdateResponse as PasswordUpdateResponseContract;
use Symfony\Component\HttpFoundation\Response;

class PasswordUpdateResponse implements PasswordUpdateResponseContract
{
    public function toResponse($request): JsonResponse|Response
    {
        if ($request->wantsJson()) {
            return response()->json();
        }

        Inertia::flash([
            'title' => __('Password updated'),
            'description' => __('Your password has been changed successfully.'),
            'type' => 'success',
        ]);

        return back();
    }
}
