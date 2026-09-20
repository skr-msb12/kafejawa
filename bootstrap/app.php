<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        $middleware->redirectGuestsTo('/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException|HttpException $e, Request $request) {
            if ($e instanceof HttpException && ($e->getStatusCode() !== 419 || !($e->getPrevious() instanceof TokenMismatchException))) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesi keamanan formulir telah kedaluwarsa. Silakan muat ulang halaman.'], 419);
            }

            return redirect()->back()->withInput($request->except(['password', '_token']))->with('error', 'Sesi keamanan formulir telah kedaluwarsa. Halaman telah disegarkan dengan sesi baru, silakan kirim ulang formulir.');
        });
    })->create();

