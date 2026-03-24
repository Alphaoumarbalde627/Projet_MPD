<?php

namespace App\Form\Admin;

use App\Entity\Temoignage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Range;

class TemoignageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('nomClient', TextType::class, [
                'label' => 'Nom du client',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom du client est obligatoire.']),
                    new Length(['max' => 255]),
                ],
                'attr' => ['placeholder' => 'Ex : Mohamed S.'],
            ])
            ->add('fonctionClient', TextType::class, [
                'label' => 'Fonction / Profil',
                'required' => false,
                'constraints' => [
                    new Length(['max' => 255]),
                ],
                'attr' => ['placeholder' => 'Ex : Client particulier'],
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
                'attr' => ['rows' => 4, 'placeholder' => 'Message du client...'],
            ])
            ->add('note', IntegerType::class, [
                'label' => 'Note (sur 5)',
                'constraints' => [
                    new Range([
                        'min' => 1,
                        'max' => 5,
                        'notInRangeMessage' => 'La note doit etre entre {{ min }} et {{ max }}.',
                    ]),
                ],
                'attr' => ['min' => 1, 'max' => 5],
            ])
            ->add('ordreAffichage', IntegerType::class, [
                'label' => 'Ordre d\'affichage',
                'constraints' => [
                    new PositiveOrZero(['message' => 'L\'ordre doit etre positif ou nul.']),
                ],
                'attr' => ['min' => 0],
            ])
            ->add('videoFile', FileType::class, [
                'label' => 'Video temoignage',
                'mapped' => false,
                'required' => !$isEdit,
                'constraints' => [
                    new File([
                        'maxSize' => '40M',
                        'mimeTypes' => ['video/mp4', 'video/webm', 'video/ogg'],
                        'mimeTypesMessage' => 'Veuillez uploader une video valide (MP4, WEBM, OGG).',
                    ]),
                ],
                'attr' => ['accept' => 'video/mp4,video/webm,video/ogg'],
            ])
            ->add('estActif', CheckboxType::class, [
                'label' => 'Temoignage actif (visible sur le site)',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Temoignage::class,
            'is_edit' => false,
        ]);

        $resolver->setAllowedTypes('is_edit', 'bool');
    }
}
