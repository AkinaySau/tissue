<?php

namespace CL\Tissue\Adapter;

use CL\Tissue\Model\Detection;

/**
 * NullAdapter for disabled virus scanner.
 */
class NullAdapter extends AbstractAdapter
{
    /**
     * NullAdapter constructor.
     */
    public function __construct()
    {
        $this->enabled = false;
    }

    /**
     * @param string $path
     *
     * @return Detection|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function detect(string $path): ?Detection
    {
        return null;
    }
}
