<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BookRepository::class)
 */
class Book
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nameBook;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $authorBook;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $coverPct;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileBook;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateRead;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="books")
     */
    private $Users;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameBook(): ?string
    {
        return $this->nameBook;
    }

    public function setNameBook(string $nameBook): self
    {
        $this->nameBook = $nameBook;

        return $this;
    }

    public function getAuthorBook(): ?string
    {
        return $this->authorBook;
    }

    public function setAuthorBook(string $authorBook): self
    {
        $this->authorBook = $authorBook;

        return $this;
    }

    public function getCoverPct(): ?string
    {
        return $this->coverPct;
    }

    public function setCoverPct(string $coverPct): self
    {
        $this->coverPct = $coverPct;

        return $this;
    }

    public function getFileBook(): ?string
    {
        return $this->fileBook;
    }

    public function setFileBook(string $fileBook): self
    {
        $this->fileBook = $fileBook;

        return $this;
    }

    public function getDateRead(): ?\DateTimeInterface
    {
        return $this->dateRead;
    }

    public function setDateRead(): self
    {
        $this->dateRead = new \DateTime();
 
        return $this;
    }

    public function getUsers(): ?User
    {
        return $this->Users;
    }

    public function setUsers(?User $Users): self
    {
        $this->Users = $Users;

        return $this;
    }
}
