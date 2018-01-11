<?php

/*
 * This file is part of the Tissue library.
 *
 * (c) Cas Leentfaar <info@casleentfaar.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CL\Tissue\Model;

class Detection
{
    public const TYPE_VIRUS = 1;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $description;

    /**
     * @param string      $path
     * @param int         $type
     * @param string|null $description
     */
    public function __construct($path, $type = self::TYPE_VIRUS, $description = null)
    {
        $this->path = $path;
        $this->type = $type;
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }
}
