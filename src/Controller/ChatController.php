<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Chat;
use App\Entity\Message;
use App\Repository\ChatRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;

class ChatController extends ApiController 
{
    private $chatRepository;
    private $messageRepository;
    private $userRepository;
    
    public function __construct(ChatRepository $chatRepository, MessageRepository $messageRepository, UserRepository $userRepository) {
        $this->chatRepository = $chatRepository;
        $this->messageRepository = $messageRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/chat/{chatId}/info", methods={"GET","HEAD"}, name="returnChatInfo")
     */
    public function returnChatInfo($chatId)
    {
        if($responseData = $this->chatRepository->getChatInfo($chatId)) {
            return $this->respond( $responseData,
                                    ["Access-Control-Allow-Origin" => "*"] );
        }
        
        return $this->respondNotFound();
    }

    /**
     * @Route("/chat/{chatId}/messages", methods={"GET","HEAD"}, name="returnChatMessages")
     */
    public function returnChatMessages($chatId)
    {
        if($responseData = $this->chatRepository->getChatMessages($this->userRepository, $chatId)) {
            return $this->respond( $responseData,
                                    ["Access-Control-Allow-Origin" => "*"] );
        }
        
        return $this->respondNotFound();
    }

    /**
     * @Route("/chat/{chatId}/messages", methods={"POST"}, name="addChatMessage")
     */
    public function addChatMessage($chatId, Request $request, EntityManagerInterface $em) {
        // Take logged user ID from session
        $loggedUserId = 1;
        
        $requestBody = $this->transformJsonBody($request);

        if(!$requestBody) {
            return $this->respondValidationError('Please provide a valid request!');
        }

        if(!$requestBody->get('text')) {
            return $this->respondValidationError('Please provide a message!');
        }

        $chat = $this->chatRepository->find($chatId);

        if(!$chat)
            return $this->respondNotFound('Can\'t find chat with this id!');

        $chat = $chat[0];

        $user = $this->userRepository->find($loggedUserId);

        if($user) 
            return $this->respondNotFound('Can\'t find user with this credentials!');
        
        $user = $user[0];

        $message = new Message;
        $message->setText($request->get('text'));
        $message->setChatId($chat);
        $message->setUserId($user);

        $em->persist($message);
        $em->flush();

        return $this->respondCreated($this->messageRepository->transform( $this->userRepository, $message ));
    }

    /**
     * @Route("/chat/{chatId}/members", methods={"GET","HEAD"}, name="returnChatMembers")
     */
    public function returnChatMembers($chatId)
    {
        if($responseData = $this->chatRepository->getChatMembers($this->userRepository, $chatId)) {
            return $this->respond( $responseData,
                                    ["Access-Control-Allow-Origin" => "*"] );
        }
        
        return $this->respondNotFound();
    }

    /**
     * @Route("/chat/{chatId}/members", methods={"POST"}, name="addChatMember")
     */
    public function addChatMember($chatId, Request $request, EntityManagerInterface $em) {
        $requestBody = $this->transformJsonBody($request);

        if(!$requestBody) {
            return $this->respondValidationError('Please provide a valid request!');
        }

        if(!$requestBody->get('id') && !$requestBody->get('login')  && !$requestBody->get('email')) {
            return $this->respondValidationError('Please provide a user id, login or email!');
        }

        $chat = $this->chatRepository->find($chatId);

        if(!$chat)
            return $this->respondNotFound('Can\'t find chat with this id!');

        $chat = $chat[0];

        $user = $this->userRepository
            ->createQueryBuilder('user')
            ->select('user')
            ->where('user.id = :userId or user.login = :userLogin or user.email = :userEmail')
            ->setParameter(':userId', $requestBody->get('id'))
            ->setParameter(':userLogin', $requestBody->get('login'))
            ->setParameter(':userEmail', $requestBody->get('email'))
            ->getQuery()
            ->getResult();

        if($user) 
            return $this->respondNotFound('Can\'t find user with this credentials!');
        
        $user = $user[0];

        if( $this->chatRepository->isMember($chat, $user) )
            return $this->respondValidationError('User is already member of this chat!');

        $chat->addUserid($user);
        $em->flush();

        return $this->respondCreated( $this->chatRepository->getChatMembers($this->userRepository, $chatId) );
    }

    /**
     * @Route("/chat/{chatId}/members/{memberId}", methods={"DELETE"}, name="removeChatMember")
     */
    public function removeChatMember($chatId, $memberId, Request $request, EntityManagerInterface $em) {
        $requestBody = $this->transformJsonBody($request);

        if(!$requestBody) {
            return $this->respondValidationError('Please provide a valid request!');
        }

        $chat = $this->chatRepository->find($chatId);

        if(!$chat)
            return $this->respondNotFound('Can\'t find chat with this id!');

        $chat = $chat[0];

        $user = $this->userRepository->find($memberId);

        if(!$user)
            return $this->respondNotFound('Can\'t find user with this id!');

        $user = $user[0];

        if( !$this->isMember($chat, $user) )
            return $this->respondValidationError('User is not a member of this chat!');
            
        $chat->removeUserid($user);
        $em->flush();

        return $this->respond( $this->chatRepository->getChatMembers($this->userRepository, $chatId) );
    }
    
    /**
     * @Route("/chat/{chatId}", methods={"GET","HEAD"}, name="returnChatAll")
     */
    public function returnChatAll($chatId)
    {
        if($resultInfo = $this->chatRepository->getChatInfo($chatId)) {
            if($chatId != 'all') {
                $resultMessages = $this->chatRepository->getChatMessages($this->userRepository, $chatId);
                $resultMembers = $this->chatRepository->getChatMembers($this->userRepository, $chatId);

                $responseData[] = ["info" => $resultInfo,
                                   "messages" => $resultMessages,
                                   "members" => $resultMembers];   
            } else {
                foreach($resultInfo as $chatInfo) {
                    $chatMessages = $this->chatRepository->getChatMessages($this->userRepository, $chatInfo['id']);
                    $chatMembers = $this->chatRepository->getChatMembers($this->userRepository, $chatInfo['id']);
    
                    $responseData[] = ["info" => $chatInfo,
                                       "messages" => $chatMessages,
                                       "members" => $chatMembers];
                }
            }

            return $this->respond( $responseData,
                                    ["Access-Control-Allow-Origin" => "*"] );
        }
        
        return $this->respondNotFound();
    }

    /**
     * @Route("/chat", methods={"POST"}, name="createChatInfo")
     */
    public function createChatInfo(Request $request, EntityManagerInterface $em) {
        $requestBody = $this->transformJsonBody($request);

        if(!$requestBody) {
            return $this->respondValidationError('Please provide a valid request!');
        }

        if(!$requestBody->get('name')) {
            return $this->respondValidationError('Please provide a chat name!');
        }

        $chat = new Chat;
        $chat->setName($request->get('name'));
        $em->persist($chat);
        $em->flush();

        return $this->respondCreated($this->chatRepository->transform($chat));
    }

    /**
     * @Route("/chat/{chatId}", methods={"DELETE"}, name="removeChatInfo")
     */
    public function removeChatInfo($chatId, Request $request, EntityManagerInterface $em) {
        $requestBody = $this->transformJsonBody($request);

        if(!$requestBody) {
            return $this->respondValidationError('Please provide a valid request!');
        }

        $chat = $this->chatRepository->find($chatId);

        if(!$chat)
            return $this->respondNotFound('Can\'t find chat with this id!');

        $chat = $chat[0];

        $em->remove($chat);
        $em->flush();

        return $this->respondCreated( $this->chatRepository->getChatInfo('all') );
    }
}
