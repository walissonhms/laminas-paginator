<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator;

use Interop\Container\ContainerInterface;
use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\Paginator\AdapterPluginManager;
use Laminas\Paginator\AdapterPluginManagerFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;

class AdapterPluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManager()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(false);
        $container->get('config')->shouldNotBeCalled();

        $factory = new AdapterPluginManagerFactory();

        $adapters = $factory($container->reveal(), AdapterPluginManager::class);
        $this->assertInstanceOf(AdapterPluginManager::class, $adapters);
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(false);
        $container->get('config')->shouldNotBeCalled();

        $adapter = $this->prophesize(AdapterInterface::class)->reveal();

        $factory = new AdapterPluginManagerFactory();
        $adapters = $factory($container->reveal(), AdapterPluginManager::class, [
            'services' => [
                'test' => $adapter,
            ],
        ]);
        $this->assertSame($adapter, $adapters->get('test'));
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2()
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);
        $container->has('config')->willReturn(false);
        $container->get('config')->shouldNotBeCalled();

        $adapter = $this->prophesize(AdapterInterface::class)->reveal();

        $factory = new AdapterPluginManagerFactory();
        $factory->setCreationOptions([
            'services' => [
                'test' => $adapter,
            ],
        ]);

        $adapters = $factory->createService($container->reveal());
        $this->assertSame($adapter, $adapters->get('test'));
    }

    public function testDoesNotConfigureAdditionalPaginatorsWhenConfigServiceDoesNotContainPaginatorsConfig()
    {
        $container = $this->createMock(ContainerInterface::class);

        $container
            ->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(true);

        $container
            ->expects($this->once())
            ->method('get')
            ->with('config')
            ->willReturn(['foo' => 'bar']);

        $factory = new AdapterPluginManagerFactory();
        $adapters = $factory($container, AdapterPluginManager::class);

        $this->assertInstanceOf(AdapterPluginManager::class, $adapters);
        $this->assertFalse($adapters->has('foo'));
    }

    public function testConfiguresPaginatorServicesWhenFound()
    {
        $paginator = $this->createMock(AdapterInterface::class);
        $config = [
            'paginators' => [
                'aliases' => [
                    'test' => 'test-too',
                ],
                'factories' => [
                    'test-too' => function ($container) use ($paginator) {
                        return $paginator;
                    },
                ],
            ],
        ];

        $container = $this->createMock(ContainerInterface::class);

        $container
            ->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(true);

        $container
            ->expects($this->once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new AdapterPluginManagerFactory();
        $paginators = $factory($container, AdapterPluginManager::class);

        $this->assertInstanceOf(AdapterPluginManager::class, $paginators);
        $this->assertTrue($paginators->has('test'));
        $this->assertSame($paginator, $paginators->get('test'));
        $this->assertTrue($paginators->has('test-too'));
        $this->assertSame($paginator, $paginators->get('test-too'));
    }
}
