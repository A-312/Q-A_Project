<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Routing\Tests\Loader;

use Symfony\Config\FileLocator;
use Symfony\Routing\Loader\PhpFileLoader;

class PhpFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $loader = new PhpFileLoader($this->getMock('Symfony\Config\FileLocator'));

        $this->assertTrue($loader->supports('foo.php'), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');

        $this->assertTrue($loader->supports('foo.php', 'php'), '->supports() checks the resource type if specified');
        $this->assertFalse($loader->supports('foo.php', 'foo'), '->supports() checks the resource type if specified');
    }

    public function testLoadWithRoute()
    {
        $loader = new PhpFileLoader(new FileLocator(array(__DIR__.'/../Fixtures')));
        $routeCollection = $loader->load('validpattern.php');
        $routes = $routeCollection->all();

        $this->assertCount(2, $routes, 'Two routes are loaded');
        $this->assertContainsOnly('Symfony\Routing\Route', $routes);

        foreach ($routes as $route) {
            $this->assertSame('/blog/{slug}', $route->getPath());
            $this->assertSame('MyBlogBundle:Blog:show', $route->getDefault('_controller'));
            $this->assertSame('{locale}.example.com', $route->getHost());
            $this->assertSame('RouteCompiler', $route->getOption('compiler_class'));
            $this->assertEquals(array('GET', 'POST', 'PUT', 'OPTIONS'), $route->getMethods());
            $this->assertEquals(array('https'), $route->getSchemes());
        }
    }
}
