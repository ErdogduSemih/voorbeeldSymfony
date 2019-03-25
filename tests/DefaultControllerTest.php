<?php
namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DefaultControllerTest extends WebTestCase
{
    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    //kiryl
    public function testShowIndex()
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("Welcome Anonymous")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("Message")')->count() == 1);

    }

    //kiryl
    public function testShowLogin()
    {
        $crawler = $this->client->request('GET', '/login');

        $this->assertTrue($crawler->filter('html:contains("Login")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("Username")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("Password")')->count() == 1);
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }


}
