<?php
namespace App\Tests\Controller\Api;

use App\Entity\User;
use App\DataFixtures\AppFixtures;
use Doctrine\ORM\EntityManager;

/**
 * @author DIAKITE SOUMAILA <soumaila.diakite@veone.net>
 */
class UserControllerTest extends AbstractControllerTest
{   
    public function testThatCanCreateUser()
    {        
        $this->client->request('POST', '/v1/rest/users', [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
        ], '{
            "username" : "john@",
            "email" : "john@gmail.com",
            "password" : "john",
            "firstName" : "John",
            "lastName" : "DADIE"
        }');
        /** @var EntityManager $em */
        $em = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'john@gmail.com']);

        $this->assertEquals('john@gmail.com', $user->getEmail());
        $this->assertResponseStatusCodeSame(204, $this->client->getResponse()->getStatusCode());
    }
    
    public function testThatCannotCreateUserWhoAlreadyExists()
    { 
        $this->loadFixture(new AppFixtures());        
        $this->client->request('POST', '/v1/rest/users', [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
        ], '{
            "username" : "soum@",
            "email" : "soum@yahoo.com",
            "password" : "aaaaaaaaaaa"
        }');

        $this->assertResponseStatusCodeSame(409, $this->client->getResponse()->getStatusCode());
    } 
    
    public function testThatCanActivateUserAccount()
    { 
        // I create my user         
        $this->client->request('POST', '/v1/rest/users', [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
        ], '{
            "username" : "john@",
            "email" : "john@gmail.com",
            "password" : "john",
            "firstName" : "John",
            "lastName" : "DADIE"
        }');

        //I get my user I just create
        /** @var EntityManager $em */
        $em = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'john@gmail.com']);

        //I activate his account
        $this->client->request('GET', '/v1/rest/users?token='.$user->getConfirmationToken(), [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
        ]);
         /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'john@gmail.com']);

        $this->assertEquals(true, $user->isEnabled());
        $this->assertResponseStatusCodeSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testThatCannotActivateUserAccountWhoDoNotExists()
    {  
        $this->loadFixture(new AppFixtures());        
        $this->client->request('GET', '/v1/rest/users?token=zezezdscscnjdcn', [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
        ]);
        $this->assertResponseStatusCodeSame(404, $this->client->getResponse()->getStatusCode());
    }

    public function testThatCanGetIndexPage()
    {
        $this->loadFixture(new AppFixtures());
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/v1/rest/index', [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json'
        ]);
        $this->assertResponseStatusCodeSame(200, $client->getResponse()->getStatusCode());                       
    }

    public function testThatCanGetIndexP()
    {
        $this->loadFixture(new AppFixtures());
        // $client = $this->createAuthenticatedClient();
        $client->request('GET', '/v1/rest/index', [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json'
        ]);
        $this->assertResponseStatusCodeSame(200, $client->getResponse()->getStatusCode());                       
    }

    public function testThatCannotGetIndexPage()
    {           
        $this->client->request('GET', '/v1/rest/index', [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json'
        ]);
        $this->assertResponseStatusCodeSame(401, $this->client->getResponse()->getStatusCode());                       
    }
}
