<?php

namespace App\Entity;

use App\Repository\FeedSourceRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as AppAssert;
use App\Attribute\Upload;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FeedSourceRepository::class)]
#[ORM\Table(name: 'koi_feed_source')]
class FeedSource
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    // The feed URL
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $url = null;

    // Optional: A name for the source (if desired, otherwise it can be omitted)
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $name = null;

    #[Upload(pathProperty: 'image', deleteProperty: 'deleteImage', maxWidth: 200, maxHeight: 200)]
    #[Assert\Image(mimeTypes: ['image/png', 'image/jpeg', 'image/webp', 'image/avif'], groups: ['colletion:image'])]
    #[AppAssert\HasEnoughSpaceForUpload]
    #[Groups(['collection:write', 'collection:image'])]
    private ?File $file = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['collection:read'])]
    private ?string $image = null;

    #[Groups(['collection:write'])]
    private ?bool $deleteImage = null;

    // Getters and setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getName(): ?string
    {
        // If no name has been defined, the URL can be used as identifier.
        return $this->name ?? $this->url;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getDeleteImage(): ?bool
    {
        return $this->deleteImage;
    }

    public function setDeleteImage(?bool $deleteImage): self
    {
        $this->deleteImage = $deleteImage;

        return $this;
    }
}
