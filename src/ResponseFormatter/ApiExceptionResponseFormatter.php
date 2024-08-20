<?php

declare(strict_types=1);

namespace App\ResponseFormatter;

use App\Controller\ApiControllerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(KernelEvents::EXCEPTION, method: 'onKernelException')]
#[AsEventListener(KernelEvents::CONTROLLER, method: 'onKernelController')]
class ApiExceptionResponseFormatter
{
    private bool $apiRequest = false;

    public function __construct(
        private readonly string $env
    ) {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        $controller = is_array($controller) ? $controller[0] : $controller;
        if (
            HttpKernelInterface::MAIN_REQUEST === $event->getRequestType()
            && $controller instanceof ApiControllerInterface
        ) {
            $this->apiRequest = true;
        }
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if ($this->apiRequest) {
            $exception = $event->getThrowable();

            $error = 'Internal server error';
            if ('dev' === $this->env) {
                $error = [
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTrace(),
                ];
            }

            $response = new JsonResponse([
                'success' => false,
                'error' => $error,
            ]);

            if ($exception instanceof HttpExceptionInterface) {
                $response->setStatusCode($exception->getStatusCode());
                $response->headers->replace($exception->getHeaders());
            } else {
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $event->setResponse($response);
        }
    }
}
