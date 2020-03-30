<?php
namespace App\Tests\Controller\Api;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Gesdinet\JWTRefreshTokenBundle\Doctrine\RefreshTokenManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author DIAKITE SOUMAILA <soumaila.diakite@veone.net>
 */
class AbstractControllerTest extends WebTestCase
{   
    /** @var Client $client */
    protected $client;
    /** @var RefreshTokenManager */
    protected $refreshTokenManager;
    /** @var EntityManager $manager */
    protected $manager;
    /** @var ORMExecutor $executor */
    protected $executor;    

    protected function setUp()
    {
        $this->client = static::createClient();
        $this->refreshTokenManager = $this->getContainer()->get('gesdinet.jwtrefreshtoken.refresh_token_manager');

        $this->manager = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->executor = new ORMExecutor($this->manager, new ORMPurger());

        $schemaTool = new SchemaTool($this->manager);
        $metadata = $this->manager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }    

    protected function getContainer(): ContainerInterface
    {
        return self::$container;
    }

    protected function loadFixture($fixture)
    {
        $loader = new Loader();
        $fixtures = is_array($fixture) ? $fixture : [$fixture];
        foreach ($fixtures as $item) {
            $loader->addFixture($item);
        }
        $this->executor->execute($loader->getFixtures());
    }    

     /**
     * Create a client with a default Authorization header.
     *
     * @param string $username
     * @param string $password
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function createAuthenticatedClient($username = 'soum@', $password = 'aaaaaaaa') // Ref my fixture values
    {
        $client = static::createClient();
        $client->request('POST', '/v1/rest/tokens', [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
        ],
        json_encode(array(
            'username' => $username,
            'password' => $password
            ))
        );
        dump($client->getResponse()->getContent());
        $data = json_decode($client->getResponse()->getContent(), true);       

        $client = static::createClient();
        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $client;
    }

    public function tearDown()
    {
        $this->manager->flush();
        $this->manager = null;
    } 
}
