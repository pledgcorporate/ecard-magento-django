<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit278ec17a5909caa6b8a7d5db803b9eb4
{
    public static $files = array (
        'e2ce2e06fa11fa8b2405a9d5aee0228e' => __DIR__ . '/../..' . '/registration.php',
    );

    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Pledg\\PledgPaymentGateway\\' => 26,
        ),
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Pledg\\PledgPaymentGateway\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit278ec17a5909caa6b8a7d5db803b9eb4::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit278ec17a5909caa6b8a7d5db803b9eb4::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit278ec17a5909caa6b8a7d5db803b9eb4::$classMap;

        }, null, ClassLoader::class);
    }
}
