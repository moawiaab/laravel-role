<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb262a809c09cf0f653aa95d0f2fd4c63
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Moawiaab\\Role\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Moawiaab\\Role\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb262a809c09cf0f653aa95d0f2fd4c63::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb262a809c09cf0f653aa95d0f2fd4c63::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb262a809c09cf0f653aa95d0f2fd4c63::$classMap;

        }, null, ClassLoader::class);
    }
}
