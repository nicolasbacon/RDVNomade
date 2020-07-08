<?php

namespace App\Form;

use App\Entity\Enigma;
use App\Entity\Session;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => "Nom de la session :  "])
            ->add('synchrone', CheckboxType::class, ['label' => "Session Synchrone", 'required'=>false])
            ->add('dateEndSession', DateTimeType::class, ['label' => "Fin de l'évènement :"])
            ->add('listEnigma',EntityType::class,
                ['class' => Enigma::class,
                'label' => "Liste Enigmes : ",
                'choice_label' => "name",
                    'multiple' => true])
            ->add('timeAlert', TimeType::class, ['label' => "Temps challenge (Avant la fin, ex : 5min) :"])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
    }
}
