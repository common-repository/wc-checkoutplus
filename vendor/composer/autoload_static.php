<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1e1e32fc31269429e549a8b8acf09d1a
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
            'CheckoutPlus\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
        ),
        'CheckoutPlus\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1e1e32fc31269429e549a8b8acf09d1a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1e1e32fc31269429e549a8b8acf09d1a::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit1e1e32fc31269429e549a8b8acf09d1a::$classMap;

        }, null, ClassLoader::class);
    }
}
