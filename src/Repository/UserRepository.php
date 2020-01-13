<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\FriendRepository;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private $messageRepository;
    private $friendRepository;

    public function __construct(RegistryInterface $registry, MessageRepository $messageRepository, FriendRepository $friendRepository)
    {
        parent::__construct($registry, User::class);
        $this->messageRepository = $messageRepository;
        $this->friendRepository = $friendRepository;
    }

    public function transform(User $user) 
    {
        return [
            'id' => $user->getId(),
            'login' => $user->getUsername(),
            'forename' => $user->getForename(),
            'surname' => $user->getSurname(),
            'email' => $user->getEmail()
        ];
    }

    public function isTaken($user) 
    {
        $result = $this->createQueryBuilder('user')
            ->select('user')
            ->where('user.username = :userLogin')
            ->orWhere('user.email = :userEmail')
            ->setParameter(':userLogin', $user->getUsername())
            ->setParameter(':userEmail', $user->getEmail())
            ->getQuery()
            ->getOneOrNullResult();

        if($result)
            return true;

        return false;
    }

    public function isFriend($user, $user2) {
        $qb = $this->friendRepository->createQueryBuilder('friend');

        $friends = $qb->select('friend')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('friend.userid',':friendId'),
                $qb->expr()->eq('friend.friendid',':userId')
            ))
            ->orWhere($qb->expr()->andX(
                $qb->expr()->eq('friend.userid',':userId'),
                $qb->expr()->eq('friend.friendid',':friendId')
            ))
            ->setParameter(':userId', $user->getId())
            ->setParameter(':friendId', $user2->getId())
            ->getQuery()
            ->getOneOrNullResult();

        if($friends)
            return true;

        return false;
    }

    public function hasChat($user, $chat) {
        foreach( $user->getChatId() as $userChat ) {
            if( $userChat->getId() == $chat->getId() )
                return true;
        }

        return false;
    }

    public function getUserInfo($user = null)
    {
        if($user)
            return $this->transform( $user );
        
        $results = $this->findAll();

        if( $results != null ) {
            foreach($results as $user) {
                $responseData[] = $this->transform( $user );
            }
            
            return $responseData;
        } else {
            return null;
        }
    }

    public function getUserFriends($user)
    {
        $friends = $this->friendRepository
            ->createQueryBuilder('friend')
            ->select('friend')
            ->where('friend.userid = :userId')
            ->orWhere('friend.friendid = :userId')
            ->setParameter(':userId', $user->getId())
            ->getQuery()
            ->getResult();

        if( $friends != null ) {
            foreach($friends as $friend) {
                if($friend->getUserid()->getId() == $user->getId()) {
                    $result[] = $this->transform($friend->getFriendid());
                } else {
                    $result[] = $this->transform($friend->getUserid());
                }
            }
            return $result;
        } else {
            return null;
        }
    }

    public function getUserChats($chatRepository, $user)
    {
        if( count($user->getChatId()) != 0 ) {
            foreach($user->getChatId() as $chat) {
                $result[] = $chatRepository->transform( $chat );
            }

            return $result;
        } else {
            return null;
        }
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
