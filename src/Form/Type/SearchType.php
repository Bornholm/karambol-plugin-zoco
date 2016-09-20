<?php

namespace KarambolZocoPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Validator\Constraints as Constraints;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $builder
        ->add('search', Type\TextType::class, [
          'label' => 'plugins.zoco.search.simple_search',
          'required' => false
        ])
        ->add('submit', Type\SubmitType::class, [
          'label' => 'plugins.zoco.search.do_search'
        ])
      ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
      $resolver->setDefaults([
        'data_class' => 'KarambolZocoPlugin\Entity\Search',
        'cascade_validation' => true,
        'csrf_protection' => false
      ]);
    }
}
