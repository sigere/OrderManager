<?php

namespace App\Controller;

use App\Service\ReportsFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    public function __construct(
        private ReportsFactory $factory
    ) {
    }

    /**
     * @param Request $request
     * @param $report
     * @return JsonResponse
     * @Route("/api/reports/{report}", name="api_reports")
     */
    public function report(Request $request, $report) : JsonResponse
    {
        $service = match($report) {
            'CertifiedUaPl' => $this->factory->getReportService(ReportsFactory::CERTIFIED_UA_PL),
            default => null
        };

        if (!$service) {
            return new JsonResponse(['success' => false, 'error' => 'Report type not found.']);
        }

        $from = $request->get('from');
        $to = $request->get('to');

        if ($from) {
            try {
                $from = new \DateTime($from);
            } catch (\Exception $e) {
                return new JsonResponse(['success' => false, 'error' => 'Cannot parse "from" value.']);
            }
        }

        if ($to) {
            try {
                $from = new \DateTime($to);
            } catch (\Exception $e) {
                return new JsonResponse(['success' => false, 'error' => 'Cannot parse "to" value.']);
            }
        }

        $service->configure([
            'from' => $from?->format('Y-m-d 00:00:00'),
            'to' => $to?->format('Y-m-d 23:59:59'),
            ]);

        $result = $service->export();

        return new JsonResponse(['success' => true, 'path' => $result]);
    }

    /**
     * @param $report
     * @return Response
     * @Route("/api/reports/get/{report}", name="api_reports_get")
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
