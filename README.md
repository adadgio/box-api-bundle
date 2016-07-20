# AdadgioBoxApiBundle

# Installation

Install with composer.

```bash
composer require adadgio/box-api-bundle
```

Add the bundle to your app kernel.

```php
new Adadgio\BoxApiBundle\AdadgioBoxApiBundle(),
```

## Configure the bundle

Add the following minimal configuration to your app.

```yml
# app/config/config.yml
adadgio_box_api:
    box_view:
        api_key: YoURA3eS0meBokzApIT0k3n
```

Update your configuration.

```yml
# app/config/routing.yml
_adadgio_box_api:
    resource: "@AdadgioBoxApiBundle/Resources/config/routing.yml"
```

Open a route in your firewall.

```yml
# app/config/security.yml
security:
    #...
    access_control:
        - { path: ^/adadgio/boxview/webhook/notification, role: IS_AUTHENTICATED_ANONYMOUSLY }

```

Run the command to update yout webhook endpoint in your box account.

```bash
php app/console adadgio:box:update
php bin/console adadgio:box:update // Sf3
```

# Usage of the BoxView API

Upload a document and schedule it to be transformed by the box view api (see also [BoxView API upload options here](https://view.box.com/reference#post-documents)).

```php
$path = 'http://photos.jardindupicvert.com/catalogue.pdf';
// $path = '/Me/AndMyself/AndMyOwnServer/path/some-document.docx'; // this works to!

$response = $this->
    ->get('adadgio_box_api.box_view')
    ->setSvg(true) // false by default, will create svg images for compatibility
    ->setThumbnails(true) // false by default, will also create thumbs (128x128, 256x256 and 480x480)
    ->upload($path)
    ->getResponse();

print_r($response); // an easy to manipulate \BoxResponse object
```

A few seconds alter, the box service will post events into the webhook endpoint we have configured for you. You just need to hook up on that event with a simple listener and perform any other action you want.

```yml
# in your app services.yml
services:
    app.box_view.notification_listener:
        public: false:
        class: AppBundle\Listener\BoxViewNotification
        tags:
            - {name: kernel.event_listener, event: adadgio.box_view.notification, method: onNotificationReceived }
```

```php
namespace AppBundle\Listener

use Adadgio\BoxApiBundle\Event\NotificationEvent;

class BoxViewNotification
{
    // ... other methods that probably make coffee or something...

    public function onNotificationReceived(NotificationEvent $event)
    {
        $request = $event->getRequest();
        $rawRequestContent = $request->getContent();
        $decodedRequestContent = $event->getDecodedRequestContent(); // this is much cleaner (you'll avoid errors)

        //debug($decodedRequestContent, true);
        // box sends several types of notifications (you probably don't need all of them)
        // but hey, its really up to you from here...

        // statuses
        //  - "verification"
        //  - "document.viewable"
        //  - "document.done"
    }
}
```

When you get an event you can read the event request details and retrieve document meta data or contents.

```php
namespace AppBundle\Listener

use Adadgio\BoxApiBundle\Component\BoxView;
use Adadgio\BoxApiBundle\Event\NotificationEvent;

class BoxViewNotification
{
    // ... other methods that probably make coffee or something...

    public function onNotificationReceived(NotificationEvent $event)
    {
        $request = $event->getRequest();
        $rawRequestContent = $request->getContent();
        $decodedRequestContent = $event->getDecodedRequestContent(); // this is much cleaner (you'll avoid errors)

        //debug($decodedRequestContent, true);
        // box sends several types of notifications (you probably don't need all of them)
        // but hey, its really up to you from here...

        // statuses
        //  - "verification"
        //  - "document.viewable"
        //  - "document.done"
        if ($decodedRequestContent['type'] === BoxView::DOCUMENT_VIEWABLE) {
            // nothing to do, although could retrieve meta data at this point
        } else if ($decodedRequestContent['type'] === BoxView::DOCUMENT_DONE) {
            // download document contents or meta data !
        } else {
            // other statuses
            // to document...
        }
    }
}
```

## Download document contents or meta data

You can download document original file or converted assets once the document is "done", or meta data once the document is viewable.

```php
// download contents
$dir = __DIR__.'/docs'; // must be writable

$response = $boxView
    ->in($dir)
    ->download('17dj760364d445b5b960b6e8a2e1c3ec')
    ->getResponse();

// success or error is given by response code (200 ok, 404 not found, 202 document not yes ready)
// print_r($response->getCode());
```

```php
// download meta data
$response = $boxView
    ->metadata('17dj760364d445b5b960b6e8a2e1c3ec')
    ->getResponse();

// success or error is given by response code (200 ok, 404 not found, 202 document not yes ready)
// print_r($response->getCode());
```

## Set up an alternative webhook endpoint

The **BoxView API** sends webhooks to your app when documents are done transforming. To customize that, or override the default endpoint for local testing for instance, update your config

```yml
# app/config/config.yml
adadgio_box_api:
    box_view:
        api_key: YoURA3eS0meBokzApIT0k3n
        # advice, keep the "/adadgio/boxview..." part, you'll use an event listener to hook up on that route
        webhook: http://myappdev.ngrok.io/adadgio/boxview/webhook/notification
```
