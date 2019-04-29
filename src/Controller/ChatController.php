<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;

use App\Entity\Chat;
use App\Entity\Message;
use App\Repository\ChatRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;

use Symfony\Component\Routing\Annotation\Route;

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

    public function getChatInfo($chatId)
    {
        if($chatId != 'all') {
            $results = $this->chatRepository->findBy(
                array(
                    'id' => intval( $chatId )
                ), 
                array());
        } else {
            $results = $this->chatRepository->findAll();
        }
        
        if($results != null) {
            if(count($results) == 1) 
                return $this->chatRepository->transform( $results[0] );

            foreach($results as $chat) {
                $responseData[] = $this->chatRepository->transform( $chat );
            }
            
            return $responseData;
        } else {
            return null;
        }
    }

    /**
     * @Route("/chat/{chatId}/info", name="returnChatInfo")
     */
    public function returnChatInfo($chatId)
    {
        if($responseData = $this->getChatInfo($chatId)) {
            return $this->respond( $responseData,
                                    ["Access-Control-Allow-Origin" => "*"] );
        } else {
            return $this->respondNotFound();
        }
    }

    public function getChatMessages($chatId)
    {
        if($chatId != 'all') {
            $results = $this->messageRepository->findBy(
                array(
                    'chatId' => intval( $chatId )
                ), 
                array());
        } else {
            $results = $this->messageRepository->findAll();
        }

        if( $results != null ) {
            foreach( $results as $message ) {
                $responseData[] = $this->messageRepository->transform( $this->userRepository, $message );
            }

            return $responseData;
        } else {
            return null;
        }
    }

    /**
     * @Route("/chat/{chatId}/messages", name="returnChatMessages")
     */
    public function returnChatMessages($chatId)
    {
        if($responseData = $this->getChatMessages($chatId)) {
            return $this->respond( $responseData );
        } else {
            return $this->respondNotFound();
        }
    }

    /**
     * @Route("/chat/{chatId}", name="returnChatAll")
     */
    public function returnChatAll($chatId)
    {
        if($resultInfo = $this->getChatInfo($chatId)) {
            if($chatId != 'all') {
                $resultMessages = $this->getChatMessages($chatId);

                $responseData[] = ["info" => $resultInfo,
                                   "messages" => $resultMessages];   
            } else {
                foreach($resultInfo as $chatInfo) {
                    $chatMessages = $this->getChatMessages($chatInfo['id']);
    
                    $responseData[] = ["info" => $chatInfo,
                                       "messages" => $chatMessages];
                }
            }

            return $this->respond( $responseData,
                                   ["Access-Control-Allow-Origin" => "*"] );
        } else {
            return $this->respondNotFound();
        }
    }
}
