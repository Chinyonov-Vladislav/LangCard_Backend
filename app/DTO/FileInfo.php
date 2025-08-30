<?php

namespace App\DTO;

class FileInfo
{
    private string $path;
    private string $type;
    private string $extension;
    private int $size;
    public function __construct(string $path, string $type, string $extension, int $size)
    {
        $this->path = $path;
        $this->type = $type;
        $this->extension = $extension;
        $this->size = $size;
    }
    public function getPath(): string
    {
        return $this->path;
    }
    public function getType(): string
    {
        return $this->type;
    }
    public function getExtension(): string
    {
        return $this->extension;
    }
    public function getSize(): int
    {
        return $this->size;
    }
}
