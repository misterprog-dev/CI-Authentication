<?php
namespace App\Controller\Api;

use Doctrine\DBAL\Connection;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Oka\RESTRequestValidatorBundle\Annotation\AccessControl;
use Oka\RESTRequestValidatorBundle\Service\ErrorResponseFactory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author DIAKITE SOUMAILA <soumaila.diakite@veone.net>
 * 
 *@Route(name="app_token_", path="/tokens", requirements={"name": "^[a-zA-Z0-9]+$"})
 */
class TokenController extends AbstractController
{

  private $jwsProvider;
  private $logger;
  private $token;
  /** @var Connection */
  private $databaseConnection;
	
  public function __construct(LoggerInterface $logger, JWSProviderInterface $jwsProvider, TokenStorageInterface $token, Connection $databaseConnection) {
    $this->logger = $logger;
    $this->jwsProvider = $jwsProvider;
    $this->token = $token;
    $this->databaseConnection = $databaseConnection;
  }

  /**
   * Validate token
   * 
   * @param Request $request
   * @param string $version
   * @param string $protocol
   * @param string $format
   * @Route(name="validate", methods={"GET", "HEAD"}, path="/validate")
   * @AccessControl(version="v1", protocol="rest", formats="json")
   */
  public function validateToken(Request $request, $version, $protocol, $format)
  {  
    $authHeader = str_replace('Bearer ', '', $request->headers->get('Authorization'));     
    if (null !== $authHeader) {
      $token = $this->jwsProvider->load($authHeader);
      if (true === $token->isInvalid()) {
        return $this->get('oka_rest_request_validator.error_response.factory')->create($this->get('translator')->trans('tokens.not_valide', [], 'error'), 401, null, [], 401);
      }
      if (true === $token->isExpired()) {
        return $this->get('oka_rest_request_validator.error_response.factory')->create($this->get('translator')->trans('tokens.expired', [], 'error'), 401, null, [], 401);
      }    
      if (false === $token->isVerified()) {
        return $this->get('oka_rest_request_validator.error_response.factory')->create($this->get('translator')->trans('tokens.unchecked', [], 'error'), 401, null, [], 401);
      }
  
    } else {
      return $this->get('oka_rest_request_validator.error_response.factory')->create($this->get('translator')->trans('http_error.bad_request', [], 'error'), 400, null, [], 400);
    } 
    return new JsonResponse(null, 204); 
  }  
  
  /**
   * List refresh token
   * 
   * @param Request $request
   * @param string $version
   * @param string $protocol
   * @param string $format
   * @Route(name="list", methods={"GET", "HEAD"}, path="/list")
   * @AccessControl(version="v1", protocol="rest", formats="json")
   */
  public function listRefreshToken(Request $request, $version, $protocol, $format)
  {  
    $user = $this->token->getToken()->getUser();    
    if (null !== $user) {
      $data = $this->databaseConnection->query(sprintf('
          SELECT refresh_token FROM refresh_tokens
          WHERE username = "%s"
      ', $user->getEmail()));
      $data->execute();
      $result = [];
      foreach ($data as $value) {
        $result[] = $value['refresh_token'];
      }
    } else {
      return $this->get('oka_rest_request_validator.error_response.factory')->create($this->get('translator')->trans('http_error.bad_request', [], 'error'), 400, null, [], 400);
    } 
    return new JsonResponse(['list_refresh_token' => $result], 200); 
  }

  public static function getSubscribedServices() {
    return array_merge(parent::getSubscribedServices(), [
      'translator' => '?'.TranslatorInterface::class,
      'oka_rest_request_validator.error_response.factory' => '?'.ErrorResponseFactory::class
    ]);
  }
}
