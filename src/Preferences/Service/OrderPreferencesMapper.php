<?php
declare(strict_types=1);

namespace App\Preferences\Service;

use App\Entity\Client;
use App\Entity\Staff;
use App\Entity\User;
use App\Preferences\Model\DateType;
use App\Preferences\Model\OrderPreferences;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.preferences.mapper')]
class OrderPreferencesMapper implements MapperInterface
{
    private const string DEFAULT_PREFERENCES_PATH = __DIR__ . '/../../Resources/default_orders_preferences.php';

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function fromArray(User $user): void
    {
        $array = $user->getPreferences()->_data[OrderPreferences::NAME] ?? require self::DEFAULT_PREFERENCES_PATH;

        $client = $staff = null;
        if (!empty($array['client'])) {
            $client = $this->entityManager->getReference(Client::class, $array['client']);
        }

        if (!empty($array['staff'])) {
            $staff = $this->entityManager->getReference(Staff::class, $array['staff']);
        }

        $result = new OrderPreferences();
        $result
            ->setDateType(DateType::tryFrom($array['date_type'] ?? '') ?? null)
            ->setColumns($array['columns'] ?? [])
            ->setClient($client)
            ->setStaff($staff)
            ->setDateFrom(isset($array['date_from']) ? new DateTime($array['date_from']['date']) : null)
            ->setDateTo(isset($array['date_to']) ? new DateTime($array['date_to']['date']) : null)
            ->setStates($array['states'] ?? null)
            ->setDeleted($array['deleted'] ?? null)
            ->setSettled($array['settled'] ?? null);

        $user->getPreferences()->setOrdersPreferences($result);
    }
}