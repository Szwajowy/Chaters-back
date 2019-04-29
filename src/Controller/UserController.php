<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\ChatRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Repository\FriendRepository;

class UserController extends ApiController
{
    private $chatRepository;
    private $messageRepository;
    private $userRepository;
    private $friendRepository;

    public function __construct(ChatRepository $chatRepository, MessageRepository $messageRepository, UserRepository $userRepository, FriendRepository $friendRepository) {
        $this->chatRepository = $chatRepository;
        $this->messageRepository = $messageRepository;
        $this->userRepository = $userRepository;
        $this->friendRepository = $friendRepository;
    }

    public function getUserInfo($userId)
    {
        if($userId != 'all') {
            $results = $this->userRepository->findBy(
                array(
                    'id' => intval( $userId )
                ), 
                array());
        } else {
            $results = $this->userRepository->findAll();
        }

        if( $results != null ) {
            if(count($results) == 1) 
                return $this->userRepository->transform( $results[0] );

            foreach($results as $user) {
                $responseData[] = $this->userRepository->transform( $user );
            }
            
            return $responseData;
        } else {
            return null;
        }
    }

    /**
     * @Route("/user/{userId}/info", name="returnUserInfo")
     */
    public function returnUserInfo($userId)
    {
        if($responseData = $this->getUserInfo($userId)) {
            return $this->respond( $responseData,
                                   ["Access-Control-Allow-Origin" => "*"] );
        } else {
            return $this->respondNotFound();
        }
    }

    public function getUserFriends($userId)
    {
        $friends = $this->friendRepository
            ->createQueryBuilder('friend')
            ->select('friend')
            ->where('friend.userid = :userId or friend.friendid = :userId')
            ->setParameter(':userId', $userId)
            ->getQuery()
            ->getResult();

        if( $friends != null ) {
            foreach($friends as $friend) {
                if($friend->getUserid()->getId() == $userId) {
                    $result[] = $this->userRepository->transform($friend->getFriendid());
                } else {
                    $result[] = $this->userRepository->transform($friend->getUserid());
                }
            }
            return $result;
        } else {
            return null;
        }
    }

    /**
     * @Route("/user/{userId}/friends", name="returnUserFriends")
     */
    public function returnUserFriends($userId)
    {
        if($userFriends = $this->getUserFriends($userId)) {
            return $this->respond( $userFriends,
                                   ["Access-Control-Allow-Origin" => "*"] );
        } else {
            return $this->respondNotFound();
        }
    }

    public function getUserChats($userId)
    {
        $user = $this->userRepository->findBy(
            array(
                'id' => intval( $userId )
            ), 
            array());

        $user = $user[0];

        if( $user != null ) {
            foreach($user->getChatId() as $chat) {
                $result[] = $this->chatRepository->transform( $chat );
            }

            return $result;
        } else {
            return null;
        }
    }

    /**
     * @Route("/user/{userId}/chats", name="returnUserChats")
     */
    public function returnUserChats($userId)
    {
        if($userChats = $this->getUserChats($userId)) {
            return $this->respond( $userChats,
                                   ["Access-Control-Allow-Origin" => "*"] );
        } else {
            return $this->respondNotFound();
        }
    }

    /**
     * @Route("/user/{userId}", name="returnUserAll")
     */
    public function returnUserAll($userId)
    {
        if($resultInfo = $this->getUserInfo($userId)) {
            if($userId != 'all') {
                $resultFriends = $this->getUserFriends($userId);
                $resultChats = $this->getUserChats($userId);
                
                $responseData[] = ["info" => $resultInfo,
                                   "friends" => $resultFriends,
                                   "chats" => $resultChats];   
            } else {
                foreach($resultInfo as $userInfo) {
                    $userFriends = $this->getUserFriends($userInfo['id']);
                    $userChats = $this->getUserChats($userInfo['id']);
    
                    $responseData[] = ["info" => $userInfo,
                                       "friends" => $userFriends,
                                       "chats" => $userChats];
                }
            }

            return $this->respond( $responseData,
                                   ["Access-Control-Allow-Origin" => "*"] );
        } else {
            return $this->respondNotFound();
        }
    }
}
