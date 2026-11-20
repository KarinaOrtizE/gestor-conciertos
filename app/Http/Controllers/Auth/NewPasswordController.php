<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::INVALID_TOKEN) {
            $this->registrarEnlaceRechazado($request);
        }

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }

    /** Deja en el log por qué se rechazó el enlace (sin escribir el código), para diagnosticar fallos en producción. */
    private function registrarEnlaceRechazado(Request $request): void
    {
        $registro = DB::table(config('auth.passwords.users.table'))->where('email', $request->email)->first();
        $vence = $registro ? Carbon::parse($registro->created_at)->addMinutes(config('auth.passwords.users.expire')) : null;

        Log::warning('Enlace de restablecimiento rechazado', [
            'motivo' => match (true) {
                ! $registro => 'no hay enlace pedido para ese correo',
                $vence->isPast() => 'vencido',
                default => 'el código no coincide con el último pedido',
            },
            'creado' => $registro?->created_at,
            'ahora' => now()->toDateTimeString(),
            'zona' => config('app.timezone'),
            'largo_codigo' => strlen((string) $request->token),
        ]);
    }
}
