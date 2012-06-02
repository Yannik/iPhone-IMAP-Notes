<?php
use Zend\Mail\Headers;

use Zend\Mail\Message;

use Zend\Mail\Storage;

use Zend\Mail\Storage\Imap;
use Zend\Mail;

class Notes
{
    /**
     * IMAP Protocol
     * @var \Zend\Mail\Protocol\Imap
     */
    protected $_imap;

    /**
     * IMAP storage interaction
     * @var Imap
     */
    protected $_storage;

    function __construct($user = NULL, $pass = NULL, $basefolder = NULL) {
        if (!is_null($user) && !is_null($pass)) {
            $this->_imap = new \Zend\Mail\Protocol\Imap('mail.messagingengine.com', NULL, FALSE);
            $this->_imap->login($user, $pass);
            $this->_storage = new Imap($this->_imap);
        }
        // Get a list of folders, look for Notes; if not then add it and be ready to go!
        if (!is_null($basefolder)) {
            $this->_storage->selectFolder($basefolder . '.Notes');
        }
        else {
            $this->_storage->selectFolder('Notes');
        }
    }

    function __destruct() {
        $this->_storage->close();
        $this->_imap->logout();
    }

    public function index() {
        // TODO: Schema-ize this
        $index = array();
        foreach ($this->_storage as $messageNum => $message) {
            if (!$message->hasFlag(Storage::FLAG_DELETED)) {
                $headers = $message->getHeaders();
                $index[] = array(
                  'num' => $messageNum,
                  'uuid' => $headers['x-universally-unique-identifier'],
                  'subject' => $message->subject,
                  'body' => $message->getContent(),
                );
            }
        }
        return $index;
    }

    public function create($msg = '') {
        $uuid = UUID::mint()->string;
        $message = new Message();
        $message->setSubject('BLOOM - '.$uuid)
        ->setBody($msg)
        ->addFrom('Notes App');
//        ->setEncoding("UTF-8");
        $message->headers()->addHeaderLine('X-Universally-Unique-Identifier', $uuid);
        $message->headers()->addHeaderLine('X-Uniform-Type-Identifier', 'com.apple.mail-note');
        // TODO: catch what might go wrong here...
        $this->_storage->appendMessage($message->toString(), NULL, array(Storage::FLAG_SEEN));
        return $uuid;
    }

    public function retrieve($msgid = NULL) {
    }

    public function update($msgid = NULL) {
    }

    public function delete($msgid = NULL) {
    }
}