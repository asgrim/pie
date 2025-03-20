<?php

declare(strict_types=1);

namespace Php\Pie\SelfManage\Verify;

use Composer\Util\AuthHelper;
use Composer\Util\HttpDownloader;
use Php\Pie\File\BinaryFile;
use Php\Pie\SelfManage\Update\ReleaseMetadata;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ExecutableFinder;

/** @internal This is not public API for PIE, so should not be depended upon unless you accept the risk of BC breaks */
final class GithubCliAttestationVerification implements VerifyPiePhar
{
    private const GH_CLI_NAME = 'gh';

    public function __construct(
        private readonly string $githubApiBaseUrl,
        private readonly HttpDownloader $httpDownloader,
        private readonly AuthHelper $authHelper,
        private readonly ExecutableFinder $executableFinder,
    ) {
    }

    public function verify(ReleaseMetadata $releaseMetadata, BinaryFile $pharFilename, OutputInterface $output): void
    {
        // @todo verify using `gh attestation verify` etc
    }
}
