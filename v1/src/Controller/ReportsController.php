<?php

namespace App\Controller;

use App\Reports\AbstractReportForm;
use App\Reports\Exception\MissingParameterException;
use App\Reports\ReportsFactory;
use App\Service\ResponseFormatter;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Twig;

/**
 * @Route("/reports")
 */
class ReportsController extends AbstractController
{
    public function __construct(
        private ReportsFactory $factory,
        private Twig\Environment $twig,
        private ResponseFormatter $formatter
    ) {
    }

    /**
     * @Route("/", name="reports")
     */
    public function index(): Response
    {
        return $this->render(
            'reports/index.html.twig',
            ['reports' => $this->factory->getAvailableReports()]
        );
    }

    /**
     * @param Request $request
     * @param $report
     * @return Response
     * @throws MissingParameterException
     * @throws Twig\Error\LoaderError
     * @throws Twig\Error\RuntimeError
     * @throws Twig\Error\SyntaxError
     * @Route("/execute/{report}", methods={"GET", "POST"}, name="reports_execute")
     */
    public function execute(Request $request, $report): Response
    {
        $service = $this->factory->getReport($report);
        if (!$service) {
            return new Response(
                $this->formatter->error("Nie znaleziono raportu \"" . $report),
                403
            );
        }

        $formFQCN = $service->getFormFQCN();
        if (!is_subclass_of($formFQCN, AbstractReportForm::class)) {
            throw new Exception($formFQCN . " must extend " . AbstractReportForm::class);
        }

        $form = $this->createForm($formFQCN);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $service->configure($form->getData());
            $data = $service->getData();
            $options = [[
                'label' => 'Download',
                'icon' => 'download',
                'class' => 'js-download-link'
            ]];

            $rowsCount = $service->getRowsCount();

            return new JsonResponse([
                'success' => true,
                'content' => $this->twig->render('reports/preview.html.twig', [
                    'data' => $data,
                ]),
                'rowsCount' => $this->twig->render('rows_count.html.twig', [
                    'rowsFound' => $rowsCount,
                    'rowsShown' => min($rowsCount, 1000),
                ]),
                'burger' => $this->twig->render(
                    'burger.html.twig',
                    ['options' => $options]
                )
            ]);
        }

        return $this->render("reports/form.html.twig", [
            'form' => $form->createView(),
            'reportName' => $service->getNameForUI()
            ]);
    }

    /**
     * @param Request $request
     * @param $report
     * @return Response
     * @throws Exception
     * @Route("/export/{report}", methods={"GET"}, name="reports_export")
     */
    public function export(Request $request, $report): Response
    {
        $service = $this->factory->getReport($report);

        if (!$service) {
            return new Response(
                $this->formatter->error("Nie znaleziono raportu \"" . $report),
                400
            );
        }

        $formFQCN = $service->getFormFQCN();
        $form = $this->createForm($formFQCN);
        $form->handleRequest($request);
        $service->configure($form->getData());

        $result = $service->exportToXLSX();

        return new JsonResponse(['success' => true, 'path' => $result]);
    }

    /**
     * @param $file
     * @return Response
     * @Route("/download/{file}", name="reports_download")
     */
    public function download($file) : Response
    {
        if (file_exists('../var/tmp/' . $file)) {
            $response = new BinaryFileResponse(
                '../var/tmp/' . $file,
                200,
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            );
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $file . '.xlsx'
            );
            return $response;
        }
        return new JsonResponse(['success' => false, 'error' => 'File "' . $file . '" not found.']);
    }
}
