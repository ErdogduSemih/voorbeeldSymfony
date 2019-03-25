<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;




class UserControllerTest extends WebTestCase
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

    //kiryl
    public function testUserList()
    {

        $this->login("test","test");
        $crawler = $this->client->request('GET', '/user');

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->assertTrue($crawler->filter('html:contains("Users overview")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("Hier zie je een overzicht van de users")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("New User")')->count() == 1);
    }

    //kiryl
    public function testLoginAdmin()
    {
        $admin = "test";
        $adminPass = "test";
        $this->logIn($admin,$adminPass);

        $crawler = $this->client->request('GET', '/');

        $this->assertTrue($crawler->filter('html:contains("Welcome "' . $admin . ')')->count() == 1);
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    //kiryl
    public function testAddNewUserPage()
    {
        $this->login("test", "test");
        $crawler = $this->client->request('GET', '/user/new');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertTrue($crawler->filter('html:contains("User name")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("Password")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("Roles string")')->count() == 1);
    }


    public function testEditUser(){

        $this->login("test", "test");
        $crawler = $this->client->request('GET', '/user/edit/11');
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertTrue($crawler->filter('html:contains("User name")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("Password")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("Roles string")')->count() == 1);
    }

}
