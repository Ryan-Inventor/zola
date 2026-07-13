<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler
{
  /**
   * Rend une réponse JSON standardisée pour toutes les routes /api/*.
   * Format : {"error":"CODE","message":"...","details":{}}
   */
  public static function render(Request $request, Throwable $e): ?JsonResponse
  {
    if (! $request->is('api/*')) {
      return null;
    }

    if ($e instanceof ValidationException) {
      return response()->json([
        'error' => 'VALIDATION_ERROR',
        'message' => $e->getMessage() ?: 'Les données fournies sont invalides.',
        'details' => $e->errors(),
      ], $e->status);
    }

    if ($e instanceof AuthenticationException) {
      return response()->json([
        'error' => 'UNAUTHORIZED',
        'message' => 'Non authentifié.',
        'details' => new \stdClass(),
      ], 401);
    }

    if ($e instanceof AuthorizationException) {
      return response()->json([
        'error' => 'FORBIDDEN',
        'message' => $e->getMessage() ?: 'Accès refusé.',
        'details' => new \stdClass(),
      ], 403);
    }

    if ($e instanceof NotFoundHttpException) {
      return response()->json([
        'error' => 'NOT_FOUND',
        'message' => 'Ressource introuvable.',
        'details' => new \stdClass(),
      ], 404);
    }

    if ($e instanceof HttpExceptionInterface) {
      return response()->json([
        'error' => self::httpStatusToCode($e->getStatusCode()),
        'message' => $e->getMessage() ?: 'Une erreur est survenue.',
        'details' => new \stdClass(),
      ], $e->getStatusCode());
    }

    $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

    return response()->json([
      'error' => 'SERVER_ERROR',
      'message' => config('app.debug') ? $e->getMessage() : 'Une erreur interne est survenue.',
      'details' => new \stdClass(),
    ], $status >= 100 && $status < 600 ? $status : 500);
  }

  private static function httpStatusToCode(int $status): string
  {
    return match ($status) {
      401 => 'UNAUTHORIZED',
      403 => 'FORBIDDEN',
      404 => 'NOT_FOUND',
      422 => 'VALIDATION_ERROR',
      default => 'HTTP_ERROR',
    };
  }
}
