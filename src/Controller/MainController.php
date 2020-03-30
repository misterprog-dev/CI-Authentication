<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Oka\RESTRequestValidatorBundle\Annotation\AccessControl;
use Oka\RESTRequestValidatorBundle\Service\ErrorResponseFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author DIAKITE SOUMAILA <soumaila.diakite@veone.net>
 */
class MainController extends AbstractController
{
    private $logger;
        
    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @param string $version
     * @param string $protocol
     * @param string $format
     * @return JsonResponse
     * @Route(name="index", methods={"GET"}, path="/index")
     * @AccessControl(version="v1", protocol="rest", formats="json")
     */
    public function index(Request $request, $version, $protocol, $format)
    {                               
        $response = new Response();
        $response->setContent('<html><body><h1>Hello world!</h1></body></html>');
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    public static function getSubscribedServices() {
        return array_merge(parent::getSubscribedServices(), [
          'translator' => '?'.TranslatorInterface::class,
          'oka_rest_request_validator.error_response.factory' => '?'.ErrorResponseFactory::class
        ]);
    }
}
