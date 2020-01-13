<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

use App\Entity\Chat;
use App\Repository\MessageRepository;

/**
 * @method Chat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chat[]    findAll()
 * @method Chat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatRepository extends ServiceEntityRepository
{
    private $messageRepository;

    public function __construct(RegistryInterface $registry, MessageRepository $messageRepository)
    {
        parent::__construct($registry, Chat::class);
        $this->messageRepository = $messageRepository;
    }

    public function transform(Chat $chat) 
    {
        return [
            'id' => $chat->getId(),
            'name' => $chat->getName()
        ];
    }

    // Check if $user is member of $chat
    public function isMember($chat, $user) {
        foreach($chat->getUserid() as $member) {
            if( ($member->getId() == $user->getId()) || ($member->getLogin() == $user->getLogin()) || ($member->getEmail() == $user->getEmail()) )
                return true;
        }

        return false;
    }

    /*
     * Basic CRUD for managing chats
     */
    public function getChatInfo($chatId)
    {
        if($chatId != 'all') {
            $results = $this->find($chatId);
        } else {
            $results = $this->findAll();
        }
        
        if($results != null) {
            if(!is_array($results)) 
                return $this->transform( $results );

            foreach($results as $chat) {
                $responseData[] = $this->transform( $chat );
            }
            
            return $responseData;
        }
        
        return null;
    }

    /*
     * Basic CRUD for managing chat messages
     */
    public function getChatMessages($userRepository, $chatId)
    {
        if($chatId != 'all') {
            $results = $this->messageRepository->findBy(
                    ['chatId' => $chatId]
                );
        } else {
            $results = $this->messageRepository->findAll();
        }

        if( $results != null ) {
            foreach( $results as $message ) {
                $responseData[] = $this->messageRepository->transform( $userRepository, $message );
            }

            return $responseData;
        }
            
        return null;
    }

    /*
     * Basic CRUD for managing chat members
     */
    public function getChatMembers($userRepository, $chatId)
    {
        $results = $this->find($chatId);

        if( $results != null ) {
            foreach( $results->getUserid() as $member ) {
                $responseData[] = $userRepository->transform( $member );
            }

            return $responseData;
        }
        
        return null;
    }

    // /**
    //  * @return Chat[] Returns an array of Chat objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Chat
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
