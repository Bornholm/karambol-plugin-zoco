<?php

namespace KarambolZocoPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Validator\Constraints as Constraints;

class AddUserWorkgroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $builder
        ->add('username', Type\TextType::class, [
          'label' => 'Utilisateur'
        ])
        ->add('submit', Type\SubmitType::class, [
          'label' => 'Inviter',
          'attr' => [
            'class' => 'btn-success'
          ]
        ])
      ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
      $resolver->setDefaults([
        'data_class' => 'Karambol\Entity\User',
        'cascade_validation' => true
      ]);
    }
}
