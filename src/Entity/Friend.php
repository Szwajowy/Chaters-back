<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Friend
 *
 * @ORM\Table(name="friend", indexes={@ORM\Index(name="userID_idx", columns={"userID"}), @ORM\Index(name="friendID_idx", columns={"friendID"})})
 * @ORM\Entity(repositoryClass="App\Repository\FriendRepository")
 */
class Friend
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
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="friendID", referencedColumnName="id")
     * })
     */
    private $friendid;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="userID", referencedColumnName="id")
     * })
     */
    private $userid;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFriendid(): ?User
    {
        return $this->friendid;
    }

    public function setFriendid(?User $friendid): self
    {
        $this->friendid = $friendid;

        return $this;
    }

    public function getUserid(): ?User
    {
        return $this->userid;
    }

    public function setUserid(?User $userid): self
    {
        $this->userid = $userid;

        return $this;
    }


}
