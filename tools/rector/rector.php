<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\EarlyReturn\Rector\StmtsAwareInterface\ReturnEarlyIfVariableRector;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\Php74\Rector\Assign\NullCoalescingOperatorRector;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\AnnotationWithValueToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DataProviderAnnotationToAttributeRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

$projectRoot = isset($_SERVER['CI']) ? $_SERVER['GITHUB_WORKSPACE'] : '';
$appPathPrefix = "{$projectRoot}/app";

$paths = ["{$appPathPrefix}/src"];
if (file_exists("{$appPathPrefix}/tests")) {
    $paths[] = "{$appPathPrefix}/tests";
}

return RectorConfig::configure()
    ->withCache('/var/tmp/rector')
    ->withPaths($paths)
    ->withRootFiles()
    ->withPhpSets(php83: true)
    ->withAttributesSets(
        symfony: true,
        doctrine: true,
        gedmo: true,
        phpunit: true,
    )
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        naming: true,
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: true,
        carbon: true,
        rectorPreset: true,
    )
    ->withImportNames(
        importShortClasses: false,
        removeUnusedImports: true
    )
    ->withSets([
        SymfonySetList::SYMFONY_64,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        DoctrineSetList::DOCTRINE_ORM_214,
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
        DoctrineSetList::DOCTRINE_COMMON_20,
        DoctrineSetList::DOCTRINE_DBAL_40,
        PHPUnitSetList::PHPUNIT_90,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
//        PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
        LevelSetList::UP_TO_PHP_83,
    ])
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
    ])
    ->withSkip([
        EncapsedStringsToSprintfRector::class,
        RenamePropertyToMatchTypeRector::class,
        NullCoalescingOperatorRector::class,
        RenameParamToMatchTypeRector::class,
        RenameVariableToMatchNewTypeRector::class,
        ReturnEarlyIfVariableRector::class,
        AnnotationToAttributeRector::class,
        DataProviderAnnotationToAttributeRector::class,
        AnnotationWithValueToAttributeRector::class,
        RenameVariableToMatchMethodCallReturnTypeRector::class,
    ]);
