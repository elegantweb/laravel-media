<?php

namespace Elegant\Media\Contracts;

use Elegant\Media\Media;
use Elegant\Media\MediaManipulation;
use Elegant\Media\MediaGroup;
use Elegant\Media\FileAdder;
use Elegant\Media\RemoteFile;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface HasMedia
{
    public function media(): Relation;

    public function addMedia(File|UploadedFile|RemoteFile $file): FileAdder;

    public function addMediaManipulation(string $name): MediaManipulation;
    public function getMediaManipulation(string $name): ?MediaManipulation;

    public function addMediaGroup(string $name): MediaGroup;
    public function getMediaGroup(string $name): ?MediaGroup;

    public function getFallbackMediaUrl(string $group = 'default', string $manipulation = null): ?string;
    public function getFallbackMediaPath(string $group = 'default', string $manipulation = null): ?string;

    public function hasMedia(string $group = 'default'): bool;
    public function getMedia(string $group = 'default'): Collection;

    public function getFirstMedia(string $group = 'default'): ?Media;
    public function getFirstMediaUrl(string $group = 'default', string $manipulation = null): ?string;
    public function getFirstMediaPath(string $group = 'default', string $manipulation = null): ?string;
}
