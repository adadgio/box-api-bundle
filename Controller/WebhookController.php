<?php

namespace Adadgio\BoxApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Adadgio\BoxApiBundle\Event\NotificationEvent;

class WebhookController extends Controller
{
    /**
     * Return a json response to the box view API webhook notification request.
     *
     * @param  \Request
     * @return \JsonResponse
     */
    public function webhookAction(Request $request)
    {
        $event = (new NotificationEvent())
            ->setRequest($request)
            ->decode();
            
        // dispatch an event
        $this
            ->get('event_dispatcher')
            ->dispatch(NotificationEvent::BOX_VIEW_NOTIFICATION_EVENT, $event);

        return new JsonResponse(array(
            'success' => true,
            'details' => $event->getDecodedRequestContent(),
            'message' => sprintf('Dispatched %s', NotificationEvent::BOX_VIEW_NOTIFICATION_EVENT
        )), 200);
    }
}
