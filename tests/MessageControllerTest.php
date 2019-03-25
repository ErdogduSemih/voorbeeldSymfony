<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class MessageControllerTest extends WebTestCase
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

    public function provideSearchAction()
    {
        return Array(
            array('test'),
            array('this is about a')
        );
    }

    //kiryl
    public function testMessagePage()
    {
        $this->login("test", "test");
        $crawler = $this->client->request('GET', '/message');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertTrue($crawler->filter('html:contains("Messages")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("New Message")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("Add category")')->count() == 1);
    }

    //kiryl
    public function testAddNewMessagePage()
    {
        $this->login("test", "test");
        $crawler = $this->client->request('GET', '/message/new');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertTrue($crawler->filter('html:contains("Content")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("Category")')->count() == 1);
    }

    //kiryl
    public function testAddNewCategoryPage()
    {
        $this->login("mod", "mod");
        $crawler = $this->client->request('GET', '/category/new');

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertTrue($crawler->filter('html:contains("Name")')->count() == 1);
    }

    //kiryl
    public function testPressNewMessageButton()
    {

        $this->login("test", "test");
        $crawler = $this->client->request('GET', '/message');
        $link = $crawler
            ->filter('a:contains("New Message")')// find all links with the text "Greet"
            ->eq(0)// select the second link in the list
            ->link();

        $crawler = $this->client->click($link);

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertTrue($crawler->filter('html:contains("Content")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("Category")')->count() == 1);
    }

    public function testSearchAction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $form = $crawler->selectButton('searchButton')->form();
        $form['search'] = 'test';
        $crawler = $client->submit($form);

        $this->assertGreaterThan(0, $crawler->filter('tr')->count());
    }

    //kiryl
    public function testCreateNewMessage()
    {

        $this->login("test", "test");
        $crawler = $this->client->request('GET', '/message/new');

        $testMessage = "Dogs are awesome";
        $testCategory = "Jokes";

        $form = $crawler->selectButton('Create')->form();
        $form['form[content]'] = $testMessage;
        $form['form[category]'] = $testCategory;

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("' . $testMessage . '")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("' . $testCategory . '")')->count() == 1);
    }

    //kiryl
    public function testEditMessage(){
        $this->login("poster", "poster");
        $crawler = $this->client->request('GET', '/message/edit/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("Content")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("Save")')->count() == 1);

    }

    public function testCreateComment()
    {
        $text = 'Semih is geniaal';
        $this->login("poster", "poster");
        $crawler = $this->client->request('POST', '/message/1/comment/new', array('content' => $text));
        $crawler = $this->client->request('GET', '/message/1/comments');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('html:contains("' . $text . '")')->count() == 1);

    }
        //semih
        public function testOnMessageCommentsContainsButton()
        {
            $this->login("test", "test");
            $crawler = $this->client->request('GET', '/message/1/comments');
            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
            $this->assertTrue($crawler->filter('html:contains("Content")')->count() == 1);

        }
//semih
        public function testOnMessageCommentsDoesNotContainsAnyComment()
        {
            $this->login("test", "test");
            $crawler = $this->client->request('GET', '/message/3/comments');
            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
            $this->assertTrue($crawler->filter('html:contains("No comments found. Add a new comment please!")')->count() == 1);

        }

        public function testOnMessagePageSaysHallo()
        {
            $this->login("test", "test");
            $crawler = $this->client->request('GET', 'Hallo poster! Hier zie je een overzicht van je persoonlijke berichten.');
            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
            $this->assertTrue($crawler->filter('html:contains("Hallo poster! Hier zie je een overzicht van je persoonlijke berichten.")')->count() == 1);

        }

}
