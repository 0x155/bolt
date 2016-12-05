<?php

namespace Bolt\Tests\Twig;

use Bolt\EventListener\ConfigListener;
use Bolt\Tests\BoltUnitTest;
use Bolt\Twig\SetcontentTokenParser;
use Bolt\Twig\SwitchTokenParser;
use Bolt\Twig\Extension\BoltExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class to test src/Twig/Extension/BoltExtension.
 *
 * @author Ross Riley <riley.ross@gmail.com>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class BoltExtensionTest extends BoltUnitTest
{
    public function testTwigInterface()
    {
        $twig = new BoltExtension(false);
        $this->assertGreaterThan(0, $twig->getFunctions());
        $this->assertGreaterThan(0, $twig->getFilters());
        $this->assertGreaterThan(0, $twig->getTests());
        $this->assertEquals('Bolt', $twig->getName());
    }

    public function testGetGlobals()
    {
        $app = $this->getApp();
        $request = Request::createFromGlobals();
        $app['request'] = $request;
        $app['request_stack']->push($request);

        // Call the event listener that adds the globals
        $event = new GetResponseEvent($app['kernel'], $request, HttpKernelInterface::MASTER_REQUEST);
        (new ConfigListener($app))->onRequest($event);

        $response = $app['twig']->getGlobals();
        $this->assertArrayHasKey('bolt_name', $response);
        $this->assertArrayHasKey('bolt_version', $response);
        $this->assertArrayHasKey('bolt_stable', $response);
        $this->assertArrayHasKey('frontend', $response);
        $this->assertArrayHasKey('backend', $response);
        $this->assertArrayHasKey('async', $response);
        $this->assertArrayHasKey('paths', $response);
        $this->assertArrayHasKey('theme', $response);
        $this->assertArrayHasKey('user', $response);
        $this->assertArrayHasKey('users', $response);
        $this->assertArrayHasKey('config', $response);
        $this->assertNotNull($response['config']);
    }

    public function testGetGlobalsSafe()
    {
        $app = $this->getApp();
        $request = Request::createFromGlobals();
        $app['request'] = $request;
        $app['request_stack']->push($request);
        $twig = new BoltExtension(true);

        $result = $twig->getGlobals();
        $this->assertArrayHasKey('config', $result);
        $this->assertNull($result['users']);
    }

    public function testGetGlobalsExceptionalExceptionIsExceptional()
    {
        $app = $this->getApp();

        $users = $this->getMock('Bolt\Users', ['getCurrentUser'], [$app]);
        $users
            ->expects($this->atLeastOnce())
            ->method('getCurrentUser')
            ->will($this->throwException(new \Exception()));
        $app['users'] = $users;
        $request = Request::createFromGlobals();
        $app['request'] = $request;
        $app['request_stack']->push($request);

        // Call the event listener that adds the globals
        $event = new GetResponseEvent($app['kernel'], $request, HttpKernelInterface::MASTER_REQUEST);
        (new ConfigListener($app))->onRequest($event);

        $result = $app['twig']->getGlobals();
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('users', $result);
        $this->assertNull($result['user']);
        $this->assertNull($result['users']);
    }

    public function testGetTokenParsers()
    {
        $twig = new BoltExtension(false);

        $result = $twig->getTokenParsers();
        $this->assertInstanceOf(SetcontentTokenParser::class, $result[0]);
        $this->assertInstanceOf(SwitchTokenParser::class, $result[1]);
    }
}
