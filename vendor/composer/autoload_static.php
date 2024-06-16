<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc90f13d90f71eb0b8f30f5eb692c9eee
{
    public static $files = array (
        '3e948ea0014beada2f32e5176b30afe9' => __DIR__ . '/../..' . '/includes/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'N' => 
        array (
            'NhrrobCoreContributions\\' => 24,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'NhrrobCoreContributions\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInitc90f13d90f71eb0b8f30f5eb692c9eee::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc90f13d90f71eb0b8f30f5eb692c9eee::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitc90f13d90f71eb0b8f30f5eb692c9eee::$classMap;

        }, null, ClassLoader::class);
    }
}
