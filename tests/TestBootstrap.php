<?php declare(strict_types=1);

use Shopware\Core\TestBootstrapper;

// Shopware ana dizinini Dockware'e göre zorunlu olarak belirtiyoruz
$_SERVER['PROJECT_ROOT'] = '/var/www/html';

$loader = (new TestBootstrapper())
    ->setProjectDir($_SERVER['PROJECT_ROOT'])
    ->addCallingPlugin() // addActivePlugins'i kaldırdık, bu metod eklentiyi otomatik bulur
    ->setForceInstallPlugins(true)
    ->bootstrap()
    ->getClassLoader();

// KRİTİK NOKTA: Buradaki namespace composer.json'daki autoload-dev ile BİREBİR aynı olmalıdır!
$loader->addPsr4('SanalposproPayment\\Tests\\', __DIR__);