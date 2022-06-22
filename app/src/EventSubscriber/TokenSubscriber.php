<?php

namespace App\EventSubscriber;

use App\Helpers\Request;
use App\Services\TokenService;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Controllers\TokenAuthenticatedController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;

class TokenSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private TokenService $ts,
        private UserRepository $ur
    ) {
    }

    public function onKernelController(
        ControllerEvent $event,
        $str,
        TraceableEventDispatcher $dispatcher
    ) {
        $controller = $event->getController();

        // when a controller class defines multiple action methods, the controller
        // is returned as [$controllerInstance, 'methodName']
        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if ($controller instanceof TokenAuthenticatedController) {

            if (
                !$event->getRequest()->headers->has('Authorization')
                && 0 !== strpos($event->getRequest()->headers->get('Authorization'), 'Bearer ')
            ) {
                return self::handleError($event, 'Forbidden', 403);
            }

            $token = substr($event->getRequest()->headers->get('Authorization'), 7);
            if (!$this->ts->validate($token)) {
                return self::handleError($event, 'Forbidden', 403);
            }

            $request = new Request($event->getRequest());
            $user = $request->getUser($this->ur, $this->ts);

            if (!isset($user)) {
                return self::handleError($event, 'Forbidden', 403);
            }
        }
    }

    private static function handleError(ControllerEvent $event, $message, $status = 400)
    {
        return $event->setController(function () {
            return new JsonResponse([
                'message' => 'Forbidden',
                'status' => 403
            ], 403);
        });
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}