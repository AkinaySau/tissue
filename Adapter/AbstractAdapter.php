<?php

/*
 * This file is part of the Tissue library.
 *
 * (c) Cas Leentfaar <info@casleentfaar.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CL\Tissue\Adapter;

use CL\Tissue\Exception\AdapterException;
use CL\Tissue\Model\Detection;
use CL\Tissue\Model\ScanResult;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var OptionsResolver|null
     */
    private $resolver;

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function scan(array $paths, array $options = []): ScanResult
    {
        $this->options = $this->resolveOptions($options);

        try {
            return $this->scanArray($paths);
        } catch (AdapterException $e) {
            // TODO: log virus scanner exception
            return new ScanResult(); // silently fail
        }
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param array $paths
     *
     * @return ScanResult
     *
     * @throws \InvalidArgumentException
     */
    protected function scanArray(array $paths): ScanResult
    {
        $files = [];
        $detections = [];

        foreach ($paths as $path) {
            $result = $this->scanSingle($path);
            $paths = array_merge($paths, $result->getPaths());
            $files = array_merge($files, $result->getFiles());
            $detections = array_merge($detections, $result->getDetections());
        }

        return new ScanResult($paths, $files, $detections);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function resolveOptions(array $options): array
    {
        if (null === $this->resolver) {
            $this->resolver = new OptionsResolver();
            $this->configureOptions($this->resolver);
        }

        return $this->resolver->resolve($options);
    }

    /**
     * @param string $path
     *
     * @return ScanResult
     *
     * @throws \InvalidArgumentException
     */
    protected function scanSingle(string $path): ?ScanResult
    {
        $files = [];
        $detections = [];
        $path = realpath($path);

        if (!$path) {
            throw new \InvalidArgumentException(sprintf('File to scan does not exist: %s', $path));
        }

        if (is_dir($path)) {
            //$paths = [$path];

            return $this->scanDir($path);
        }

        $files[] = $path;
        if ($detection = $this->detect($path)) {
            $detections[] = $detection;
        }

        return new ScanResult([$path], $files, $detections);
    }

    /**
     * @param string $dir
     *
     * @return ScanResult
     */
    protected function scanDir(string $dir): ScanResult
    {
        $fileInfo = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        $files = [];
        foreach ($fileInfo as $pathname => $item) {
            if (!$item->isFile()) {
                continue;
            }

            $files[] = $pathname;
        }

        return $this->scanArray($files);
    }

    /**
     * @param string      $path
     * @param int         $type
     * @param string|null $description
     *
     * @return Detection
     */
    protected function createDetection(string $path, $type = Detection::TYPE_VIRUS, $description = null): Detection
    {
        return new Detection($path, $type, $description);
    }

    /**
     * Creates a new process that you might use to interact with your virus-scanner's executable.
     *
     * @param array    $command The command to run and its arguments listed as separate entries
     * @param int|null $timeout An optional number of seconds for the process' timeout limit
     *
     * @return Process A new process
     *
     * @codeCoverageIgnore
     */
    protected function createProcess(array $command, $timeout = null): Process
    {
        $process = new Process($command);

        if (null !== $timeout) {
            $process->setTimeout($timeout);
        }

        return $process;
    }

    /**
     * Creates a new process builder that you might use to interact with your virus-scanner's executable.
     *
     * @deprecated
     *
     * @param array    $arguments An optional array of arguments
     * @param int|null $timeout   An optional number of seconds for the process' timeout limit
     *
     * @return ProcessBuilder A new process builder
     *
     * @codeCoverageIgnore
     */
    protected function createProcessBuilder(array $arguments = [], $timeout = null): ProcessBuilder
    {
        $pb = new ProcessBuilder($arguments);
        if (null !== $timeout) {
            $pb->setTimeout($timeout);
        }

        return $pb;
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
    }

    /**
     * @param string $path
     *
     * @return Detection|null
     */
    abstract protected function detect(string $path): ?Detection;
}
