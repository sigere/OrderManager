<?php

namespace App\Controller;

use App\Service\Reports\CertifiedUaPlReport;
use App\Service\ReportsFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class ReportsController extends AbstractController
{
    public function __construct(
        private ReportsFactory $factory
    ) {
    }

    /**
     * @Route("/reports", name="reports")
     */
    public function index(): Response
    {
        return $this->render(
            'reports/index.html.twig',
            ['reports' => ReportsFactory::REPORTS]
        );
    }

    /**
     * @param Request $request
     * @param $report
     * @return JsonResponse
     * @throws \Exception
     * @Route("/reports/api/{report}", name="reports_report")
     */
    public function report(Request $request, $report) : JsonResponse
    {
        $service = $this->factory->getReportService($report);

        if (!$service) {
            return new JsonResponse(['success' => false, 'error' => 'Report type not found.']);
        }

        $service->configure($request);

        $result = $service->export();

        return new JsonResponse(['success' => true, 'path' => $result]);
    }

    /**
     * @param $report
     * @return Response
     * @Route("/reports/api/get/{report}", name="reports_get_report")
     */
    public function getReport($report) : Response
    {
        if (file_exists('../var/tmp/' . $report)) {
            $response = new BinaryFileResponse(
                '../var/tmp/' . $report,
                200,
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            );
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'certifed_ua_pl.xlsx'
            );
            return $response;
        }
        return new JsonResponse(['success' => false, 'error' => 'File "' . $report . '" not found.']);
    }
}