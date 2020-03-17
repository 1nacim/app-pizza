<?php

namespace App\Form;

use App\Entity\Adresse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdresseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numeroRue', TextType::class, [
                'attr' => [
                    'placeholder' => 'Numéro de la rue où vous vous trouvez'
                ]
            ])
            ->add('rue', TextType::class, [
                'attr' => [
                    'placeholder' => 'Nom de la rue/avenue... où vous vous trouvez'
                ]
            ])
            ->add('ville', TextType::class, [
                'attr' => [
                    'placeholder' => 'Nom de la ville où vous vous trouvez'
                ]
            ])
            ->add('codePostal', IntegerType::class, [
                'attr' => [
                    'placeholder' => 'Code postal du lieu où vous vous trouvez'
                ]
            ])
            ->add('quartier', TextType::class, [
                'attr' => [
                    'placeholder' => 'Nom du quartier rue où vous vous trouvez'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Finaliser la commande'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Adresse::class,
        ]);
    }
}
