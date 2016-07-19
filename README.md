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
```

# Usage of the BoxView API

Upload a document and schedule it to be transformed by the box view api (see also [BoxView API upload options here](https://view.box.com/reference#post-documents)).

```php
$path = 'http://photos.jardindupicvert.com/catalogue.pdf';
// $path = '/Me/AndMyself/AndMyOwnServer/path/some-document.docx'; // this works to!

$response = $this->
    ->get('adadgio_box_api.box_view')
    ->setSvg(true) // false by default, will create svg images for compatibility
    ->setThumbnails(true) // false by default, will also create thumbs when transformed
    ->upload($path)
    ->getResponse();

print_r($response); // an easy to manipulate \BoxResponse object
```

A few seconds alter, the box service will post events into the webhook endpoint we have configured for you. You just need to hook up on that event with a simple listener and perform any other action you want.

```yml
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

        // box sends several types of notifications (you probably don't need all of them)
        // but hey, its really up to you from here...
        $doSomething = $this->handleNotificationTypes($rawRequestContent);
    }
    
    public function handleNotificationTypes(array $rawRequestContent)
    {
        switch ($content['type']) {
            case 'verification':
                return false; // verification sent when webhook is changed
            break;
            case 'document.??':
                return false; // i don't know the other ones by heart
            break;
            case 'document.done':
                return true; // this is probably what you've been eagerly waiting for!
            break;
            default:
                return false;
            break;
        }
    }
}
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
