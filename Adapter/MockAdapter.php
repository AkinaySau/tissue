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

use CL\Tissue\Model\Detection;

/**
 * MockAdapter for testing purposes.
 *
 * NOTE: this adapter does a really lame job at detecting infections
 * It should only be used for simple functional tests.
 */
class MockAdapter extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    protected function detect(string $path): ?Detection
    {
        $content = file_get_contents($path);
        foreach (['infect', 'virus', 'eicar'] as $mockedInfection) {
            if (false !== stripos($path, $mockedInfection) || false !== stripos($content, $mockedInfection)) {
                return $this->createDetection($path);
            }
        }

        return null;
    }
}
