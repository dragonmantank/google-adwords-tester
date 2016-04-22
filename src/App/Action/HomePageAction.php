<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;
use Zend\Expressive\Plates\PlatesRenderer;
use Zend\Expressive\Twig\TwigRenderer;
use Zend\Expressive\ZendView\ZendViewRenderer;

class HomePageAction
{
    private $router;

    private $template;

    public function __construct(Router\RouterInterface $router, Template\TemplateRendererInterface $template = null)
    {
        $this->router   = $router;
        $this->template = $template;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $data = [
            'report' => '',
            'developer_token' => '',
            'client_id' => '',
            'client_secret' => '',
            'refresh_token' => '',
            'client_customer_id' => '',
            'service' => '',
            'report_type' => '',
            'fields' => '',
            'predicate' => '',
            'start_date' => '',
            'end_date' => '',
            'output' => '',
        ];

        if ($request->getMethod() == 'POST') {
            $postData = $request->getParsedBody();

            $data['developer_token'] = $postData['developer_token'];
            $data['client_id'] = $postData['client_id'];
            $data['client_secret'] = $postData['client_secret'];
            $data['redirect_uri'] = $postData['redirect_uri'];
            $data['refresh_token'] = $postData['refresh_token'];
            $data['client_customer_id'] = $postData['client_customer_id'];
            $data['service'] = $postData['service'];
            $data['report_type'] = $postData['report_type'];
            $data['fields'] = $postData['fields'];
            $data['start_date'] = $postData['start_date'];
            $data['end_date'] = $postData['end_date'];
            $data['output'] = $postData['output'];
            $data['predicate'] = $postData['predicate'];

            $selectorFields = explode(',', $data['fields']);
            foreach ($selectorFields as $key => $field) {
                $selectorFields[$key] = trim($field);
            }

            $predicates = explode("\n", $data['predicate']);

            $startDate = new \DateTime($data['start_date']);
            $endDate = new \DateTime($data['end_date']);

            $adwordsUser = new \AdWordsUser();
            $adwordsUser->SetDeveloperToken($data['developer_token']);
            $adwordsUser->SetOAuth2Info([
                'client_id'       => $data['client_id'],
                'client_secret'   => $data['client_secret'],
                'refresh_token' => $data['refresh_token'],
            ]);
            $adwordsUser->SetClientCustomerId($data['client_customer_id']);

            $adwordsUser->LoadService($data['service']);
            $selector                 = new \Selector();
            $selector->fields         = $selectorFields;
            foreach ($predicates as $predicate) {
                $predicate = explode(' ', $predicate, 3);
                if (count($predicate) == 3) {
                    $selector->predicates[] = new \Predicate($predicate[0], $predicate[1], $predicate[2]);
                }
            }

            $selector->dateRange      = new \DateRange();
            $selector->dateRange->min = $startDate->format('Ymd');
            $selector->dateRange->max = $endDate->format('Ymd');

            $reportDefinition                 = new \ReportDefinition();
            $reportDefinition->selector       = $selector;
            $reportDefinition->reportName     = 'Sales Orders Ad Performance Report';
            $reportDefinition->dateRangeType  = 'CUSTOM_DATE';
            $reportDefinition->reportType     = $data['report_type'];
            $reportDefinition->downloadFormat = $data['output'];

            \ReportUtils::DownloadReport($reportDefinition, 'data/findme.xml', $adwordsUser, []);
            $report = file_get_contents('data/findme.xml');
            @unlink('data/findme.xml');

            switch ($data['output']) {
                case 'XML':
                    $dom = new \DOMDocument('1.0');
                    $dom->preserveWhiteSpace = false;
                    $dom->formatOutput = true;
                    $dom->loadXML($report);
                    $data['report'] = $dom->saveXML();
                    break;
                case 'CSV':
                    $lines = count(explode("\n", $report));
                    $data['report'] = $report;
                    break;
            }
        }

        return new HtmlResponse($this->template->render('app::home-page', $data));
    }
}
