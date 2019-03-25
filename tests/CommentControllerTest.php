<?php
namespace App\Tests;

use App\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class CommentControllerTest extends WebTestCase
{
    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    private function login($username, $password)
    {
        $session = $this->client->getContainer()->get('session');

        $firewallName = 'secured_area';

        $authenticationManager = $this->client->getContainer()->get('public.authentication.manager');
        $token = $authenticationManager->authenticate(
            new UsernamePasswordToken(
                $username, $password,
                $firewallName
            ));

        $session->set('_security_' . $firewallName, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }



    public function testEditComment()
    {
        $this->login("test", "test");
        $crawler = $this->client->request('GET', '/comment/edit/34670615');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertTrue($crawler->filter('html:contains("Content")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("Save")')->count() == 1);

    }

    public function testDeleteComment(){
        $this->login("test", "test");
        $crawler = $this->client->request('DELETE', '/comment/delete/34670615');
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertTrue($crawler->filter('html:contains("Succes deleting comment")')->count() == 1);
    }
}