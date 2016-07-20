<?php

namespace Adadgio\BoxApiBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class WebhookUpdateCommand extends ContainerAwareCommand
{
    /**
     * @var object Symfony router
     */
    private $router;

    /**
     * @var object BoxView API service.
     */
    private $boxView;

    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('adadgio:box:update')
            ->setDescription('Sets up BoxView API webhook endpoint');
    }

    /**
     * Execute the command.
     *
     * @param object \InputInterface
     * @param object \OutputInterface
     * @return @void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $this->router = $container->get('router');
        $this->boxView = $container->get('adadgio_box_api.box_view');

        // resolve the webhook new url (from config or default)
        $webhookUrl = $this->resolveWebhookUrl($container);

        // retrieve previous webhook url
        $response = $this->boxView
            ->getWebhook()
            ->getResponse();

        $output->writeln(sprintf('Previous webhook: <info>%s</info>', $response->getContentParameter('url')));

        // set new webhook url
        $response = $this->boxView
            ->setWebhook($webhookUrl)
            ->getResponse();

        if ($response->getContentParameter('type') === 'error') {
            $output->writeln(sprintf('Cannot setup webhook to %s: <error>%s</error>', $webhookUrl, $response->getContentParameter('details')[0]['message']));
        } else {
            $output->writeln(sprintf('New updated webhook to <info>%s</info>:', $webhookUrl, $response->getContentParameter('url')));
        }
    }

    /**
     * Resolve the new webhook url to set. Its the base Webhook controller route by
     * default but can be overriden by the configuration "webhook" node.
     *
     * @param \ContainerInterface
     * @return string Final webhook url path
     */
    private function resolveWebhookUrl(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $defaultWebhookUrl = $this->router->generate('adadgio_box_api_webhook_notification', array(), UrlGeneratorInterface::ABSOLUTE_URL);
        $configWebhookUrl = $container->getParameter('adadgio_box_api.box_view')['webhook'];

        return (null === $configWebhookUrl) ? $defaultWebhookUrl : $configWebhookUrl;
    }
}
