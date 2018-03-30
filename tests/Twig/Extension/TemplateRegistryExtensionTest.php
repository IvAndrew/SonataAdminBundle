<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\AdminBundle\Twig\Extension\TemplateRegistryExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Twig\TwigFunction;

/**
 * Class TemplateRegistryExtensionTest.
 */
class TemplateRegistryExtensionTest extends TestCase
{
    /**
     * @var TemplateRegistryExtension
     */
    private $extension;

    /**
     * @var TemplateRegistryInterface
     */
    private $templateRegistry;

    /**
     * @var ContainerInterface
     */
    private $container;

    private $admin;

    protected function setUp()
    {
        $this->templateRegistry = $this->prophesize(TemplateRegistryInterface::class);
        $this->container = $this->prophesize(ContainerInterface::class);

        $this->templateRegistry->getTemplate('edit')->willReturn('@SonataAdmin/CRUD/edit.html.twig');

        $this->extension = new TemplateRegistryExtension(
            $this->templateRegistry->reveal(),
            $this->container->reveal()
        );
    }

    public function getFunctionsTest()
    {
        $expected = [
            new TwigFunction('get_admin_template', [$this->extension, 'getAdminTemplate']),
            new TwigFunction('get_global_template', [$this->extension, 'getGlobalTemplate']),

            // NEXT MAJOR: Remove this line
            new TwigFunction('get_admin_pool_template', [$this->extension, 'getGlobalTemplate'], ['deprecated' => true]),
        ];

        $this->assertEquals($expected, $this->extension->getFunctions());
    }

    public function testGetAdminTemplate()
    {
        $this->container->get('admin.post.template_registry')->willReturn($this->templateRegistry);

        $this->assertEquals(
            '@SonataAdmin/CRUD/edit.html.twig',
            $this->extension->getAdminTemplate('edit', 'admin.post')
        );
    }

    public function testGetAdminTemplateFailure()
    {
        $this->container->get('admin.post.template_registry')->willReturn(null);

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage('You have requested a non-existent service "admin.post.template_registry"');

        $this->assertEquals(
            '@SonataAdmin/CRUD/edit.html.twig',
            $this->extension->getAdminTemplate('edit', 'admin.post')
        );
    }

    public function testGetGlobalTemplate()
    {
        $this->assertEquals(
            '@SonataAdmin/CRUD/edit.html.twig',
            $this->extension->getGlobalTemplate('edit')
        );
    }
}