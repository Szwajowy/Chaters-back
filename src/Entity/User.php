<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="username", type="string", length=45, unique=true, nullable=false)
     */
    private $username;

    /**
     * @var string|null
     *
     * @ORM\Column(name="password", type="string", length=45, nullable=false)
     */
    private $password;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="string", length=45, unique=true, nullable=false)
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="forename", type="string", length=45, nullable=true)
     */
    private $forename;

    /**
     * @var string|null
     *
     * @ORM\Column(name="surname", type="string", length=45, nullable=true)
     */
    private $surname;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Chat", mappedBy="userid")
     */
    private $chatId;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->chatId = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getForename(): ?string
    {
        return $this->forename;
    }

    public function setForename(?string $forename): self
    {
        $this->forename = $forename;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(?string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * @return Collection|Chat[]
     */
    public function getChatId(): Collection
    {
        return $this->chatId;
    }

    public function addChatId(Chat $chatId): self
    {
        if (!$this->chatId->contains($chatId)) {
            $this->chatId[] = $chatId;
            $chatId->addUserid($this);
        }

        return $this;
    }

    public function removeChatId(Chat $chatId): self
    {
        if ($this->chatId->contains($chatId)) {
            $this->chatId->removeElement($chatId);
            $chatId->removeUserid($this);
        }

        return $this;
    }

    public function getRoles()
    {
        return [
            'ROLE_USER'
        ];
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {

    }

    public function serialize() {
        return serialize( [
            $this->id,
            $this->username,
            $this->email,
            $this->forename,
            $this->surname
        ]);
    }

    public function unserialize($string) {
        list ( 
            $this->id,
            $this->username,
            $this->email,
            $this->forename,
            $this->surname
        ) = unserialize($string, ['allowed_classes' => false]);
    }

}
