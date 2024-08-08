<?php
declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

readonly class OrderService
{
    public function __construct(
        private Environment $twig,
        private RouterInterface $router,
    ) {
    }

    public function renderSearchForm(FormInterface $form, ?array $orders = null): string
    {
        return $this->twig->render('orders/_search.html.twig', [
                'form' => $form->createView(),
                'endpointData' => [
                    'data-url' => $this->router->generate(
                        name: 'api_orders_search_post', referenceType: UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    'data-method' => 'POST',
                ],
                'orders' => $orders,
                'rowsFound' => count($orders),
                'rowsShown' => count($orders),
                'cache' => [
                    'search-form' => ['key' => '', 'tags' => [], 'ttl' => new \DateInterval('P1D')],
                ],
            ]);
    }
}