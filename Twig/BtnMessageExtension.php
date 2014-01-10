<?php

namespace Btn\MessageBundle\Twig;

use Btn\MessageBundle\Model\MessageManager;

class BtnMessageExtension extends \Twig_Extension
{
    /**
     * Message Manager
     * @var \Btn\MessageBundle\Model\MessageManager
     */
    protected $mm;

    public function __construct(MessageManager $mm)
    {
        $this->mm = $mm;
    }

    /**
     *
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('message_type_key', array($this, 'getTypeKey')),
        );
    }

    /**
     *
     */
    public function getTypeKey($type)
    {
        return $this->mm->getTypeKey($type);
    }

    /**
     *
     */
    public function getName()
    {
        return 'btn_message_extension';
    }
}
