<?php

declare(strict_types=1);

namespace Php\Pie\DependencyResolver;

use Composer\Composer;
use Composer\Filter\PlatformRequirementFilter\PlatformRequirementFilterFactory;
use Composer\Installer;
use Composer\IO\IOInterface;
use Composer\Package\CompletePackageInterface;
use Composer\Package\Link;
use Composer\Package\Version\VersionSelector;
use Composer\Repository\RepositorySet;
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\VersionParser;
use Php\Pie\TargetPhp\PhpBinaryPath;
use Php\Pie\TargetPhp\ResolveTargetPhpToPlatformRepository;

use function in_array;

/** @internal This is not public API for PIE, so should not be depended upon unless you accept the risk of BC breaks */
final class ResolveDependencyWithComposer implements DependencyResolver
{
    public function __construct(
        private readonly IOInterface $io,
        private readonly Composer $composer,
        private readonly RepositorySet $repositorySet,
        private readonly ResolveTargetPhpToPlatformRepository $resolveTargetPhpToPlatformRepository,
    ) {
    }

    public function __invoke(PhpBinaryPath $phpBinaryPath, string $packageName, string|null $requestedVersion): Package
    {
        // @todo can we change this per installation? might be nice to segment (e.g. `~/.pie/<package>/*` instead of `~/.pie/*`
//        $this->composer->getConfig()->merge([
//            'config' => ['vendor-dir' => __DIR__ . '/../../COMPOSER_VENDOR1'],
//            'vendor-dir' => __DIR__ . '/../../COMPOSER_VENDOR1',
//        ]);
//        $this->io->loadConfiguration($this->composer->getConfig());

        $rootPackage = $this->composer->getPackage();

        $versionSelector = (new VersionSelector(
            $this->repositorySet,
            ($this->resolveTargetPhpToPlatformRepository)($phpBinaryPath),
        ));
        $package = $versionSelector->findBestCandidate($packageName, $requestedVersion);
        $recommendedRequireVersion = $versionSelector->findRecommendedRequireVersion($package);
        $constraint = (new VersionParser())->parseConstraints($recommendedRequireVersion);

        $rootPackage->setRequires([
            $packageName => new Link('a', $packageName, $constraint, Link::TYPE_REQUIRE, $recommendedRequireVersion),
        ]);

        $install = Installer::create($this->io, $this->composer);
        $install
            ->setAllowedTypes(['php-ext', 'php-ext-zend'])
            ->setIgnoredTypes([])
            ->setDryRun(false)
            ->setDownloadOnly(false)
            ->setVerbose(false)
//            ->setPreferSource($preferSource)
//            ->setPreferDist($preferDist)
            ->setDevMode(false)
            ->setDumpAutoloader(false)
            ->setOptimizeAutoloader(false)
            ->setClassMapAuthoritative(false)
            ->setPlatformRequirementFilter(PlatformRequirementFilterFactory::ignoreAll())
//            ->setApcuAutoloader($apcu, $apcuPrefix)
//            ->setPlatformRequirementFilter($this->getPlatformRequirementFilter($input))
//            ->setAudit($input->getOption('audit'))
//            ->setErrorOnAudit($input->getOption('audit'))
//            ->setAuditFormat($this->getAuditFormat($input))
        ;

        $install->run();

        // @todo ok, now install has run, and it put the source in `~/.pie/asgrim/example-pie-extension`... how do I get the path?

        $r = $this->composer->getRepositoryManager()->getLocalRepository();
        $pp = $r->findPackages($packageName);

        echo "hi";
        //then feed stuff to an Installer and run it (see InstallCommand)

        // override: Factory::createDefaultInstallers & addLocalRepository

//        if (! $package instanceof CompletePackageInterface) {
//            throw UnableToResolveRequirement::fromRequirement($packageName, $requestedVersion);
//        }
//
//        $type = $package->getType();
//        if (! in_array($type, [Package::TYPE_PHP_MODULE, Package::TYPE_ZEND_EXTENSION])) {
//            throw UnableToResolveRequirement::toPhpOrZendExtension($package, $packageName, $requestedVersion);
//        }
//
//        return Package::fromComposerCompletePackage($package);
    }
}
