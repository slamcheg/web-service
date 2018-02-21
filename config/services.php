<?php
/**
 * @var \Symfony\Component\DependencyInjection\ContainerBuilder $container
 * @var \Symfony\Component\DependencyInjection\Loader\PhpFileLoader $this
 */

$container->register('requestStack', \Symfony\Component\HttpFoundation\RequestStack::class);
$container->setAlias(\Symfony\Component\HttpFoundation\RequestStack::class, 'requestStack');


$container->register('default', \Proxy\Service\Controllers\DefaultController::class)
    ->addMethodCall('setRequestStack',[new \Symfony\Component\DependencyInjection\Reference('requestStack')]);
$container->setAlias(\Proxy\Service\Controllers\DefaultController::class, 'default');

