<?php

namespace Btn\MessageBundle\Twig;

use Btn\MessageBundle\Helper\MessageHelper;

class BtnMessageExtension extends \Twig_Extension
{
    /**
     * Message Manager
     * @var \Btn\MessageBundle\Helper\MessageHelper
     */
    protected $mh;

    public function __construct(MessageHelper $mh)
    {
        $this->mh = $mh;
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
        return $this->mh->getTypeKey($type);
    }

    /**
     *
     */
    public function getName()
    {
        return 'btn_message_extension';
    }
}
