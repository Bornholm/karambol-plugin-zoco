<?php

namespace KarambolZocoPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Validator\Constraints as Constraints;

class WorkgroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $builder
        ->add('name', Type\TextType::class, [
          'label' => 'plugins.zoco.workgroup.name'
        ])
        ->add('submit', Type\SubmitType::class, [
          'label' => 'plugins.zoco.workgroup.create',
          'attr' => [
            'class' => 'btn-success'
          ]
        ])
      ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
      $resolver->setDefaults([
        'data_class' => 'KarambolZocoPlugin\Entity\Workgroup',
        'cascade_validation' => true
      ]);
    }
}
