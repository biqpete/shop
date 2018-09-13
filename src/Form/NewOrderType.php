<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 05/09/2018
 * Time: 12:25
 */

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $form, array $options)
    {
        $form
            ->add('orderName', TextType::class, array(
                'attr' => [
                    'class' => 'form-control',
                ]
            ))
            ->add('cpu', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'choices' => array(
                    'none' => null,
                    'i3' => 'i3',
                    'i5' => 'i5',
                    'i7' => 'i7',
                )
            ))
            ->add('ram', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'choices' => array(
                    'none' => null,
                    '8' => 8,
                    '16' => 16,
                    '32' => 32,
                )
            ))
            ->add('hdd', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'choices' => array(
                    'none' => null,
                    '128' => 128,
                    '256' => 256,
                    '512' => 512,
                )
            ))
            ->add('screen', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'choices' => array(
                    'none' => null,
                    '10' => 10,
                    '13' => 13,
                    '15' => 15,
                )
            ))
            ->add('price', TextType::class, array(
                'attr' => array('class' => 'form-control'),
                'disabled' => 'true'
            ))
            ->add('comment', TextType::class, array(
                'attr' => array('class' => 'form-control')
            ))
            ->add('createdAt', HiddenType::class, [
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Order::class,
        ));
    }
}