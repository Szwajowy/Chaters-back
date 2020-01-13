<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\User;
use App\Entity\Friend;

use App\Repository\ChatRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Repository\FriendRepository;

use App\Controller\ChatController;

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

    /**
     * @Route("/user/{userId}/info", methods={"GET","HEAD"}, name="returnUserInfo")
     */
    public function returnUserInfo($userId)
    {
        $user = $this->userRepository->find($userId);

        if(!$user)
            return $this->respondNotFound('Can\'t find user with this id!');

        if($responseData = $this->userRepository->getUserInfo($user)) {
            return $this->respond( $responseData,
                                   ["Access-Control-Allow-Origin" => "*"] );
        } else {
            return $this->respondNotFound();
        }
    }
    
    /**
     * @Route("/user/{userId}", methods={"DELETE"}, name="removeUserInfo")
     */
    public function removeUserInfo($userId, EntityManagerInterface $em) {
        $user = $this->userRepository->find($userId);

        if(!$user)
            return $this->respondNotFound('Can\'t find user with this id!');

        $em->remove($user);
        $em->flush();

        return $this->respondCreated( $this->userRepository->getUserInfo('all') );
    }

    /**
     * @Route("/user/{userId}/friends", methods={"GET","HEAD"}, name="returnUserFriends")
     */
    public function returnUserFriends($userId)
    {
        $user = $this->userRepository->find($userId);

        if(!$user)
            return $this->respondNotFound('Can\'t find user with this id!');

        if($userFriends = $this->userRepository->getUserFriends($user)) {
            return $this->respond( $userFriends,
                                   ["Access-Control-Allow-Origin" => "*"] );
        } else {
            return $this->respondNotFound();
        }
    }

    /**
     * @Route("/user/{userId}/friends/{friendId}", methods={"POST"}, name="addUserFriend")
     */
    public function addUserFriend($userId, $friendId, EntityManagerInterface $em) {
        $user = $this->userRepository->find($userId);

        if(!$user)
            return $this->respondNotFound('Can\'t find user with this id!');

        $user2 = $this->userRepository->find($friendId);

        if(!$user2)
            return $this->respondNotFound('Can\'t find friend with this id!');

        if($this->userRepository->isFriend($user, $user2))
            return $this->respondValidationError('Users are already friends!');

        $friend = new Friend();
        $friend->setUserid($user);
        $friend->setFriendid($user2);

        $em->persist($friend);
        $em->flush();

        return $this->respondCreated( $this->userRepository->getUserFriends($userId) );
    }

    /**
     * @Route("/user/{userId}/friends/{friendId}", methods={"DELETE"}, name="removeUserFriend")
     */
    public function removeUserFriend($userId, $friendId, EntityManagerInterface $em) {
        $user = $this->userRepository->find($userId);    

        if(!$user)
            return $this->respondNotFound('Can\'t find user with this id!');

        $user2 = $this->userRepository->find($friendId);

        if(!$user2)
            return $this->respondNotFound('Can\'t find friend with this id!');

        if(!$this->userRepository->isFriend($user, $user2))
            return $this->respondValidationError('Users are not friends!');

        $em->remove($user2);
        $em->flush();

        return $this->respondCreated( $this->userRepository->getUserFriends($userId) );
    }

    /**
     * @Route("/user/{userId}/chats", methods={"GET","HEAD"}, name="returnUserChats")
     */
    public function returnUserChats($userId)
    {
        $user = $this->find($userId);

        if(!$user)
            return $this->respondNotFound('Can\'t find user with this id!');

        if($userChats = $this->userRepository->getUserChats($this->chatRepository, $user)) {
            return $this->respond( $userChats,
                                   ["Access-Control-Allow-Origin" => "*"] );
        } else {
            return $this->respondNotFound();
        }
    }

    /**
     * @Route("/user/{userId}/chats/{chatId}", methods={"POST"}, name="addUserChat")
     */
    public function addUserChat($userId, $chatId, EntityManagerInterface $em) {
        $user = $this->userRepository->find($userId);

        if(!$user)
            return $this->respondNotFound('Can\'t find user with this id!');

        $chat = $this->chatRepository->find($chatId);

        if(!$chat)
            return $this->respondNotFound('Can\'t find chat with this id!');

        if($this->userRepository->hasChat($user, $chat))
            return $this->respondValidationError('User is already member of this chat!');

        $user->addChatId($chat);
        $em->flush();

        return $this->respondCreated( $this->userRepository->getUserChats($this->chatRepository, $user->getId()) );
    }

    /**
     * @Route("/user/{userId}/chat/{chatId}", methods={"DELETE"}, name="removeUserChat")
     */
    public function removeUserChat($userId, $chatId, EntityManagerInterface $em) {
        $user = $this->userRepository->find($userId);

        if(!$user)
            return $this->respondNotFound('Can\'t find user with this id!');

        $chat = $this->chatRepository->find($chatId);

        if(!$chat)
            return $this->respondNotFound('Can\'t find chat with this id!');

        if(!$this->userRepository->hasChat($user, $chat))
            return $this->respondValidationError('User isn\'t member of this chat!');

        $user->removeChatId($chat);
        $em->flush();

        return $this->respondCreated( $this->userRepository->getUserChats($this->chatRepository, $user->getId()) );
    }

    /**
     * @Route("/user/{userId}", methods={"GET","HEAD"}, name="returnUserAll")
     */
    public function returnUserAll($userId)
    {
        if($userId != 'all') {
            $user = $this->userRepository->find($userId);

            if(!$user)
                return $this->respondNotFound('Can\'t find user with this id!');

            $resultInfo = $this->userRepository->getUserInfo($user);
            $resultFriends = $this->userRepository->getUserFriends($user);
            $resultChats = $this->userRepository->getUserChats($this->chatRepository, $user);
                
            $responseData[] = ["info" => $resultInfo,
                                "friends" => $resultFriends,
                                "chats" => $resultChats];   
        } else {
            $resultInfo = $this->userRepository->getUserInfo();

            foreach($resultInfo as $userInfo) {
                $user = $this->userRepository->find($userInfo['id']);

                if(!$user)
                    return $this->respondNotFound('Can\'t find user with this id!');

                $user = $user[0];

                $userFriends = $this->userRepository->getUserFriends($userInfo['id']);
                $userChats = $this->userRepository->getUserChats($this->chatRepository,$userInfo['id']);

                $responseData[] = ["info" => $userInfo,
                                    "friends" => $userFriends,
                                    "chats" => $userChats];
            }
        }
        
        return $this->respond( $responseData,
                                ["Access-Control-Allow-Origin" => "*"] );
    }
}
