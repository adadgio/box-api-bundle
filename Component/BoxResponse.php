<?php

namespace Adadgio\BoxApiBundle\Component;

class BoxResponse
{
    /**
     * @var integer Response code
     */
    private $code;

    /**
     * @var string Response contents
     */
    private $content;

    /**
     * Service constructor.
     *
     * @param  array Bundle configuration nodes
     * @return void
     */
    public function __construct($content, $code)
    {
        $this->code = $code;
        $this->content = json_decode($content, true);
    }

    /**
     * Get response contents.
     *
     * @return array Decoded json response
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get response code.
     *
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }
    
    /**
     * Get response contents.
     *
     * @return array Decoded json response
     */
    public function getContentParameter($key)
    {
        return isset($this->content[$key]) ? $this->content[$key] : null;
    }

    /**
     * Get response error message.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return (isset($this->response['type']) && $this->response['type'] === 'error') ? $this->response['message'] : null;
    }
}
