<?php
namespace App\Tests\Controller\Api;

use App\DataFixtures\AppFixtures;

/**
 * @author DIAKITE SOUMAILA <soumaila.diakite@veone.net>
 */
class TokenControllerTest extends AbstractControllerTest
{    
    public function testThatCanCreateToken()
    { 
        $this->loadFixture(new AppFixtures());  
        $this->client->request('POST', '/v1/rest/tokens', [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
        ], '{"username": "soum@", "password": "aaaaaaaa"}');
        
        $this->assertNotEmpty($this->client->getResponse()->getContent());
        $this->assertResponseStatusCodeSame(200, $this->client->getResponse()->getStatusCode());                  
    }

    public function testThatCannotCreateTokenWithBadInfo()
    {         
        $this->client->request('POST', '/v1/rest/tokens', [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
        ], '{"username": "soum@", "password": "aaa"}');
        
        $this->assertResponseStatusCodeSame(401);                         
    }

    public function testThatCannotValidateInvalidToken()
    { 
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9-miqKqYXQ9agFs6HM'));
        $this->client->request('GET', '/v1/rest/tokens/validate', [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json'
        ]);

        $this->assertResponseStatusCodeSame(401, $this->client->getResponse()->getStatusCode());                       
    }

    public function testThatCannotValidateExpiredToken()
    { 
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE1ODQxMTE5MTYsImV4cCI6MTU4NDExNTUxNiwicm9sZXMiOlsiUk9MRV9BREhFUkVOVCJdLCJ1c2VybmFtZSI6Im1ldW5pZXJAZ21haWwuY29tIn0.e5Ht_WC4x-rWyAwG8nd-otv3jnCKiVTm5ZJIHOVpWYVyAO4ZMeu3Ab7-6b-U4T5-NtqbAfuf5N8jkDhodf1VAYgFyTQYiZDmGVEitODI-QEpNVGKphcCdNjc6m0dxaOeADf5D3S_iSmWsgLAAC7tDAaEZQ3UGS9Xu2z2Y8wHeBrhCzw51_W6RNe0phuiJOE59wLokINjaOx5L8o4mh60BUOIFeEnvzH5CgzsHek_cJzh4mrAugkPb0mf6fJ9VyNWLRZaqbw9uMyN1DQUnVW9GiD7Z68sKD9o7BPlcN_pNFgjtmNirU6KeD5mxvitVqOAjQXstcRoJN2n5Mef3_IRNj7LNPhxKnOUgliFZp68rfB2P-tAKtAszRmgpmXVPP5zEfdABeSbRF3KlVHRV-_cRCmniDrVaH9ryPzjvV3gIoSQcLL7iZiLk1l09HAAp0wNY19Pk--f23ZvTppZ3yEFQAo-MnGXBpFGXhg6OcABliPTJCelcIrqYmgu6J_Lkt6Bc8vVKhm1r26HSszs4-ZaPcPkmBfjseyZtdaMAcyFnIZKRnhw7LywrFIEZ3hRZplWm5c7S6PZEMALiQcy3wg6N-bYcIZA9mr_BepoEWs24OuUaHaRjzEiZeUDkyNm_As-zyM5yPL0bFuFo2fBSO6YTwe3gwfwXq_AauS1IPEHv5I'));
        $this->client->request('GET', '/v1/rest/tokens/validate', [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json'
        ]);

        $this->assertResponseStatusCodeSame(401, $this->client->getResponse()->getStatusCode());                      
    }

    public function testThatCanValidateToken()
    { 
        $this->loadFixture(new AppFixtures());
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/v1/rest/tokens/validate', [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json'
        ]);
        
        $this->assertResponseStatusCodeSame(204, $client->getResponse()->getStatusCode());                       
    } 
    
    public function testThatCanRevokeRefreshToken()
    { 
        $this->loadFixture(new AppFixtures());
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/v1/rest/tokens/revoke', [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json'
        ]);
        
        $this->assertResponseStatusCodeSame(302, $client->getResponse()->getStatusCode());                       
    }  

    public function testThatCanListsRefreshToken()
    { 
        $this->loadFixture(new AppFixtures());
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/v1/rest/tokens/list', [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json'
        ]);

        $this->assertNotEmpty($client->getResponse()->getContent());
        $this->assertResponseStatusCodeSame(200, $client->getResponse()->getStatusCode());                       
    } 

    public function testThatCanRefreshToken()
    {  
        //I generate a jwt and a refresh jwt
        $this->loadFixture(new AppFixtures());  
        $this->client->request('POST', '/v1/rest/tokens', [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
        ], '{"username": "soum@", "password": "aaaaaaaa"}');        

        //I take a refresh jwt of a user that I created previously. I use email to refresh jwt token
        $refreshToken = $this->refreshTokenManager->getLastFromUsername('soum@');

        // I refresh user jwt token
        $this->client->request('POST', '/v1/rest/tokens/refresh', [], [], 
        [
			'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json'
        ], '{
            "refresh_token" : "'.$refreshToken.'"
            }'
        );
        $secondData = json_decode($this->client->getResponse()->getContent(), true);
        $secondToken = $secondData['token'];

        $this->assertNotEmpty($secondToken);
        $this->assertResponseStatusCodeSame(200, $this->client->getResponse()->getStatusCode());                     
    }
    
}
