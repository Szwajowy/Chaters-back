<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Message
 *
 * @ORM\Table(name="message", indexes={@ORM\Index(name="chatId_idx", columns={"chatId"}), @ORM\Index(name="userUD_idx", columns={"userId"})})
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 */
class Message
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
     * @ORM\Column(name="text", type="string", length=45, nullable=true)
     */
    private $text;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="sendedAt", type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     * @ORM\Version
     */
    private $sendedAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="recievedAt", type="datetime", nullable=true)
     */
    private $recievedAt;

    /**
     * @var \Chat
     *
     * @ORM\ManyToOne(targetEntity="Chat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="chatId", referencedColumnName="id")
     * })
     */
    private $chatId;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="userId", referencedColumnName="id")
     * })
     */
    private $userId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getSendedAt(): ?\DateTimeInterface
    {
        return $this->sendedAt;
    }

    public function setSendedAt(?\DateTimeInterface $sendedAt): self
    {
        $this->sendedAt = $sendedAt;

        return $this;
    }

    public function getRecievedat(): ?\DateTimeInterface
    {
        return $this->recievedAt;
    }

    public function setRecievedAt(?\DateTimeInterface $recievedAt): self
    {
        $this->recievedAt = $recievedAt;

        return $this;
    }

    public function getChatId(): ?Chat
    {
        return $this->chatId;
    }

    public function setChatId(?Chat $chatId): self
    {
        $this->chatId = $chatId;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): self
    {
        $this->userId = $userId;

        return $this;
    }


}
