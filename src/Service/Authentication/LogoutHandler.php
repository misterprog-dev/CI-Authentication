<?php
namespace App\Service\Authentication;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutHandler implements LogoutHandlerInterface, LogoutSuccessHandlerInterface
{
    /** @var Connection */
    private $databaseConnection;
	
    public function __construct(Connection $databaseConnection) {
        $this->databaseConnection = $databaseConnection;
    }

    /**
     * Supprimer le refresh_token après déconnexion
     *
     * @param Request $request
     * @param Response $response
     * @param TokenInterface $token
     * @return void
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $authenticatedUser = $token->getUser();
        if (null === $authenticatedUser) {
            return;
        }
        $this->databaseConnection->exec(sprintf('
            DELETE FROM refresh_tokens
            WHERE username = "%s"
        ', $authenticatedUser->getUsername()));
    }

    public function onLogoutSuccess(Request $request)
    {}
}
