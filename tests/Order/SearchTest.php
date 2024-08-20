<?php

declare(strict_types=1);

namespace App\Tests\Order;

use App\Tests\AppWebTestCase;

class SearchTest extends AppWebTestCase
{
    public function testSearchOrder(): void
    {
        $this->client->loginUser($this->getUser());

        $this->client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');
        $this->client->request('GET', '/api/order/search');

        $crawler = $this->getCrawlerOnJSONResponse('renderedSearch');
        $form = $crawler->selectButton('Search')->form();

        $this->client->submit($form, ['orders_search_form' => ['id' => 1000]]);

        $this->assertResponseStatusCodeSame(404);
    }
}
