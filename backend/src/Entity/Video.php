<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
#[Vich\Uploadable]
class Video
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $teacherFirstName;

    #[ORM\Column(length: 100)]
    private string $teacherLastName;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    /**
     * 🔥 NOM DU FICHIER EN BASE
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $videoName = null;

    /**
     * 🔥 FICHIER TEMPORAIRE (PAS EN BASE)
     */
    #[Vich\UploadableField(mapping: 'videos', fileNameProperty: 'videoName')]
    private ?File $videoFile = null;

    /**
     * 🔥 OBLIGATOIRE POUR VICH
     */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /* ==================== GETTERS / SETTERS ==================== */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getVideoName(): ?string
    {
        return $this->videoName;
    }

    public function setVideoName(?string $videoName): static
    {
        $this->videoName = $videoName;
        return $this;
    }

    /**
     * 👉 GETTER OBLIGATOIRE POUR LE FORM
     */
    public function getVideoFile(): ?File
    {
        return $this->videoFile;
    }

    /**
     * 👉 SETTER OBLIGATOIRE POUR VICH
     */
    public function setVideoFile(?File $videoFile = null): void
    {
        $this->videoFile = $videoFile;

        if ($videoFile !== null) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getTeacherFirstName(): string
    {
        return $this->teacherFirstName;
    }

    public function setTeacherFirstName(string $teacherFirstName): self
    {
        $this->teacherFirstName = $teacherFirstName;
        return $this;
    }

    public function getTeacherLastName(): string
    {
        return $this->teacherLastName;
    }

    public function setTeacherLastName(string $teacherLastName): self
    {
        $this->teacherLastName = $teacherLastName;
        return $this;
    }
}
