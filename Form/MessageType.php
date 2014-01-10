<?php

namespace Btn\MessageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MessageType extends AbstractType
{
    /**
     * Messge type
     * @var array
     */
    protected $types;

    /**
     *
     */
    public function __construct($types)
    {
        $this->types = $types;

        return $this;
    }

    /**
     *
     */
    public function getTypesForChoice()
    {
        $return = array();
        foreach ($this->types as $types) {
            $return[$types['id']] = 'message_type.' . $types['name'];
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->types) {
            $builder->add('type', 'choice', array(
                'label'   => 'form.type',
                'choices' => $this->getTypesForChoice(),
            ));
        } else {
            $builder->add('type', 'hidden', array(
                'data' => null,
            ));
        }
        $builder
            ->add('subject', 'text', array(
                'label' => 'form.subject',
            ))
            ->add('body', 'textarea', array(
                'label' => 'form.body',
            ))
            ->add('send', 'submit', array(
                'label' => 'form.send',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'BtnMessageBundle',
            'csrf_protection'    => true,
            // 'data_class' => 'Btn\MessageBundle\Entity\Message',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'message';
    }
}
