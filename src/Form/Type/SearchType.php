<?php

namespace KarambolZocoPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Validator\Constraints as Constraints;

class SearchType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options) {
    $builder
      ->add('q', Type\TextType::class, [
        'label' => 'plugins.zoco.search.simple_search',
        'required' => false,
        'property_path' => 'search'
      ])
      ->add('a', Type\DateType::class, [
        'label' => 'plugins.zoco.search.after_date',
        'required' => false,
        'property_path' => 'after'
      ])
      ->add('b', Type\DateType::class, [
        'label' => 'plugins.zoco.search.before_date',
        'required' => false,
        'property_path' => 'before'
      ])
      ->add('s', Type\SubmitType::class, [
        'label' => 'plugins.zoco.search.do_search'
      ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver) {
    $resolver->setDefaults([
      'data_class' => 'KarambolZocoPlugin\Entity\Search',
      'cascade_validation' => true,
      'csrf_protection' => false,
      'allow_extra_fields' => true
    ]);
  }

  public function getBlockPrefix() {
    return '';
  }

}
