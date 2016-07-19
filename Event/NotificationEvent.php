<?php

namespace Adadgio\BoxApiBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class NotificationEvent extends Event
{
    /**
     * @var object \RequestInterface
     */
    private $request;

    /**
     * @var array Decoded request contents.
     */
    private $decodedRequestContent;

    /**
     * The event name were supposed to hook with.
     */
    const BOX_VIEW_NOTIFICATION_EVENT = 'adadgio.box_view.notification_event';
    
    /**
     * Get original request.
     *
     * @return object \RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get decoded box request content.
     *
     * @return object \RequestInterface
     */
    public function getDecodedRequestContent()
    {
        return $this->decodedRequestContent;
    }

    /**
     * Set original request.
     *
     * @param  object \RequestInterface
     * @return object \NotificationEvent
     */
    public function setRequest(\Symfony\Component\HttpFoundation\Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Decodes Box View API  notification request that are a bit special.
     *
     * @param
     * @return array
     */
    public function decode()
    {
        $this->decodedRequestContent = json_decode($this->request->getContent(), true)[0];

        return $this;
    }
}
