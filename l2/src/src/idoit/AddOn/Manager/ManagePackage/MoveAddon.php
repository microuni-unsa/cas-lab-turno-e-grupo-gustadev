<?php

namespace idoit\AddOn\Manager\ManagePackage;

use idoit\AddOn\Manager\Exception\PackageException;
use idoit\AddOn\Manager\ManageActionInterface;
use Symfony\Component\Filesystem\Filesystem;

class MoveAddon implements ManageActionInterface
{
    /**
     * @var string
     */
    private string $sourceFolder;

    /**
     * @var string
     */
    private string $targetFolder;

    /**
     * @param string $sourceFolder
     * @param string $targetFolder
     */
    public function __construct(string $sourceFolder, string $targetFolder)
    {
        $this->sourceFolder = $sourceFolder;
        $this->targetFolder = $targetFolder;
    }

    /**
     * @return bool
     * @throws PackageException
     */
    public function process(): bool
    {
        if (!file_exists($this->sourceFolder)) {
            throw PackageException::noPackageFound();
        }

        $filesystem = new Filesystem();
        // Move add-on specific files.
        $filesystem->mirror($this->sourceFolder, $this->targetFolder);

        // Afterwards, delete the folder from temp.
        $filesystem->remove($this->sourceFolder);

        return true;
    }
}
