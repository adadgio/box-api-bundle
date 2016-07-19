<?php

namespace Adadgio\BoxApiBundle\Component;

use Adadgio\BoxApiBundle\Component\BoxResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BoxView
{
    /**
     * @var array Bundle configuration.
     */
    private $config;

    /**
     * @var array Runtime options
     */
    private $options;

    /**
     * @var array Last api response.
     */
    private $response;

    /**
     * @var string Base BoxView standard API url
     */
    const VIEW_API_URL = 'https://view-api.box.com/1';

    /**
     * @var string Base BoxView upload API url
     */
    const UPLOAD_API_URL = 'https://upload.view-api.box.com/1';

    /**
     * Constants for file upload modes (multipart or by remote url)
     */
    const URL_UPLOAD = 'URL_UPLOAD';
    const MULTIPART_UPLOAD = 'MULTIPART_UPLOAD';

    /**
     * Service constructor.
     *
     * @param  array Bundle configuration nodes
     * @return void
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->options = array(
            'svg'        => false,
            'thumbnails' => false,
        );
    }

    /**
     * Get last api call response.
     *
     * @return
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set svg option for uploads.
     *
     * @param  boolean
     * @return object  \BoxView
     */
    public function setSvg($boolean)
    {
        $this->options['svg'] = (boolean) $boolean;

        return $this;
    }

    /**
     * Set thumbnails creation option for uploads.
     *
     * @param  boolean
     * @return object  \BoxView
     */
    public function setThumbnails($boolean)
    {
        $this->options['thumbnails'] = (true === $boolean) ? '128x128,256x256,480x480' : false;

        return $this;
    }

    /**
     * Uploads a file to box via remote url.
     *
     * @param string Remote file http url or server local path
     * @param array  Upload options (scg, thumbnails...)
     * @return array Box api response
     */
    public function upload($filepath)
    {
        $this->response = $this->request(array(
            CURLOPT_URL           => $this->getEndpoint(array('documents')),
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS    => $this->configureUploadPostFields($filepath),
            CURLOPT_HTTPHEADER    => array((self::isUrl($filepath)) ? 'Content-Type: application/json' : 'Content-Type: multipart/form-data'),
        ));

        return $this;
    }

    /**
     * Sets up a webhook url to receive notifications from the BoxView API service.
     * Only one webhook can be set per application. Box will test that url upon request.
     *
     * @param  string Webhook callback url
     * @return object \BoxView
     */
    public function setWebhook($url)
    {
        $this->response = $this->request(array(
            CURLOPT_URL           => $this->getEndpoint(array('settings', 'webhook')),
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS    => array('url' => rtrim($url, '/')),
            CURLOPT_HTTPHEADER    => array('Content-Type: application/json'),
        ));

        return $this;
    }

    /**
     * Retrieve the current webhook url that was set for this application.
     *
     * @return object \BoxView
     */
    public function getWebhook()
    {
        $this->response = $this->request(array(
            CURLOPT_URL        => $this->getEndpoint(array('settings', 'webhook')),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
        ));

        return $this;
    }

    /**
     * Delete the current webhook callback url that was set for this application.
     *
     * @return object \BoxView
     */
    public function deleteWebhook()
    {
        $this->response = $this->request(array(
            CURLOPT_URL           => $this->getEndpoint(array('settings', 'webhook')),
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER    => array('Content-Type: application/json'),
        ));

        return $this;
    }

    /**
     * Perform a curl request to the API and turns it into a response.
     *
     * @param  array  Curl parameters
     * @return object \BoxResponse
     */
    protected function request(array $options = array())
    {
        $resolver = new OptionsResolver();
        $this->configureRequest($resolver);
        $options = $resolver->resolve($options);

        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $body = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        $ctype = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        curl_close($curl);

        return new BoxResponse($body, $code);
    }

    /**
     * Create a box api endpoint from uri segments.
     *
     * @param  array  Uri segments to join
     * @return string Url endpoint
     */
    protected function getEndpoint(array $uris)
    {
        return rtrim(static::VIEW_API_URL, '/').'/'.implode('/', $uris);
    }

    /**
     * Default curl configuration for curl requests.
     *
     * @param object \Symfony\Component\OptionsResolver\OptionsResolver
     */
    protected function configureRequest(OptionsResolver $resolver)
    {
        $resolver->setRequired(array(
            CURLOPT_URL
        ));

        $resolver->setDefined(array(
            CURLOPT_POSTFIELDS, CURLOPT_FILE, CURLOPT_NOPROGRESS, CURLOPT_PROGRESSFUNCTION
        ));

        $resolver->setDefaults(array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTPHEADER     => array(sprintf('Authorization: Token %s', $this->config['api_key'])),
            CURLOPT_CUSTOMREQUEST  => 'GET',
        ));

        // important allowed types
        $resolver->setAllowedTypes(CURLOPT_URL, array('string'));
        $resolver->setAllowedTypes(CURLOPT_HTTPHEADER, array('array'));
        $resolver->setAllowedTypes(CURLOPT_POSTFIELDS, array('array'));

        // normalize http header(s)
        $resolver->setNormalizer(CURLOPT_HTTPHEADER, function ($options, $value) {
            return array_unique(array_merge(
                $value, array(sprintf('Authorization: Token %s', $this->config['api_key']))
            ));
        });

        // encode the post fields to json "Content-Type: application/json" or custom, usualy "Content-Type: multipart/form-data"
        $resolver->setNormalizer(CURLOPT_POSTFIELDS, function ($options, $value) {
            if (in_array('Content-Type: application/json', $options[CURLOPT_HTTPHEADER])) {
                return json_encode($value);
            } else {
                return $value;
            }
        });
    }

    /**
     * Configure post fields options for file uploads.
     *
     * @param  string File remote url path or server location path
     * @param  array  Upload method (cf. class url/multipart constants)
     * @return array  Upload options (svg, thumbnails, etc)
     */
    private function configureUploadPostFields($filepath)
    {
        $postFields = array(
            'name' => self::createFilename($filepath),
        );

        // detect if its a remote url file upload or multipart file upload
        if (self::isUrl($filepath)) {
            $postFields['url'] = $filepath;
        } else {
            $postFields['file'] = new \CurlFile($filepath);
            if (!is_file()) {
                throw new \Exception(sprintf('File "%s" not found', $filepath));
            }
        }

        // optional thumbnails creation upon upload
        if ($this->options['thumbnails']) {
            $postFields['thumbnails'] = $this->options['thumbnails'];
        }

        // optional svg creation upon upload
        if ($this->options['svg']) {
            $postFields['svg'] = $this->options['svg'];
        }

        return $postFields;
    }

    /**
     * Creates a basic filename from url or path input.
     *
     * @param  string Url or server path
     * @return string Filename
     */
    private static function createFilename($path)
    {
        return basename($path);
    }

    /**
     * Checks if an url is a remote url.
     *
     * @param  string File path
     * @return boolean
     */
    private static function isUrl($path)
    {
        return filter_var($path, FILTER_VALIDATE_URL);
    }
}
