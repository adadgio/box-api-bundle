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

## Setting up alternative webhooks endpoint

The **BoxView API** sends webhooks to your app when documents are done transforming. To customize that, or override the default endpoint for local testing for instance, update your config

```yml
# app/config/config.yml
adadgio_box_api:
    box_view:
        api_key: YoURA3eS0meBokzApIT0k3n
        webhook: http://myappdev.ngrok.io/adadgio/boxview/webhook/notification # advice, keep the "/adadgio/boxview..." part, you'll use an event listener to hook up on that route
```

# Using the BoxView API

## Upload a document
