<?php

namespace App\Form;

use App\Entity\Player;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pseudo')
            ->add('mail')
            ->add('password')
            ->add('photo')
            ->add('descByAdmin')
            ->add('timePlayer')
            ->add('lastChance')
            ->add('rSuccess')
            ->add('rPrecision')
            ->add('rHelp')
            ->add('nbrAskHelp')
            ->add('nbrAskReceivedHelp')
            ->add('nbrAcceptHelp')
            ->add('team')
            ->add('listAsset')
            ->add('listEnigma')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Player::class,
        ]);
    }
}
