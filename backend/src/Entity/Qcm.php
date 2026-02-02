<?php

namespace App\Entity;

use App\Repository\QcmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: QcmRepository::class)]
class Qcm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['qcm:list', 'qcm:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['qcm:list', 'qcm:read'])]
    private string $title;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['qcm:read'])]
    private ?string $sourcePdfName = null;

    #[ORM\Column]
    #[Groups(['qcm:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(
        mappedBy: 'qcm',
        targetEntity: Question::class,
        orphanRemoval: true,
        cascade: ['persist']
    )]
    #[Groups(['qcm:read'])]
    private Collection $questions;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getSourcePdfName(): ?string
    {
        return $this->sourcePdfName;
    }

    public function setSourcePdfName(?string $sourcePdfName): static
    {
        $this->sourcePdfName = $sourcePdfName;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setQcm($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        $this->questions->removeElement($question);
        // orphanRemoval = true → suppression automatique
        return $this;
    }
}
