<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class EditUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $form, array $options)
    {
        $form
            ->add('locale', LanguageType::class,[
                'preferred_choices' => ['pl','en']
            ])
            ->add('firstName', TextType::class,[
            'attr' => [
                'class' => 'form-control'
            ],
            'required' => false
            ])
            ->add('secondName', TextType::class,[
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('imageFile', VichFileType::class, array(
                'label' => 'Image',
                'required' => false,
                'allow_delete' => true,
                'download_label' => true
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
        ));
    }
}