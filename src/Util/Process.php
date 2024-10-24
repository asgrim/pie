<?php

declare(strict_types=1);

namespace Php\Pie\Util;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process as SymfonyProcess;
use Symfony\Component\Console\Output\OutputInterface;

use function trim;

final class Process
{
    /** @psalm-suppress UnusedConstructor */
    private function __construct()
    {
    }

    /**
     * Just a helper to invoke a Symfony Process command with a simplified API
     * for the common invocations we have in PIE.
     *
     * Things to note:
     *  - uses mustRun (i.e. throws exception if command execution fails)
     *  - very short timeout by default (5 seconds)
     *  - output is trimmed
     *
     * @param list<string> $command
     *
     * @throws ProcessFailedException
     */
    public static function run(array $command, string|null $workingDirectory = null, int|null $timeout = 5, ?OutputInterface $output = null): string
    {
        if ($output instanceof OutputInterface) {
            $process = new SymfonyProcess($command, $workingDirectory, timeout: $timeout);
            $process->start();

            $iterator = $process->getIterator();
            foreach ($iterator as $data) {
                $output->write($data);
            }
			// executes after the command finishes
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            return '';
        }
        return trim((new SymfonyProcess($command, $workingDirectory, timeout: $timeout))
            ->mustRun()
            ->getOutput());
    }
}
