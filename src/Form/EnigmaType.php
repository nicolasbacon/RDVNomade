<?php

namespace App\Form;

use App\Entity\Enigma;
use App\Entity\Skill;
use App\Repository\SkillRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnigmaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => "Nom de l'énigme :  "])
            ->add('description', TextType::class, ['label' => "Description :  "])
            ->add('answer', TextType::class, ['label' => "Réponse :  "])
            ->add('star', CheckboxType::class, ['label' => "Enigme avec Etoile", 'required'=>false])
            ->add('listSkill', EntityType::class,
                [
                 'label' => "Liste Compétences : ",
                 'class'=> Skill::class,
                 'query_builder' => function(SkillRepository $repo) {
                        return $repo->findAllOrdered();
                        },
                 'choice_label'=> "namevalue",
                 'multiple' => true])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Enigma::class,
        ]);
    }
}
