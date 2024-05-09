<?php

namespace App\Entity;

use App\Repository\EnigmeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EnigmeRepository::class)]
class Enigme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getUsers", "getEnigmes"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUsers", "getEnigmes"])]
    private ?string $intitule = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUsers", "getEnigmes"])]
    private ?string $solution = null;

    #[ORM\Column]
    #[Groups(["getUsers", "getEnigmes"])]
    private ?bool $resolue = null;

    #[ORM\ManyToOne(inversedBy: 'enigmes')]
    #[Groups(["getEnigmes"])]
    private ?User $utilisateur = null;

    #[ORM\Column(length: 255)]
    private ?string $reponse_a = null;

    #[ORM\Column(length: 255)]
    private ?string $reponse_b = null;

    #[ORM\Column(length: 255)]
    private ?string $reponse_c = null;

    #[ORM\Column(length: 255)]
    private ?string $reponse_d = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    public function setIntitule(string $intitule): static
    {
        $this->intitule = $intitule;

        return $this;
    }

    public function getSolution(): ?string
    {
        return $this->solution;
    }

    public function setSolution(string $solution): static
    {
        $this->solution = $solution;

        return $this;
    }

    public function isResolue(): ?bool
    {
        return $this->resolue;
    }

    public function setResolue(bool $resolue): static
    {
        $this->resolue = $resolue;

        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getReponseA(): ?string
    {
        return $this->reponse_a;
    }

    public function setReponseA(string $reponse_a): static
    {
        $this->reponse_a = $reponse_a;

        return $this;
    }

    public function getReponseB(): ?string
    {
        return $this->reponse_b;
    }

    public function setReponseB(string $reponse_b): static
    {
        $this->reponse_b = $reponse_b;

        return $this;
    }

    public function getReponseC(): ?string
    {
        return $this->reponse_c;
    }

    public function setReponseC(string $reponse_c): static
    {
        $this->reponse_c = $reponse_c;

        return $this;
    }

    public function getReponseD(): ?string
    {
        return $this->reponse_d;
    }

    public function setReponseD(string $reponse_d): static
    {
        $this->reponse_d = $reponse_d;

        return $this;
    }
}
