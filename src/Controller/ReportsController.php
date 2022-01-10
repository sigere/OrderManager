<?php

namespace App\Controller;

use App\Service\ReportsFactory;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Twig;

class ReportsController extends AbstractController
{
    public function __construct(
        private ReportsFactory $factory,
        private Twig\Environment $twig
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
     * @Route("/reports/api/form/{report}", name="reports_form_report")
     */
    public function getForm($report): JsonResponse
    {
        $service = $this->factory->getReportService($report);
        return new JsonResponse([
            'success' => true,
            'form' => $service->renderForm()
        ]);
    }

    /**
     * @Route("/reports/api/details/{report}", name="reports_form_details")
     */
    public function getDetails($report): JsonResponse
    {
        foreach (ReportsFactory::REPORTS as $rep) {
            if ($rep['id'] == $report) {
                return new JsonResponse([
                    'success' => true,
                    'details' => $rep['details']
                ]);
            }
        }
        return new JsonResponse(['success' => false, 'error' => 'Report not found.']);
    }

    /**
     * @param Request $request
     * @param $report
     * @return JsonResponse
     * @throws Exception
     * @Route("/reports/api/preview/{report}", name="reports_preview_report")
     */
    public function getPreview(Request $request, $report): JsonResponse
    {
        $service = $this->factory->getReportService($report);

        if (!$service) {
            return new JsonResponse(['success' => false, 'error' => 'Report type not found.']);
        }

        $service->configure($request);

        $preview = $service->getPreview();

        return  new JsonResponse([
            'success' => true,
            'preview' => $this->twig->render('reports/preview.html.twig', ['preview' => $preview])
        ]);
    }

    /**
     * @param Request $request
     * @param $report
     * @return JsonResponse
     * @throws Exception
     * @Route("/reports/api/export/{report}", name="reports_export_report")
     */
    public function export(Request $request, $report): JsonResponse
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
