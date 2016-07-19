<?php

namespace Adadgio\BoxApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        $content = json_decode($request->getContent());

        switch ($content['type']) {
            case 'verification':
                
            break;
            case '':

            break;
            default:

            break;
        }
        // if ($content['type'] === 'verification') {
        //     // skip
        // }

        return new JsonResponse(array('success' => true, 'message' => 'ackowledged'), 200);
    }
}
