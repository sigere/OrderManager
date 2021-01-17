<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/addOrder", methods={"POST"})
     */
    public function addOrder(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tmp = validate($request);
        if ($tmp['error']) {
            return new JsonResponse(['error' => $tmp['error']]);
        }

        $data = $tmp['data'];

        $order = new Order();
        $order->setAuthor($this->getUser());

        $order->setClient($data['client']);
        $order->setTopic($data['topic']);
        $order->setBaseLang($data['baseLang']);
        $order->setTargetLang($data['targetLang']);
        $order->setDeadline($data['deadlineData'].' '.$data['deadlineTime']);
        $order->setStaff($data['staff']);
        $order->setInfo($data['info']);
        $order->setPages($data['pages']);
        $order->setPrie($data['price']);
        $order->setAdoption($data['adoption']);
        $order->setCertified($data['certified']);

        $entityManager->persist($order);

        $log = new Log($this->getUser(), 'Created new order', $order);
        $entityManager->persist($log);
    }

    private function validate(Request $request, EntityManagerInterface $entityManager): array
    {
        $data['client'] = $request->get('client');
        $data['topic'] = $request->get('topic');
        $data['baseLang'] = $request->get('baseLang');
        $data['targetLang'] = $request->get('targetLang');
        $data['deadlineDate'] = $request->get('deadlineDate');
        $data['staff'] = $request->get('staff');
        $data['certified'] = $request->get('cerified');

        foreach ($data as $i) {
            if (!$i) {
                return ['error' => 'Wprowadzono niekompletne dane.'];
            }
        }

        //nullable
        $data['info'] = $request->get('info');
        $data['pages'] = $request->get('pages');
        $data['price'] = $request->get('price');
        $data['adoption'] = $request->get('adoptionDate');
        $data['deadlineTime'] = $request->get('deadlineTime');

        switch ($data['certified']) {
            case 'Tak':
            case 'tak':
                $data['certified'] = true;
                break;
            case 'Nie':
            case 'nie':
                $data['certified'] = false;
                break;
            default:
                return ['error' => 'Wprowadzono niepoprawną wartość UW'];
        }

        if (!validateDate($data['deadlineDate'], 'Y-m-d')) {
            return ['error' => 'Wprowadzono błędną datę terminu.'];
        }

        if (!$data['deadlineTime'] || '' == $data['deadlineTime']) {
            $data['deadlineTime'] = '00:00';
        } elseif (!validateDate($data['deadlineTime'], 'H:i')) {
            return ['error' => 'Wprowadzono błędną godzinę terminu.'];
        }

        if (!validateDate($data['adoption'], 'Y-m-d')) {
            return ['error' => 'Wprowadzono błędną datę dodania.'];
        }

        $client = $entityManager->getRepository(Client::class)->findOneBy([
            'alias' => $data['client'],
            ]);
        if (!$data['client'] = $client) {
            return ['error' => 'Nie znaleziono podanego klienta.'];
        }

        $staff = $entityManager->getRepository(Staff::class)->findOneBy([
            'firstName' => explode(' ', trim($data['staff']))[0],
            ]);
        if (!$data['staff'] = $staff) {
            return ['error' => 'Nie znaleziono podanego pracownika'];
        }

        $baseLang = $entityManager->getRepository(Lang::class)->findOneBy([
            'short' => $data['baseLang'],
            ]);
        if (!$data['baseLang'] = $baseLang) {
            return ['error' => 'Nie znaleziono podanego języka "z"'];
        }

        $targetLang = $entityManager->getRepository(Lang::class)->findOneBy([
            'short' => $data['targetLang'],
            ]);
        if (!$data['targetLang'] = $targetLang) {
            return ['error' => 'Nie znaleziono podanego języka "na"'];
        }

        if (!is_numeric($data['price'])) {
            return ['error' => 'Cena za stronę musi być liczbą.'];
        }
        if (!is_numeric($data['pages'])) {
            return ['error' => 'Liczba stron musi być liczbą.'];
        }

        return [
            'data' => $data,
            'error' => null,
        ];
    }

    private function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }
}
