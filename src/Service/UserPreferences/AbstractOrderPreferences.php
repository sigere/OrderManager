<?php

namespace App\Service\UserPreferences;

use App\Entity\Client;
use App\Entity\Staff;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Security;

abstract class AbstractOrderPreferences
{
    public const COLUMNS = [
        'adoption',
        'client',
        'topic',
        'lang',
        'deadline',
        'staff'
    ];
    public const DATE_TYPE_DEADLINE = "deadline";
    public const DATE_TYPE_ADOPTION = "adoption";

    protected array $columns;
    protected ?Client $client;
    protected ?Staff $staff;
    protected ?\DateTime $dateFrom;
    protected ?\DateTime $dateTo;
    protected string $dateType;
    protected $user;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        Security $security,
        KernelInterface $kernel
    ) {
        $this->user = $security->getUser();
        $config = $this->user->getPreferences()[$this->getArrayKey()]
            ?? json_decode(
                file_get_contents(
                    $kernel->getProjectDir() . '/config/default_' . $this->getArrayKey() . '_preferences.json'
                ),
                true);
        $this->load($config);
    }

    /**
     * @return string
     */
    protected abstract function getArrayKey(): string;

    /**
     * @return void
     */
    public function save(): void
    {
        $preferences = $this->user->getPreferences();
        $preferences[$this->getArrayKey()] = $this->encode();
        $this->user->setPreferences($preferences);
        $this->entityManager->persist($this->user);
        $this->entityManager->flush();
    }

    protected function encode(): array
    {
        return [
            'columns' => $this->columns,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'date_type' => $this->dateType,
            'client' => $this->client?->getId(),
            'staff' => $this->staff?->getId(),
        ];
    }

    public function applyForm(mixed $data): void
    {
        $this->setDateFrom($data['date-from'] ?? null);
        $this->setDateTo($data['date-to'] ?? null);
        $this->setStaff($data['select-staff'] ?? null);
        $this->setClient($data['select-client'] ?? null);

        if (isset($data['date-type']) &&
            in_array(
                $data['date-type'],
                [self::DATE_TYPE_DEADLINE, self::DATE_TYPE_ADOPTION]
            )
        ) {
            $this->setDateType($data['date-type']);
        }
    }

    protected function load(array $config): void
    {
        $this->columns = $config['columns'] ?? [];

        $this->dateFrom = isset($config['date_from']) ? new \DateTime($config['date_from']['date']) : null;
        $this->dateTo= isset($config['date_to']) ? new \DateTime($config['date_to']['date']) : null;

        $this->dateType = ($config['date_type'] ?? "") == self::DATE_TYPE_ADOPTION
            ? self::DATE_TYPE_ADOPTION : self::DATE_TYPE_DEADLINE;

        $this->client = $this->entityManager
            ->getRepository(Client::class)
            ->findOneBy([
                'id' => ($config['client'] ?? 0)
            ]);

        $this->staff = $this->entityManager
            ->getRepository(Staff::class)
            ->findOneBy([
                'id' => ($config['staff'] ?? 0)
            ]);
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param array $columns
     * @return AbstractOrderPreferences
     */
    public function setColumns(array $columns): AbstractOrderPreferences
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @return Client|null
     */
    public function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * @param Client|null $client
     * @return AbstractOrderPreferences
     */
    public function setClient(?Client $client): AbstractOrderPreferences
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return Staff|null
     */
    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    /**
     * @param Staff|null $staff
     * @return AbstractOrderPreferences
     */
    public function setStaff(?Staff $staff): AbstractOrderPreferences
    {
        $this->staff = $staff;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateFrom(): ?\DateTime
    {
        return $this->dateFrom;
    }

    /**
     * @param \DateTime|null $dateFrom
     * @return AbstractOrderPreferences
     */
    public function setDateFrom(?\DateTime $dateFrom): AbstractOrderPreferences
    {
        $this->dateFrom = $dateFrom;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateTo(): ?\DateTime
    {
        return $this->dateTo;
    }

    /**
     * @param \DateTime|null $dateTo
     * @return AbstractOrderPreferences
     */
    public function setDateTo(?\DateTime $dateTo): AbstractOrderPreferences
    {
        $this->dateTo = $dateTo;
        return $this;
    }

    /**
     * @return string
     */
    public function getDateType(): string
    {
        return $this->dateType;
    }

    /**
     * @param string $dateType
     * @return AbstractOrderPreferences
     */
    public function setDateType(string $dateType): AbstractOrderPreferences
    {
        $this->dateType = $dateType;
        return $this;
    }
}
