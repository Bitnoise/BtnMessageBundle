<?php

namespace Btn\MessageBundle\Helper;

class MessageHelper
{
    /**
     * @var array Message types from config
     */
    protected $messageType;

    /**
     * @param array $messageType
     */
    public function __construct(array $messageType = null)
    {
        $this->messageType = $messageType;
    }

    /**
     * Get Message type
     *
     * @return array
     */
    public function getMessageType()
    {
        return $this->messageType;
    }

    /**
     * Get type array
     *
     * @param  integer      $input
     * @return integer|null
     */
    public function getType($input)
    {
        if (!is_null($input) && $this->messageType) {
            foreach ($this->messageType as $key => $type) {
                if ($key === $input || (is_numeric($input) && $input == $type['id'])) {
                    if (isset($type['key']) && $type['key'] !== $key) {
                        throw new \Exception("Message type array shound't have key element diferent then array key");
                    }
                    $type['key'] = $key;

                    return $type;
                }
            }
            throw new \Exception(sprintf("Message type for '%s' haven't been found", $input));
        }

        return null;
    }

    /**
     * Get type id for message type
     *
     * @param  integer      $input
     * @return integer|null
     */
    public function getTypeId($input)
    {
        $type = $this->getType($input);

        return $type['id'];
    }

    /**
     * Get type key for message type
     *
     * @param  integer      $input
     * @return integer|null
     */
     public function getTypeKey($input)
     {
        $type = $this->getType($input);

        return $type['key'];
     }
}
