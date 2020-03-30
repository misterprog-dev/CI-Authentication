<?php
namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Email\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Oka\RESTRequestValidatorBundle\Annotation\AccessControl;
use Oka\RESTRequestValidatorBundle\Annotation\RequestContent;
use Oka\RESTRequestValidatorBundle\Service\ErrorResponseFactory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author DIAKITE SOUMAILA <soumaila.diakite@veone.net>
 * 
 *@Route(name="app_user_", path="/users", requirements={"name": "^[a-zA-Z0-9]+$"})
 */
class UserController extends AbstractController
{
    private $logger;
    private $encoder;
    private $entityManager;
	
    public function __construct(LoggerInterface $logger, UserPasswordEncoderInterface $encoder, EntityManagerInterface $entityManager) {
        $this->logger = $logger;
        $this->encoder = $encoder;
        $this->entityManager = $entityManager;        
    }

    /**
     * Save user
     *
     * @param Request $request
     * @param string $version
     * @param string $protocol
     * @param string $format
     * @return JsonResponse
     * @param array $requestContent
     * @Route(name="save", methods={"POST"})
     * @AccessControl(version="v1", protocol="rest", formats="json")
     * @RequestContent(constraints="saveConstraints")
     */
    public function save(Request $request, $version, $protocol, $format, array $requestContent, ValidatorInterface $validator, UserRepository $userRepo, Mailer $mailer)
    {        
        $user = new User();
        $user->setUsername($requestContent['username'])
            ->setEmail($requestContent['email'])
            ->setPassword($this->encoder->encodePassword($user, $requestContent['password']));
        if (isset($requestContent['firstName'])) 
            $user->setFirstName($requestContent['firstName']);
        if (isset($requestContent['lastName'])) 
            $user->setLastName($requestContent['lastName']);
        if (isset($requestContent['phoneNumber'])) 
            $user->setPhoneNumber($requestContent['phoneNumber']);
        
        /* $errors = $validator->validate($user, null, ['Create']);
        if (count($errors) > 0) {
            return $this->get('oka_rest_request_validator.error_response.factory')->create($this->get('translator')->trans('user.already_used', [], 'error'), 409, null, [], 409);
        }*/

        try {            
            $olderuser = $userRepo->findOneBy(array('username' => $requestContent['username'], 'email' => $requestContent['email']));
            if ($olderuser) {
                return $this->get('oka_rest_request_validator.error_response.factory')->create($this->get('translator')->trans('user.already_used', [], 'error'), 409, null, [], 409);
            }    
            $user->setConfirmationToken($this->generateToken());        
            $this->entityManager->persist($user);
            $this->entityManager->flush();   
            $mailer->sendConfirmationEmail($user);
            
        } catch (Exception $e) {
            return $this->get('oka_rest_request_validator.error_response.factory')->create($this->get('translator')->trans('database.unexpected_error', [], 'error'), 500, null, [], 500);
        }                
        return new JsonResponse(null, 204);
    }

    /**     
     * @param Request $request
     * @param string $version
     * @param string $protocol
     * @param string $format
     * @return JsonResponse
     * @Route(name="activate", methods={"GET"})
     * @AccessControl(version="v1", protocol="rest", formats="json")
     */
    public function activateAccount(Request $request, $version, $protocol, $format, UserRepository $userRepo)
    {
        $token = $request->query->get('token');
        $user = $userRepo->findOneBy(['confirmationToken' => $token]);
        if(null === $user) {
            return $this->get('oka_rest_request_validator.error_response.factory')->create($this->get('translator')->trans('user.user_data_error', [], 'error'), 404, null, [], 404);
        }
        try {
            $user->setEnabled(true);            
            $this->entityManager->flush();
        } catch (Exception $e) {    
            $response = $e->getCode();
            if ($response == 404) {
                return $this->get('oka_rest_request_validator.error_response.factory')->create($this->get('translator')->trans('user.user_data_error', [], 'error'), 404, null, [], 404);
            }
            return $this->get('oka_rest_request_validator.error_response.factory')->create($this->get('translator')->trans('database.unexpected_error', [], 'error'), 500, null, [], 500);
        }           
        return new JsonResponse(null, 200);
    }

    /**
     * Generate confirmation token
     *
     * @return string
     */
    private function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    public static function getSubscribedServices() {
        return array_merge(parent::getSubscribedServices(), [
          'translator' => '?'.TranslatorInterface::class,
          'oka_rest_request_validator.error_response.factory' => '?'.ErrorResponseFactory::class
        ]);
    }

    private static function saveConstraints() :Assert\Collection {
        return new Assert\Collection([
            'username' => new Assert\Required([new Assert\NotBlank(), new Assert\NotNull()]),
            'email' => new Assert\Required([new Assert\Email(), new Assert\NotNull()]),
            'password' => new Assert\Required([new Assert\NotNull()]),
            'firstName' => new Assert\Optional([new Assert\NotNull()]),
            'lastName' => new Assert\Optional([new Assert\NotNull()]),
            'phoneNumber' => new Assert\Optional([new Assert\NotNull(), new Assert\Length(['max' => 15, 'min' => 8])])
        ]);
    }    
}
